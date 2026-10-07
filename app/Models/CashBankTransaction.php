<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu baris transaksi kas/bank (masuk/keluar) -- bagian ringan dari
 * Finance & Accounting (SRS 4.14, Cash & Bank). Dibuat otomatis saat
 * Invoice ditandai lunas (kategori invoice_payment, tipe in) atau Kasbon
 * dicairkan (kategori cash_advance_disbursement, tipe out), atau manual
 * lewat Finance\CashBankLedger untuk transaksi lain (operasional,
 * pemasukan/pengeluaran lain, pengembalian sisa kasbon di luar Payroll).
 *
 * TIDAK ADA edit/delete sama sekali -- lebih ketat dari pola "no
 * destructive delete" di modul lain (Invoice/CashAdvance/Payroll masih
 * punya status pembatalan): ini buku kas, begitu salah catat, koreksinya
 * adalah entri balik (transaksi baru dengan arah berlawanan), bukan
 * mengubah baris yang sudah ada.
 *
 * Kategori vendor_payment (tipe out) dibuat otomatis saat Purchase Order
 * ditandai dibayar lewat Purchasing\ManagePurchaseOrders -- pasangan AP
 * (hutang vendor) dari AR (invoice_payment) yang sudah ada.
 */
#[Fillable(['cash_bank_account_id', 'type', 'category', 'amount', 'transaction_date', 'description', 'invoice_id', 'cash_advance_id', 'purchase_order_id', 'other_receivable_id', 'fixed_asset_id', 'created_by'])]
class CashBankTransaction extends Model
{
    public const TYPES = [
        'in' => 'Masuk',
        'out' => 'Keluar',
    ];

    public const CATEGORIES = [
        'invoice_payment' => 'Pelunasan Invoice',
        'cash_advance_disbursement' => 'Pencairan Kasbon',
        'cash_advance_return' => 'Pengembalian Sisa Kasbon',
        'vendor_payment' => 'Pembayaran ke Vendor',
        'other_receivable_given' => 'Pemberian Piutang Lain-lain',
        'other_receivable_payment' => 'Pembayaran Piutang Lain-lain',
        'fixed_asset_purchase' => 'Pembelian Aset Tetap',
        'operational_expense' => 'Pengeluaran Operasional',
        'other_income' => 'Pemasukan Lain',
        'other_expense' => 'Pengeluaran Lain',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'transaction_date' => 'date',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(CashBankAccount::class, 'cash_bank_account_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function cashAdvance(): BelongsTo
    {
        return $this->belongsTo(CashAdvance::class);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function otherReceivable(): BelongsTo
    {
        return $this->belongsTo(OtherReceivable::class);
    }

    public function fixedAsset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
}
