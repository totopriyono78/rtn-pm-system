<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
use App\Support\Audit;
use App\Support\Notifier;

/**
 * Kasbon / Cash Advance & Expense teknisi (SRS 4.16). Alur: diajukan ->
 * disetujui/ditolak/dibatalkan -> dicairkan -> (teknisi input rincian
 * pengeluaran) -> dipertanggungjawabkan -> selesai. Pencairan tercatat
 * di Buku Kas/Bank (SRS 4.14) DAN auto-posting ke jurnal GL (SRS 4.14,
 * lanjutan GL/Chart of Account) -- lihat disburse().
 */
#[Fillable([
    'project_id', 'requested_by', 'amount_requested', 'purpose', 'status',
    'approved_by', 'approved_at', 'rejection_reason',
    'disbursed_at', 'disbursed_by', 'closed_at', 'closed_by',
])]
class CashAdvance extends Model
{
    use HasFactory;

    public const STATUSES = [
        'diajukan' => 'Menunggu Approval',
        'ditolak' => 'Ditolak',
        'dibatalkan' => 'Dibatalkan',
        'disetujui' => 'Disetujui, Menunggu Pencairan',
        'dicairkan' => 'Sudah Dicairkan',
        'dipertanggungjawabkan' => 'Menunggu Verifikasi',
        'selesai' => 'Selesai',
    ];

    protected function casts(): array
    {
        return [
            'amount_requested' => 'decimal:2',
            'approved_at' => 'datetime',
            'disbursed_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
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

    public function disburser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }

    public function closer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(CashAdvanceExpense::class);
    }

    public function getTotalExpenseAttribute(): float
    {
        return (float) $this->expenses()->sum('amount');
    }

    /**
     * Positif: teknisi masih berhutang / harus mengembalikan sisa ke kantor.
     * Negatif: kantor perlu mengganti kekurangan (pengeluaran teknisi lebih
     * besar dari kasbon yang diterima).
     */
    public function getBalanceAttribute(): float
    {
        return (float) $this->amount_requested - $this->total_expense;
    }

    public function approve(User $approver): void
    {
        abort_unless($this->status === 'diajukan', 400, 'Hanya kasbon berstatus menunggu approval yang dapat disetujui.');
        $this->status = 'disetujui';
        $this->approved_by = $approver->id;
        $this->approved_at = now();
        $this->save();

        Audit::log($this, 'approved', "Kasbon untuk {$this->requester->name} disetujui oleh {$approver->name}.");
        Notifier::user($this->requester, 'Kasbon Disetujui', "Pengajuan kasbon Anda telah disetujui, menunggu pencairan.", route('teknisi.cash-advances'));
    }

    public function reject(User $approver, string $reason): void
    {
        abort_unless($this->status === 'diajukan', 400, 'Hanya kasbon berstatus menunggu approval yang dapat ditolak.');
        $this->status = 'ditolak';
        $this->approved_by = $approver->id;
        $this->approved_at = now();
        $this->rejection_reason = $reason;
        $this->save();

        Audit::log($this, 'rejected', "Kasbon untuk {$this->requester->name} ditolak oleh {$approver->name}. Alasan: {$reason}");
        Notifier::user($this->requester, 'Kasbon Ditolak', "Pengajuan kasbon Anda ditolak. Alasan: {$reason}", route('teknisi.cash-advances'));
    }

    public function cancel(): void
    {
        abort_unless($this->status === 'diajukan', 400, 'Hanya kasbon yang masih menunggu approval yang dapat dibatalkan.');
        $this->status = 'dibatalkan';
        $this->save();

        Audit::log($this, 'cancelled', "Kasbon untuk {$this->requester->name} dibatalkan.");
    }

    /**
     * Auto-posting GL (SRS 4.14, lanjutan GL/Chart of Account, keputusan
     * scope eksplisit user 2026-10-06): Debit Uang Muka/Kasbon Karyawan
     * (aset, piutang ke karyawan), Kredit Kas & Bank.
     */
    public function disburse(User $user, CashBankAccount $account): void
    {
        abort_unless($this->status === 'disetujui', 400, 'Hanya kasbon yang sudah disetujui yang dapat dicairkan.');

        DB::transaction(function () use ($user, $account) {
            $this->status = 'dicairkan';
            $this->disbursed_by = $user->id;
            $this->disbursed_at = now();
            $this->save();

            // Catat sebagai transaksi kas/bank riil (keluar) -- SRS 4.14 Cash & Bank.
            CashBankTransaction::create([
                'cash_bank_account_id' => $account->id,
                'type' => 'out',
                'category' => 'cash_advance_disbursement',
                'amount' => $this->amount_requested,
                'transaction_date' => now()->toDateString(),
                'description' => "Pencairan kasbon untuk {$this->requester->name}" . ($this->purpose ? " -- {$this->purpose}" : ''),
                'cash_advance_id' => $this->id,
                'created_by' => $user->id,
            ]);

            JournalEntry::createAutoPosted(
                "Pencairan kasbon untuk {$this->requester->name}",
                [
                    ['chart_of_account_id' => JournalEntry::account('kasbon_karyawan')->id, 'debit' => $this->amount_requested, 'credit' => 0],
                    ['chart_of_account_id' => JournalEntry::account('kas_bank')->id, 'debit' => 0, 'credit' => $this->amount_requested],
                ],
                $user,
                null,
                ['cash_advance_id' => $this->id]
            );

            Audit::log($this, 'disbursed', "Kasbon untuk {$this->requester->name} dicairkan oleh {$user->name} dari akun {$account->name}.", actor: $user);
        });

        Notifier::user($this->requester, 'Kasbon Dicairkan', "Kasbon Anda sebesar Rp ".number_format((float) $this->amount_requested, 0, ',', '.')." telah dicairkan.", route('teknisi.cash-advances'));
    }

    public function submitForReview(): void
    {
        abort_unless($this->status === 'dicairkan', 400, 'Hanya kasbon yang sudah dicairkan yang dapat diajukan pertanggungjawabannya.');
        abort_if($this->expenses()->count() === 0, 400, 'Tambahkan minimal satu rincian pengeluaran sebelum mengajukan pertanggungjawaban.');
        $this->status = 'dipertanggungjawabkan';
        $this->save();

        Audit::log($this, 'submitted', "Pertanggungjawaban kasbon untuk {$this->requester->name} diajukan, menunggu verifikasi.");
        Notifier::permission('manage-cash-advances', 'Pertanggungjawaban Kasbon Menunggu Verifikasi', "Pertanggungjawaban kasbon {$this->requester->name} menunggu verifikasi Anda.", route('admin.cash-advances'));
    }

    public function close(User $user): void
    {
        abort_unless($this->status === 'dipertanggungjawabkan', 400, 'Hanya kasbon yang sudah diajukan pertanggungjawabannya yang dapat ditutup.');
        $this->status = 'selesai';
        $this->closed_by = $user->id;
        $this->closed_at = now();
        $this->save();

        Audit::log($this, 'approved', "Pertanggungjawaban kasbon untuk {$this->requester->name} diverifikasi & ditutup oleh {$user->name}.");
    }
}
