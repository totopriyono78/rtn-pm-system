<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\Audit;
use App\Support\Notifier;

/**
 * Budgeting formal -- bagian terakhir Finance & Accounting (SRS 4.14,
 * lanjutan GL/Chart of Account). Anggaran TAHUNAN per akun Chart of
 * Account, dibandingkan ke jurnal GL yang sudah posted di periode yang
 * sama (actual) -- bukan pengganti `Project.budget`/`ProjectBudgetLine`
 * (anggaran per proyek individual vs Purchase Order, tetap dipakai apa
 * adanya untuk kontrol budget proyek operasional).
 *
 * Alur: draft (bebas diubah) -> disetujui (oleh Direktur, lock permanen)
 * atau dibatalkan (hanya dari draft) -- konsisten pola "tidak ada edit
 * setelah final" yang dipakai JournalEntry/Invoice/dll.
 */
#[Fillable([
    'code', 'period_year', 'status', 'notes', 'created_by',
    'approved_by', 'approved_at', 'cancelled_by', 'cancelled_at',
])]
class Budget extends Model
{
    public const STATUSES = [
        'draft' => 'Draft',
        'disetujui' => 'Disetujui',
        'dibatalkan' => 'Dibatalkan',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(BudgetLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function getTotalPlannedAttribute(): float
    {
        return round((float) $this->lines->sum('planned_amount'), 2);
    }

    public static function generateCode(): string
    {
        $count = static::count() + 1;

        return sprintf('BGT-%04d', $count);
    }

    /**
     * Ganti seluruh baris anggaran sekaligus -- hapus baris lama, buat
     * ulang dari array tervalidasi. Hanya boleh selagi draft, meniru
     * JournalEntry::replaceLines().
     */
    public function replaceLines(array $lines): void
    {
        abort_unless($this->status === 'draft', 400, 'Hanya anggaran berstatus draft yang baris-barisnya dapat diubah.');

        $this->lines()->delete();
        foreach ($lines as $line) {
            $this->lines()->create([
                'chart_of_account_id' => $line['chart_of_account_id'],
                'planned_amount' => $line['planned_amount'] ?? 0,
                'notes' => $line['notes'] ?? null,
            ]);
        }
        $this->load('lines');
    }

    public function approve(User $user): void
    {
        abort_unless($this->status === 'draft', 400, 'Hanya anggaran berstatus draft yang dapat disetujui.');
        abort_if($this->lines()->count() === 0, 400, 'Anggaran butuh minimal satu baris akun sebelum disetujui.');

        $this->status = 'disetujui';
        $this->approved_by = $user->id;
        $this->approved_at = now();
        $this->save();

        Audit::log($this, 'approved', "Anggaran {$this->code} (periode {$this->period_year}) disetujui oleh {$user->name}.");
        Notifier::user($this->creator, 'Anggaran Disetujui', "Anggaran {$this->code} periode {$this->period_year} telah disetujui.", route('finance.budgets'));
    }

    public function cancel(User $user): void
    {
        abort_unless($this->status === 'draft', 400, 'Hanya anggaran berstatus draft yang dapat dibatalkan. Anggaran yang sudah disetujui dikoreksi lewat anggaran baru.');

        $this->status = 'dibatalkan';
        $this->cancelled_by = $user->id;
        $this->cancelled_at = now();
        $this->save();

        Audit::log($this, 'cancelled', "Anggaran {$this->code} (periode {$this->period_year}) dibatalkan oleh {$user->name}.");
    }
}
