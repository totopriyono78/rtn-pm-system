<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['budget_id', 'chart_of_account_id', 'planned_amount', 'notes'])]
class BudgetLine extends Model
{
    protected function casts(): array
    {
        return [
            'planned_amount' => 'decimal:2',
        ];
    }

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function chartOfAccount(): BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class);
    }

    /**
     * Realisasi (actual) -- mutasi akun ini dari jurnal GL yang SUDAH
     * POSTED sepanjang tahun anggaran (period_year induknya), dengan
     * tanda disesuaikan ke normal_balance akun (sama persis logika
     * ChartOfAccount::postedBalance(), tapi dibatasi satu tahun
     * kalender, bukan sepanjang masa). Memanfaatkan auto-posting GL
     * (Invoice/PO/Kasbon/Payroll/Aset Tetap) yang sudah dibangun --
     * BUKAN input manual terpisah.
     */
    public function getActualAttribute(): float
    {
        $year = $this->budget->period_year;

        $sumDebit = (float) $this->chartOfAccount->journalEntryLines()
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->whereYear('entry_date', $year))
            ->sum('debit');
        $sumCredit = (float) $this->chartOfAccount->journalEntryLines()
            ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->whereYear('entry_date', $year))
            ->sum('credit');

        return $this->chartOfAccount->normal_balance === 'debit'
            ? round($sumDebit - $sumCredit, 2)
            : round($sumCredit - $sumDebit, 2);
    }

    public function getVarianceAttribute(): float
    {
        return round((float) $this->planned_amount - $this->actual, 2);
    }

    public function getUsagePercentAttribute(): ?float
    {
        if ((float) $this->planned_amount <= 0) {
            return null;
        }

        return round(($this->actual / (float) $this->planned_amount) * 100, 1);
    }
}
