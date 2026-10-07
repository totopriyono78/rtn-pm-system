<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use App\Support\Audit;
use App\Support\Notifier;

/**
 * Piutang Lain-lain -- bagian "AR murni" dari Finance & Accounting (SRS
 * 4.14, lanjutan GL/Chart of Account, keputusan scope eksplisit user
 * 2026-10-06). Piutang DI LUAR yang sudah tercermin lewat Invoice
 * (piutang dagang/customer -- itu sudah tercatat lewat status Invoice
 * sejak awal, dan sekarang auto-posting GL lewat Invoice::markPaid()).
 * Ini untuk uang yang diberikan ke pihak lain yang harus dikembalikan:
 * pinjaman karyawan di luar kasbon proyek, titipan/DP ke vendor di luar
 * PO resmi, atau piutang lain-lain.
 *
 * Alur: diajukan -> disetujui/ditolak/dibatalkan -> diberikan (uang
 * keluar, auto-posting GL + Buku Kas/Bank) -> pembayaran kembali
 * (bisa dicicil lewat OtherReceivablePayment, masing-masing auto-posting
 * GL + Buku Kas/Bank) -> lunas OTOMATIS begitu total pembayaran >=
 * jumlah yang diberikan. Tidak ada aksi hapus -- konsisten pola
 * "no destructive delete" record finansial di seluruh app.
 */
#[Fillable([
    'code', 'debtor_type', 'debtor_name', 'user_id', 'project_id', 'description', 'amount', 'due_date',
    'status', 'requested_by', 'approved_by', 'approved_at', 'rejection_reason',
    'given_by', 'given_at', 'cancelled_by', 'cancelled_at', 'notes',
])]
class OtherReceivable extends Model
{
    public const DEBTOR_TYPES = [
        'karyawan' => 'Karyawan',
        'vendor' => 'Vendor',
        'lainnya' => 'Lainnya',
    ];

    public const STATUSES = [
        'diajukan' => 'Menunggu Approval',
        'ditolak' => 'Ditolak',
        'dibatalkan' => 'Dibatalkan',
        'disetujui' => 'Disetujui, Menunggu Diberikan',
        'diberikan' => 'Sudah Diberikan',
        'lunas' => 'Lunas',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_date' => 'date',
            'approved_at' => 'datetime',
            'given_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function debtor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function giver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'given_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(OtherReceivablePayment::class);
    }

    public function debtorTypeLabel(): string
    {
        return self::DEBTOR_TYPES[$this->debtor_type] ?? $this->debtor_type;
    }

    public function getTotalPaidAttribute(): float
    {
        return round((float) $this->payments()->sum('amount'), 2);
    }

    public function getOutstandingAttribute(): float
    {
        return round((float) $this->amount - $this->total_paid, 2);
    }

