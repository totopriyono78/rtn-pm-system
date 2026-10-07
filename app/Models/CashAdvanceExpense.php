<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Rincian pengeluaran (pertanggungjawaban/realisasi) atas satu CashAdvance.
 * Kategori memakai daftar yang sama dengan ProjectBudgetLine::CATEGORIES
 * supaya konsisten dengan breakdown budget proyek yang sudah ada.
 */
#[Fillable([
    'cash_advance_id', 'category', 'description', 'amount', 'expense_date',
    'receipt_disk_path', 'receipt_original_name', 'receipt_mime_type', 'receipt_size_bytes',
])]
class CashAdvanceExpense extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
        ];
    }

    public function cashAdvance(): BelongsTo
    {
        return $this->belongsTo(CashAdvance::class);
    }
}
