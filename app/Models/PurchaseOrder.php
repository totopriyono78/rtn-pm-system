<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\DB;
use App\Support\Audit;
use App\Support\Notifier;

#[Fillable(['request_for_quotation_id', 'vendor_id', 'project_id', 'code', 'status', 'payment_status', 'total', 'created_by', 'approved_by', 'approved_at', 'paid_by', 'paid_at', 'payment_reference'])]
class PurchaseOrder extends Model
{
    use HasFactory;

    public const STATUSES = [
        'issued' => 'Diterbitkan',
        'cancelled' => 'Dibatalkan',
    ];

    public const PAYMENT_STATUSES = [
        'belum_dibayar' => 'Belum Dibayar',
        'lunas' => 'Lunas',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(RequestForQuotation::class, 'request_for_quotation_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function materialTrackings(): HasManyThrough
    {
        return $this->hasManyThrough(
            MaterialTracking::class,
            PurchaseOrderItem::class,
            'purchase_order_id',
            'purchase_order_item_id'
        );
    }

    public static function generateCode(): string
    {
        $year = now()->format('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;

        return sprintf('PO-%s-%04d', $year, $count);
    }

    public function recalcTotal(): void
    {
        $this->total = $this->items()->sum('subtotal');
        $this->save();
    }

    /**
     * AP (hutang vendor) -- pasangan dari Invoice::markPaid() (AR). Hanya
     * PO berstatus issued dan belum lunas yang dapat dibayar; sekali
     * dibayar tercatat otomatis sebagai transaksi keluar (kategori
     * vendor_payment) di Buku Kas/Bank, sama seperti pelunasan invoice
     * dan pencairan kasbon.
     */
    /**
     * Auto-posting GL (SRS 4.14, lanjutan GL/Chart of Account, keputusan
     * scope eksplisit user 2026-10-06): Debit Beban Operasional Proyek,
     * Kredit Kas & Bank. Dibebankan langsung (tidak lewat Hutang Usaha)
     * karena PO tidak pernah dibukukan sebagai hutang saat diterbitkan --
     * konsisten dengan tidak ada jurnal apa pun sebelum titik pembayaran.
     */
    public function payVendor(User $user, CashBankAccount $account, ?string $paymentReference = null): void
    {
        abort_unless($this->status === 'issued', 400, 'Hanya PO yang sudah diterbitkan yang dapat dibayar.');
        abort_if($this->payment_status === 'lunas', 400, 'PO ini sudah ditandai lunas.');

        DB::transaction(function () use ($user, $account, $paymentReference) {
            $this->payment_status = 'lunas';
            $this->paid_by = $user->id;
            $this->paid_at = now();
            $this->payment_reference = $paymentReference;
            $this->save();

            CashBankTransaction::create([
                'cash_bank_account_id' => $account->id,
                'type' => 'out',
                'category' => 'vendor_payment',
                'amount' => $this->total,
                'transaction_date' => now()->toDateString(),
                'description' => "Pembayaran PO {$this->code} ke vendor {$this->vendor->name}",
                'purchase_order_id' => $this->id,
                'created_by' => $user->id,
            ]);

            JournalEntry::createAutoPosted(
                "Pembayaran PO {$this->code} ke vendor {$this->vendor->name}",
                [
                    ['chart_of_account_id' => JournalEntry::account('beban_operasional_proyek')->id, 'debit' => $this->total, 'credit' => 0],
                    ['chart_of_account_id' => JournalEntry::account('kas_bank')->id, 'debit' => 0, 'credit' => $this->total],
                ],
                $user,
                $this->code,
                ['purchase_order_id' => $this->id]
            );

            Audit::log($this, 'paid', "PO {$this->code} dibayar ke vendor {$this->vendor->name} oleh {$user->name}.", actor: $user);
        });

        Notifier::user($this->creator, 'Purchase Order Dibayar', "PO {$this->code} ke vendor {$this->vendor->name} telah dibayar.", route('purchasing.po'));
    }
}
