<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Master akun Kas/Bank -- bagian ringan dari Finance & Accounting (SRS
 * 4.14). Dipakai sebagai "tempat uang" untuk mencatat transaksi riil:
 * pelunasan Invoice yang masuk (lihat Invoice::markPaid), pencairan Kasbon
 * yang keluar (lihat CashAdvance::disburse), dan transaksi manual lain
 * (operasional, dsb) lewat Finance\CashBankLedger. BUKAN General Ledger
 * penuh -- tidak ada jurnal/akun lawan/chart of account, cuma saldo +
 * daftar transaksi per akun.
 */
#[Fillable(['name', 'type', 'bank_name', 'account_number', 'opening_balance', 'is_active', 'created_by'])]
class CashBankAccount extends Model
{
    public const TYPES = [
        'cash' => 'Kas',
        'bank' => 'Bank',
    ];

    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CashBankTransaction::class);
    }

    public function getCurrentBalanceAttribute(): float
    {
        $totalIn = (float) $this->transactions()->where('type', 'in')->sum('amount');
        $totalOut = (float) $this->transactions()->where('type', 'out')->sum('amount');

        return round((float) $this->opening_balance + $totalIn - $totalOut, 2);
    }
}
