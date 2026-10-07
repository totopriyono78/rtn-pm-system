<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Chart of Account (COA) -- bagian "General Ledger/Chart of Account" dari
 * Finance & Accounting (SRS 4.14). Master akun akuntansi flat (tanpa
 * hierarki parent/child untuk fase ini -- bisa ditambah nanti kalau
 * dibutuhkan, additive). `normal_balance` ditentukan otomatis dari `type`
 * lewat booted() hook, bukan field yang diisi manual, supaya konsisten
 * (Aset/Beban = normal debit, Kewajiban/Modal/Pendapatan = normal
 * kredit).
 */
#[Fillable(['code', 'name', 'type', 'is_active', 'created_by'])]
class ChartOfAccount extends Model
{
    public const TYPES = [
        'asset' => 'Aset',
        'liability' => 'Kewajiban',
        'equity' => 'Modal',
        'revenue' => 'Pendapatan',
        'expense' => 'Beban',
    ];

    public const TYPE_NORMAL_BALANCE = [
        'asset' => 'debit',
        'liability' => 'credit',
        'equity' => 'credit',
        'revenue' => 'credit',
        'expense' => 'debit',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (ChartOfAccount $account) {
            $account->normal_balance = self::TYPE_NORMAL_BALANCE[$account->type] ?? 'debit';
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function journalEntryLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    /**
     * Saldo berjalan dari jurnal yang sudah posted saja (draft/dibatalkan
     * tidak dihitung). Tanda hasil disesuaikan dengan normal_balance akun
     * supaya akun kredit (Kewajiban/Modal/Pendapatan) tetap tampil positif
     * saat saldonya wajar (kredit > debit).
     */
    public function postedBalance(): float
    {
        $sumDebit = (float) $this->journalEntryLines()->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted'))->sum('debit');
        $sumCredit = (float) $this->journalEntryLines()->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted'))->sum('credit');

        return $this->normal_balance === 'debit'
            ? round($sumDebit - $sumCredit, 2)
            : round($sumCredit - $sumDebit, 2);
    }

    public function isUsedInAnyJournal(): bool
    {
        return $this->journalEntryLines()->exists();
    }
}
