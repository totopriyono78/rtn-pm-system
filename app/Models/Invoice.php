<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use App\Support\Audit;
use App\Support\Notifier;

/**
 * Invoice & Billing -- SRS 4.15. Menagih SATU Project per invoice (lump-sum
 * per periode, biasanya direferensikan ke dokumen BAPP RO/BAL Bulanan yang
 * sudah ada lewat project_document_id). Alur status: draft -> sent -> paid,
 * atau dibatalkan (cancelled) kapan saja sebelum paid.
 */
#[Fillable([
    'project_id', 'project_document_id', 'invoice_number', 'invoice_date', 'due_date', 'period_label',
    'subtotal', 'tax_percent', 'tax_amount', 'total_amount', 'status', 'notes', 'created_by',
    'sent_at', 'paid_at', 'payment_reference',
])]
class Invoice extends Model
{
    use HasFactory;

    public const STATUSES = [
        'draft' => 'Draft',
        'sent' => 'Terkirim ke Customer',
        'paid' => 'Lunas',
        'cancelled' => 'Dibatalkan',
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_percent' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'sent_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Invoice $invoice) {
            $subtotal = (float) $invoice->subtotal;
            $taxPercent = (float) $invoice->tax_percent;
            $invoice->tax_amount = round($subtotal * $taxPercent / 100, 2);
            $invoice->total_amount = round($subtotal + $invoice->tax_amount, 2);
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function supportingDocument(): BelongsTo
    {
        return $this->belongsTo(ProjectDocument::class, 'project_document_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'sent'
            && $this->due_date !== null
            && $this->due_date->isPast();
    }

    public static function generateNumberSuggestion(): string
    {
        $year = now()->format('Y');
        $count = static::whereYear('invoice_date', $year)->count() + 1;

        return sprintf('INV-%s-%04d', $year, $count);
    }

    public function send(User $user): void
    {
        abort_unless($this->status === 'draft', 400, 'Hanya invoice berstatus draft yang dapat dikirim.');

        $this->status = 'sent';
        $this->sent_at = now();
        $this->save();

        Audit::log($this, 'sent', "Invoice {$this->invoice_number} dikirim oleh {$user->name}.", actor: $user);

        $clientUsers = $this->project?->directContract?->customer?->clientUsers ?? collect();
        Notifier::clients($clientUsers, 'Invoice Baru', "Invoice {$this->invoice_number} (Rp ".number_format((float) $this->total_amount, 0, ',', '.').") telah diterbitkan untuk proyek {$this->project?->name}.", route('client.invoices'));
    }

    /**
     * Auto-posting GL (SRS 4.14, lanjutan GL/Chart of Account, keputusan
     * scope eksplisit user 2026-10-06): Debit Kas & Bank, Kredit
     * Pendapatan Jasa Maintenance (subtotal) + Hutang Pajak PPN kalau ada
     * pajak. Revenue diakui pada saat pelunasan (cash-basis) karena belum
     * ada pengakuan piutang terpisah saat invoice terkirim -- lihat modul
     * "AR murni" untuk pengakuan piutang di titik invoice terkirim.
     */
    public function markPaid(User $user, CashBankAccount $account, ?string $paymentReference = null): void
    {
        abort_unless($this->status === 'sent', 400, 'Hanya invoice yang sudah terkirim yang dapat dicatat lunas.');

        DB::transaction(function () use ($user, $account, $paymentReference) {
            $this->status = 'paid';
            $this->paid_at = now();
            $this->payment_reference = $paymentReference;
            $this->save();

            // Catat sebagai transaksi kas/bank riil (masuk) -- SRS 4.14 Cash & Bank.
            CashBankTransaction::create([
                'cash_bank_account_id' => $account->id,
                'type' => 'in',
                'category' => 'invoice_payment',
                'amount' => $this->total_amount,
                'transaction_date' => now()->toDateString(),
                'description' => "Pelunasan Invoice {$this->invoice_number}",
                'invoice_id' => $this->id,
                'created_by' => $user->id,
            ]);

            $lines = [
                ['chart_of_account_id' => JournalEntry::account('kas_bank')->id, 'debit' => $this->total_amount, 'credit' => 0],
                ['chart_of_account_id' => JournalEntry::account('pendapatan_jasa')->id, 'debit' => 0, 'credit' => $this->subtotal],
            ];
            if ((float) $this->tax_amount > 0) {
                $lines[] = ['chart_of_account_id' => JournalEntry::account('hutang_pajak')->id, 'debit' => 0, 'credit' => $this->tax_amount];
            }

            JournalEntry::createAutoPosted(
                "Pelunasan Invoice {$this->invoice_number}",
                $lines,
                $user,
                $this->invoice_number,
                ['invoice_id' => $this->id]
            );

            Audit::log($this, 'paid', "Invoice {$this->invoice_number} dicatat lunas oleh {$user->name}.", actor: $user);
        });

        Notifier::user($this->creator, 'Invoice Lunas', "Invoice {$this->invoice_number} telah dicatat lunas.", route('invoices.index'));
    }

    public function cancel(): void
    {
        abort_unless(in_array($this->status, ['draft', 'sent'], true), 400, 'Invoice yang sudah lunas atau dibatalkan tidak dapat dibatalkan lagi.');

        $this->status = 'cancelled';
        $this->save();

        Audit::log($this, 'cancelled', "Invoice {$this->invoice_number} dibatalkan.");
    }
}
