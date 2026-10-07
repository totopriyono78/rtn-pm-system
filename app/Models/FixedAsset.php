<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use App\Support\Audit;

/**
 * Asset Management -- bagian terakhir Finance & Accounting (SRS 4.14).
 * Register aset tetap milik PERUSAHAAN (kendaraan, peralatan teknis,
 * peralatan kantor, dll) untuk tujuan akuntansi/depresiasi. BUKAN
 * `PumpAsset` (Master Unit Pompa) -- itu aset milik KLIEN yang
 * di-maintain PT RTN, entity yang sama sekali berbeda.
 *
 * Dua cara masuk ke register: (1) "Pembelian Baru" -- uang keluar dari
 * Kas/Bank, auto-posting GL (Debit Aset Tetap, Kredit Kas & Bank), lihat
 * createWithAcquisition(); (2) "Hanya Catat" -- murni mendaftarkan aset
 * yang SUDAH dimiliki perusahaan sebelum sistem ini ada, tanpa transaksi
 * kas/jurnal apa pun (supaya tidak menciptakan pengeluaran kas palsu
 * untuk aset yang sudah lama dibeli).
 *
 * Depresiasi garis lurus (straight-line) sederhana, dijalankan manual per
 * periode lewat runMonthlyDepreciation() (aksi batch, bukan job
 * terjadwal otomatis -- konsisten dengan PayrollRun yang juga dijalankan
 * manual per periode oleh Administrator, bukan cron). Satu jurnal GL
 * teragregasi dibuat per run (Debit Beban Depresiasi, Kredit Akumulasi
 * Depresiasi) -- bukan satu jurnal per aset, supaya Buku Besar tidak
 * dipenuhi baris kecil-kecil kalau asetnya banyak.
 *
 * Pelepasan aset (dijual/rusak/dihapuskan) SENGAJA murni status, TANPA
 * auto-posting GL -- menghitung laba/rugi pelepasan (proceeds vs nilai
 * buku) butuh keputusan akuntansi tersendiri (bagaimana akun laba/rugi
 * pelepasan dipetakan) yang belum diputuskan; kalau dibutuhkan, dicatat
 * manual lewat Entry Voucher.
 */
#[Fillable([
    'code', 'name', 'category', 'acquisition_date', 'acquisition_cost', 'salvage_value', 'useful_life_years',
    'accumulated_depreciation', 'last_depreciated_period', 'unit_id', 'location', 'status',
    'disposed_at', 'disposed_by', 'disposal_notes', 'notes', 'created_by',
])]
class FixedAsset extends Model
{
    public const CATEGORIES = [
        'kendaraan' => 'Kendaraan',
        'peralatan_teknis' => 'Peralatan Teknis',
        'peralatan_kantor' => 'Peralatan Kantor',
        'lainnya' => 'Lainnya',
    ];

    public const STATUSES = [
        'aktif' => 'Aktif',
        'dijual' => 'Dijual',
        'rusak' => 'Rusak/Tidak Terpakai',
        'dihapuskan' => 'Dihapuskan',
    ];

