<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Support\Audit;

/**
 * Jurnal umum (Entry Voucher) -- bagian "General Ledger/Chart of Account"
 * dari Finance & Accounting (SRS 4.14). Dua jalur pembuatan:
 *
 * 1. MANUAL oleh Administrator lewat form Jurnal Umum (status awal
 *    draft, boleh diedit bebas sebelum posting) -- jalur asli, keputusan
 *    eksplisit user 2026-10-06.
 * 2. OTOMATIS (`source` = 'otomatis') lewat `createAutoPosted()`, dipanggil
 *    dari Invoice::markPaid(), PurchaseOrder::payVendor(),
 *    CashAdvance::disburse(), dan PayrollRun::finalize() -- keputusan
 *    scope eksplisit user 2026-10-06 (membalik keputusan "manual dulu"
 *    di atas), dengan pemetaan akun debit/kredit tetap per jenis
 *    transaksi (lihat AUTO_POST_ACCOUNTS). Jurnal otomatis dibuat
 *    LANGSUNG berstatus posted (tidak lewat draft) karena transaksi
 *    sumbernya sendiri sudah final (invoice lunas, PO dibayar, kasbon
 *    dicairkan, payroll difinalisasi) -- tidak ada "draft jurnal" yang
 *    mengambang menunggu sumbernya sendiri sudah final.
 *
 * Alur status: draft (boleh diedit bebas, belum masuk hitungan
 * saldo/laporan) -> posted (lock permanen, baru dihitung di Buku Besar &
 * Neraca Saldo) atau dibatalkan (hanya dari draft). Begitu posted, TIDAK
 * ADA edit/batal -- koreksi dilakukan lewat jurnal baru (jurnal pembalik),
 * konsisten dengan kaidah "tidak ada edit/delete setelah final" yang
 * sudah dipakai di CashBankTransaction. Ini juga berarti jurnal otomatis
 * tidak pernah bisa dibatalkan dari UI -- kalau transaksi sumbernya salah,
 * koreksi lewat jurnal pembalik manual yang baru, bukan membatalkan
 * jurnal otomatis yang sudah posted.
 */
#[Fillable([
    'entry_number', 'entry_date', 'reference', 'description', 'status', 'created_by',
    'source', 'invoice_id', 'purchase_order_id', 'cash_advance_id', 'payroll_run_id', 'other_receivable_id', 'fixed_asset_id',
])]
class JournalEntry extends Model
{
    public const STATUSES = [
        'draft' => 'Draft',
        'posted' => 'Posted',
        'dibatalkan' => 'Dibatalkan',
    ];

    public const SOURCES = [
        'manual' => 'Manual',
        'otomatis' => 'Otomatis',
    ];

    /**
     * Pemetaan kode akun Chart of Account tetap per jenis transaksi yang
     * di-auto-post -- dipilih dari 16 akun default ChartOfAccountSeeder.
     * Kalau akun dengan kode ini dihapus/diganti kodenya, `account()` di
     * bawah akan abort dengan pesan jelas (bukan exception SQL mentah).
     */
    public const AUTO_POST_ACCOUNTS = [
        'kas_bank' => '1000',
        'piutang_usaha' => '1100',
        'piutang_lain' => '1150',
        'kasbon_karyawan' => '1300',
        'aset_tetap' => '1500',
        'akumulasi_depresiasi' => '1550',
        'hutang_gaji' => '2100',
        'hutang_pajak' => '2200',
        'pendapatan_jasa' => '4000',
        'beban_gaji' => '5000',
        'beban_operasional_proyek' => '5100',
        'beban_depresiasi' => '5400',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'posted_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function cashAdvance(): BelongsTo
    {
        return $this->belongsTo(CashAdvance::class);
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function otherReceivable(): BelongsTo
    {
        return $this->belongsTo(OtherReceivable::class);
    }

    public function fixedAsset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class);
    }

    public function sourceLabel(): string
    {
        return self::SOURCES[$this->source] ?? $this->source;
    }

    public function getTotalDebitAttribute(): float
    {
        return round((float) $this->lines->sum('debit'), 2);
    }

    public function getTotalCreditAttribute(): float
    {
        return round((float) $this->lines->sum('credit'), 2);
    }

    public function getIsBalancedAttribute(): bool
    {
        return $this->total_debit > 0 && abs($this->total_debit - $this->total_credit) < 0.005;
    }

    public static function generateNumberSuggestion(): string
    {
        $year = now()->format('Y');
        $count = static::whereYear('entry_date', $year)->count() + 1;

        return sprintf('JE-%s-%04d', $year, $count);
    }

