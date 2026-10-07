<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Prospect ("lead") -- CRM & Sales Pipeline, SRS 4.5. Entity terpisah dari
 * Customer: lead banyak yang gagal/batal dan datanya lebih cair daripada
 * Customer resmi. Pipeline: prospek -> qualified -> proposal -> negosiasi,
 * lalu berakhir di won (dikonversi jadi Customer, lihat win()) atau lost
 * (lihat lose()). Setiap perpindahan tahap otomatis tercatat sebagai
 * ProspectActivity type=stage_change.
 */
#[Fillable([
    'code', 'company_name', 'contact_name', 'contact_phone', 'contact_email', 'address',
    'source', 'status', 'estimated_value', 'notes', 'lost_reason', 'won_at', 'lost_at',
    'customer_id', 'assigned_to', 'created_by',
])]
class Prospect extends Model
{
    use HasFactory;

    public const STATUSES = [
        'prospek' => 'Prospek Baru',
        'qualified' => 'Qualified',
        'proposal' => 'Proposal Terkirim',
        'negosiasi' => 'Negosiasi',
        'won' => 'Won (Deal)',
        'lost' => 'Lost',
    ];

    /**
     * Tahap pipeline yang masih "terbuka" / aktif, berurutan. won & lost
     * adalah tahap akhir (tertutup), ditangani lewat method win()/lose(),
     * bukan moveToStage().
     */
    public const PIPELINE_STAGES = ['prospek', 'qualified', 'proposal', 'negosiasi'];

    protected function casts(): array
    {
        return [
            'estimated_value' => 'decimal:2',
            'won_at' => 'datetime',
            'lost_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ProspectActivity::class)->orderByDesc('activity_date');
    }

    public function salesOrders(): HasMany
    {
        return $this->hasMany(SalesOrder::class);
    }

    public function isOpen(): bool
    {
        return in_array($this->status, self::PIPELINE_STAGES, true);
    }

    public static function generateCode(): string
    {
        $year = now()->format('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;

        return sprintf('PRS-%s-%04d', $year, $count);
    }

    /**
     * Pindahkan prospect ke tahap pipeline lain (tidak termasuk won/lost,
     * lihat win()/lose() untuk itu). Mencatat 1 ProspectActivity
     * type=stage_change per perpindahan.
     */
    public function moveToStage(string $stage, ?string $note = null): void
    {
        abort_unless(in_array($stage, self::PIPELINE_STAGES, true), 400, 'Tahap pipeline tidak valid.');
        abort_unless($this->isOpen(), 400, 'Prospect ini sudah ditutup (won/lost), tidak bisa dipindah tahap lagi.');

        $fromStatus = $this->status;

        if ($fromStatus === $stage) {
            return;
        }

        DB::transaction(function () use ($stage, $fromStatus, $note) {
            $this->status = $stage;
            $this->save();

            ProspectActivity::create([
                'prospect_id' => $this->id,
                'user_id' => auth()->id(),
                'type' => 'stage_change',
                'from_status' => $fromStatus,
                'to_status' => $stage,
                'notes' => $note,
                'activity_date' => now(),
            ]);
        });
    }

    /**
     * Tandai prospect sebagai menang & konversi jadi Customer -- baik
     * dengan membuat Customer baru dari data kontak prospect ini (default),
     * maupun menghubungkan ke Customer yang sudah ada ($existingCustomerId,
     * untuk kasus order ulang dari customer lama yang sempat dicatat
     * sebagai lead baru).
     */
    public function win(?int $existingCustomerId = null, ?string $note = null): Customer
    {
        abort_unless($this->isOpen(), 400, 'Prospect ini sudah ditutup (won/lost).');
        $fromStatus = $this->status;

        return DB::transaction(function () use ($existingCustomerId, $note, $fromStatus) {
            if ($existingCustomerId) {
                $customer = Customer::findOrFail($existingCustomerId);
            } else {
                $customer = Customer::create([
                    'code' => Customer::generateCode(),
                    'name' => $this->company_name,
                    'address' => $this->address,
                    'npwp' => null,
                    'pic_name' => $this->contact_name,
                    'pic_phone' => $this->contact_phone,
                    'pic_email' => $this->contact_email,
                    'is_active' => true,
                ]);
            }

            $this->status = 'won';
            $this->customer_id = $customer->id;
            $this->won_at = now();
            $this->save();

            ProspectActivity::create([
                'prospect_id' => $this->id,
                'user_id' => auth()->id(),
                'type' => 'stage_change',
                'from_status' => $fromStatus,
                'to_status' => 'won',
                'notes' => $note ?: 'Dikonversi menjadi Customer: '.$customer->name,
                'activity_date' => now(),
            ]);

            return $customer;
        });
    }

    public function lose(string $reason): void
    {
        abort_unless($this->isOpen(), 400, 'Prospect ini sudah ditutup (won/lost).');
        $fromStatus = $this->status;

        DB::transaction(function () use ($reason, $fromStatus) {
            $this->status = 'lost';
            $this->lost_reason = $reason;
            $this->lost_at = now();
            $this->save();

            ProspectActivity::create([
                'prospect_id' => $this->id,
                'user_id' => auth()->id(),
                'type' => 'stage_change',
                'from_status' => $fromStatus,
                'to_status' => 'lost',
                'notes' => $reason,
                'activity_date' => now(),
            ]);
        });
    }
}