    public static function generateCode(): string
    {
        $year = now()->format('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;

        return sprintf('PL-%s-%04d', $year, $count);
    }

    public function approve(User $approver): void
    {
        abort_unless($this->status === 'diajukan', 400, 'Hanya pengajuan berstatus menunggu approval yang dapat disetujui.');
        $this->status = 'disetujui';
        $this->approved_by = $approver->id;
        $this->approved_at = now();
        $this->save();

        Audit::log($this, 'approved', "Piutang lain-lain {$this->code} ({$this->debtor_name}) disetujui oleh {$approver->name}.");
        Notifier::user($this->requester, 'Piutang Lain-lain Disetujui', "Pengajuan piutang lain-lain {$this->code} telah disetujui.", route('finance.other-receivables'));
    }

    public function reject(User $approver, string $reason): void
    {
        abort_unless($this->status === 'diajukan', 400, 'Hanya pengajuan berstatus menunggu approval yang dapat ditolak.');
        $this->status = 'ditolak';
        $this->approved_by = $approver->id;
        $this->approved_at = now();
        $this->rejection_reason = $reason;
        $this->save();

        Audit::log($this, 'rejected', "Piutang lain-lain {$this->code} ({$this->debtor_name}) ditolak oleh {$approver->name}. Alasan: {$reason}");
        Notifier::user($this->requester, 'Piutang Lain-lain Ditolak', "Pengajuan piutang lain-lain {$this->code} ditolak. Alasan: {$reason}", route('finance.other-receivables'));
    }

    public function cancel(User $user): void
    {
        abort_unless($this->status === 'diajukan', 400, 'Hanya pengajuan yang masih menunggu approval yang dapat dibatalkan.');
        $this->status = 'dibatalkan';
        $this->cancelled_by = $user->id;
        $this->cancelled_at = now();
        $this->save();

        Audit::log($this, 'cancelled', "Piutang lain-lain {$this->code} ({$this->debtor_name}) dibatalkan oleh {$user->name}.");
    }

    /**
     * Uang benar-benar diberikan ke debtor -- tercatat sebagai transaksi
     * keluar di Buku Kas/Bank + auto-posting jurnal GL (Debit Piutang
     * Lain-lain, Kredit Kas & Bank).
     */
    public function give(User $user, CashBankAccount $account): void
    {
        abort_unless($this->status === 'disetujui', 400, 'Hanya pengajuan yang sudah disetujui yang dapat ditandai diberikan.');

        DB::transaction(function () use ($user, $account) {
            $this->status = 'diberikan';
            $this->given_by = $user->id;
            $this->given_at = now();
            $this->save();

            CashBankTransaction::create([
                'cash_bank_account_id' => $account->id,
                'type' => 'out',
                'category' => 'other_receivable_given',
                'amount' => $this->amount,
                'transaction_date' => now()->toDateString(),
                'description' => "Pemberian piutang lain-lain {$this->code} kepada {$this->debtor_name}",
                'other_receivable_id' => $this->id,
                'created_by' => $user->id,
            ]);

            JournalEntry::createAutoPosted(
                "Pemberian piutang lain-lain {$this->code} kepada {$this->debtor_name}",
                [
                    ['chart_of_account_id' => JournalEntry::account('piutang_lain')->id, 'debit' => $this->amount, 'credit' => 0],
                    ['chart_of_account_id' => JournalEntry::account('kas_bank')->id, 'debit' => 0, 'credit' => $this->amount],
                ],
                $user,
                $this->code,
                ['other_receivable_id' => $this->id]
            );

            Audit::log($this, 'given', "Piutang lain-lain {$this->code} diberikan kepada {$this->debtor_name} oleh {$user->name}.", actor: $user);
        });

        Notifier::user($this->requester, 'Piutang Lain-lain Diberikan', "Piutang lain-lain {$this->code} sebesar Rp ".number_format((float) $this->amount, 0, ',', '.')." telah diberikan kepada {$this->debtor_name}.", route('finance.other-receivables'));
    }

    /**
     * Catat satu pembayaran kembali (bisa dicicil) -- cash masuk ke Buku
     * Kas/Bank + auto-posting jurnal GL (Debit Kas & Bank, Kredit
     * Piutang Lain-lain). Status otomatis jadi "lunas" begitu total
     * pembayaran (termasuk yang baru ini) >= jumlah yang diberikan.
     */
    public function recordPayment(User $user, CashBankAccount $account, float $amount, string $paymentDate, ?string $notes = null): OtherReceivablePayment
    {
        abort_unless($this->status === 'diberikan', 400, 'Hanya piutang yang sudah diberikan yang dapat dicatat pembayarannya.');
        abort_if($amount <= 0, 400, 'Jumlah pembayaran harus lebih dari 0.');
        abort_if($amount > $this->outstanding + 0.005, 400, 'Jumlah pembayaran melebihi sisa piutang (Rp '.number_format($this->outstanding, 0, ',', '.').').');

        return DB::transaction(function () use ($user, $account, $amount, $paymentDate, $notes) {
            $payment = $this->payments()->create([
                'cash_bank_account_id' => $account->id,
                'amount' => $amount,
                'payment_date' => $paymentDate,
                'notes' => $notes,
                'created_by' => $user->id,
            ]);

            CashBankTransaction::create([
                'cash_bank_account_id' => $account->id,
                'type' => 'in',
                'category' => 'other_receivable_payment',
                'amount' => $amount,
                'transaction_date' => $paymentDate,
                'description' => "Pembayaran piutang lain-lain {$this->code} dari {$this->debtor_name}",
                'other_receivable_id' => $this->id,
                'created_by' => $user->id,
            ]);

            JournalEntry::createAutoPosted(
                "Pembayaran piutang lain-lain {$this->code} dari {$this->debtor_name}",
                [
                    ['chart_of_account_id' => JournalEntry::account('kas_bank')->id, 'debit' => $amount, 'credit' => 0],
                    ['chart_of_account_id' => JournalEntry::account('piutang_lain')->id, 'debit' => 0, 'credit' => $amount],
                ],
                $user,
                $this->code,
                ['other_receivable_id' => $this->id]
            );

            $this->refresh();
            if ($this->outstanding <= 0.005) {
                $this->status = 'lunas';
                $this->save();
            }

            Audit::log($this, 'payment_recorded', "Pembayaran piutang lain-lain {$this->code} dicatat oleh {$user->name} sebesar Rp ".number_format($amount, 0, ',', '.').".", actor: $user);

            return $payment;
        });
    }
}