    /**
     * Posting jurnal -- hanya dari draft, dan hanya kalau total debit =
     * total kredit (validasi ulang di sini, bukan cuma di form) serta
     * minimal ada 2 baris. Setelah posted, baris jurnal ikut dihitung di
     * Buku Besar & Neraca Saldo dan TIDAK BISA diubah lagi.
     */
    public function post(User $user): void
    {
        abort_unless($this->status === 'draft', 400, 'Hanya jurnal berstatus draft yang dapat di-posting.');
        abort_if($this->lines->count() < 2, 400, 'Jurnal butuh minimal 2 baris (debit dan kredit).');
        abort_unless($this->is_balanced, 400, 'Total debit dan kredit harus sama (balance) sebelum posting.');

        $this->status = 'posted';
        $this->posted_by = $user->id;
        $this->posted_at = now();
        $this->save();

        Audit::log($this, 'posted', "Jurnal {$this->entry_number} diposting oleh {$user->name}.", actor: $user);
    }

    public function cancel(User $user): void
    {
        abort_unless($this->status === 'draft', 400, 'Hanya jurnal berstatus draft yang dapat dibatalkan. Jurnal yang sudah posted dikoreksi lewat jurnal pembalik baru.');

        $this->status = 'dibatalkan';
        $this->cancelled_by = $user->id;
        $this->cancelled_at = now();
        $this->save();

        Audit::log($this, 'cancelled', "Jurnal {$this->entry_number} dibatalkan oleh {$user->name}.");
    }

    /**
     * Ganti seluruh baris jurnal sekaligus (dipakai saat menyimpan form
     * Entry Voucher yang masih draft) -- hapus semua baris lama lalu buat
     * ulang dari array yang divalidasi. Hanya boleh selagi draft.
     */
    public function replaceLines(array $lines): void
    {
        abort_unless($this->status === 'draft', 400, 'Hanya jurnal berstatus draft yang baris-barisnya dapat diubah.');

        $this->lines()->delete();
        foreach ($lines as $line) {
            $this->lines()->create([
                'chart_of_account_id' => $line['chart_of_account_id'],
                'debit' => $line['debit'] ?? 0,
                'credit' => $line['credit'] ?? 0,
                'description' => $line['description'] ?? null,
            ]);
        }
        $this->load('lines');
    }

    /**
     * Ambil ChartOfAccount yang dipakai untuk auto-posting lewat key di
     * AUTO_POST_ACCOUNTS (bukan ID langsung, supaya kode tetap jadi satu
     * sumber kebenaran). Abort dengan pesan jelas kalau akunnya tidak
     * ditemukan (kode diubah/akun dihapus dari Chart of Account) --
     * dibanding membiarkan NOT NULL constraint SQL gagal mentah.
     */
    public static function account(string $key): ChartOfAccount
    {
        $code = self::AUTO_POST_ACCOUNTS[$key] ?? abort(500, "Kunci akun auto-posting tidak dikenal: {$key}.");

        return ChartOfAccount::where('code', $code)->first()
            ?? abort(500, "Akun Chart of Account dengan kode {$code} tidak ditemukan -- auto-posting jurnal gagal. Periksa halaman Chart of Account.");
    }

    /**
     * Buat jurnal otomatis LANGSUNG berstatus posted -- dipanggil dari
     * dalam DB::transaction() method pemanggil (Invoice::markPaid(), dkk)
     * supaya atomic dengan perubahan status transaksi sumbernya. Validasi
     * balance tetap dijalankan sebagai pertahanan (kalaupun pemetaan akun
     * di atas seharusnya selalu balance by construction) -- konsisten
     * dengan post() yang juga validasi ulang, bukan percaya form/caller.
     *
     * $sourceLinks: salah satu dari ['invoice_id' => ..], ['purchase_order_id' => ..],
     * ['cash_advance_id' => ..], atau ['payroll_run_id' => ..] -- dipakai UI untuk
     * link balik ke dokumen sumber.
     */
    public static function createAutoPosted(string $description, array $lines, ?User $user, ?string $reference, array $sourceLinks): self
    {
        $totalDebit = round(array_sum(array_map(fn ($l) => (float) ($l['debit'] ?? 0), $lines)), 2);
        $totalCredit = round(array_sum(array_map(fn ($l) => (float) ($l['credit'] ?? 0), $lines)), 2);
        abort_unless($totalDebit > 0 && abs($totalDebit - $totalCredit) < 0.005, 500, 'Auto-posting jurnal gagal: debit dan kredit tidak balance ('.$totalDebit.' vs '.$totalCredit.'). Ini bug pemetaan akun, bukan kesalahan input user.');

        $entry = static::create(array_merge([
            'entry_number' => static::generateNumberSuggestion(),
            'entry_date' => now()->toDateString(),
            'reference' => $reference,
            'description' => $description,
            'status' => 'posted',
            'source' => 'otomatis',
            'created_by' => $user?->id,
            'posted_by' => $user?->id,
        ], $sourceLinks));

        $entry->posted_at = now();
        $entry->save();

        foreach ($lines as $line) {
            $entry->lines()->create([
                'chart_of_account_id' => $line['chart_of_account_id'],
                'debit' => $line['debit'] ?? 0,
                'credit' => $line['credit'] ?? 0,
                'description' => $line['description'] ?? null,
            ]);
        }

        return $entry;
    }
}