    protected function casts(): array
    {
        return [
            'acquisition_date' => 'date',
            'acquisition_cost' => 'decimal:2',
            'salvage_value' => 'decimal:2',
            'accumulated_depreciation' => 'decimal:2',
            'disposed_at' => 'datetime',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function disposer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disposed_by');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function getDepreciableBaseAttribute(): float
    {
        return round((float) $this->acquisition_cost - (float) $this->salvage_value, 2);
    }

    public function getMonthlyDepreciationAttribute(): float
    {
        if ($this->useful_life_years <= 0) {
            return 0.0;
        }

        return round($this->depreciable_base / ($this->useful_life_years * 12), 2);
    }

    public function getBookValueAttribute(): float
    {
        return round((float) $this->acquisition_cost - (float) $this->accumulated_depreciation, 2);
    }

    public function getIsFullyDepreciatedAttribute(): bool
    {
        return $this->book_value <= (float) $this->salvage_value + 0.005;
    }

    public static function generateCode(): string
    {
        $year = now()->format('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;

        return sprintf('AST-%s-%04d', $year, $count);
    }

    /**
     * Daftarkan aset baru. $recordPurchase=true berarti ini pembelian
     * baru (uang keluar dari Kas/Bank, auto-posting GL) -- $account wajib
     * diisi kalau true. $recordPurchase=false berarti murni mendaftarkan
     * aset yang sudah dimiliki, tanpa transaksi kas/jurnal.
     */
    public static function createWithAcquisition(array $data, User $user, bool $recordPurchase, ?CashBankAccount $account = null): self
    {
        abort_if($recordPurchase && ! $account, 400, 'Pilih akun Kas/Bank untuk mencatat pembelian aset baru.');

        return DB::transaction(function () use ($data, $user, $recordPurchase, $account) {
            $asset = static::create(array_merge($data, [
                'code' => static::generateCode(),
                'status' => 'aktif',
                'accumulated_depreciation' => 0,
                'created_by' => $user->id,
            ]));

            if ($recordPurchase) {
                CashBankTransaction::create([
                    'cash_bank_account_id' => $account->id,
                    'type' => 'out',
                    'category' => 'fixed_asset_purchase',
                    'amount' => $asset->acquisition_cost,
                    'transaction_date' => $asset->acquisition_date->toDateString(),
                    'description' => "Pembelian aset tetap {$asset->code} -- {$asset->name}",
                    'fixed_asset_id' => $asset->id,
                    'created_by' => $user->id,
                ]);

                JournalEntry::createAutoPosted(
                    "Pembelian aset tetap {$asset->code} -- {$asset->name}",
                    [
                        ['chart_of_account_id' => JournalEntry::account('aset_tetap')->id, 'debit' => $asset->acquisition_cost, 'credit' => 0],
                        ['chart_of_account_id' => JournalEntry::account('kas_bank')->id, 'debit' => 0, 'credit' => $asset->acquisition_cost],
                    ],
                    $user,
                    $asset->code,
                    ['fixed_asset_id' => $asset->id]
                );
            }

            return $asset;
        });
    }

    public function dispose(User $user, string $status, ?string $notes = null): void
    {
        abort_unless($this->status === 'aktif', 400, 'Hanya aset berstatus aktif yang dapat dilepaskan.');
        abort_unless(in_array($status, ['dijual', 'rusak', 'dihapuskan'], true), 400, 'Status pelepasan tidak valid.');

        $this->status = $status;
        $this->disposed_at = now();
        $this->disposed_by = $user->id;
        $this->disposal_notes = $notes;
        $this->save();

        Audit::log($this, 'disposed', "Aset {$this->code} ({$this->name}) dilepaskan ({$status}) oleh {$user->name}.", actor: $user);
    }

    /**
     * Jalankan depresiasi bulan ini untuk SEMUA aset aktif yang belum
     * didepresiasi di periode ini dan belum fully-depreciated. Satu
     * jurnal GL teragregasi (bukan per aset). Mengembalikan null kalau
     * tidak ada aset yang perlu didepresiasi (bukan error -- kondisi
     * wajar kalau semua aset sudah fully-depreciated atau belum ada
     * aset aktif sama sekali).
     */
    public static function runMonthlyDepreciation(User $user, string $period): ?JournalEntry
    {
        $assets = static::where('status', 'aktif')
            ->where(fn ($q) => $q->whereNull('last_depreciated_period')->orWhere('last_depreciated_period', '!=', $period))
            ->get()
            ->filter(fn (self $asset) => ! $asset->is_fully_depreciated);

        if ($assets->isEmpty()) {
            return null;
        }

        return DB::transaction(function () use ($assets, $user, $period) {
            $totalDepreciation = 0.0;

            foreach ($assets as $asset) {
                $amount = min($asset->monthly_depreciation, $asset->depreciable_base - (float) $asset->accumulated_depreciation);
                $amount = round(max($amount, 0), 2);

                if ($amount <= 0) {
                    continue;
                }

                $asset->accumulated_depreciation = round((float) $asset->accumulated_depreciation + $amount, 2);
                $asset->last_depreciated_period = $period;
                $asset->save();

                $totalDepreciation = round($totalDepreciation + $amount, 2);
            }

            if ($totalDepreciation <= 0) {
                return null;
            }

            return JournalEntry::createAutoPosted(
                "Depresiasi aset tetap periode {$period} ({$assets->count()} aset)",
                [
                    ['chart_of_account_id' => JournalEntry::account('beban_depresiasi')->id, 'debit' => $totalDepreciation, 'credit' => 0],
                    ['chart_of_account_id' => JournalEntry::account('akumulasi_depresiasi')->id, 'debit' => 0, 'credit' => $totalDepreciation],
                ],
                $user,
                $period,
                []
            );
        });
    }
}
