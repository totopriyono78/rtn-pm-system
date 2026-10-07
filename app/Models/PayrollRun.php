<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Support\Audit;

/**
 * Payroll bulanan (SRS 4.17). Satu PayrollRun = satu periode (bulan+tahun),
 * berisi banyak Payslip (satu per karyawan dengan EmployeeSalary aktif).
 * Alur status: draft -> finalized (kasbon terkait otomatis ditutup) atau
 * dibatalkan (hanya selagi masih draft). Tidak ada delete -- konsisten
 * dengan Invoice/CashAdvance, status "dibatalkan" dipakai sebagai ganti.
 */
#[Fillable(['period_month', 'period_year', 'status', 'generated_by', 'finalized_by', 'finalized_at', 'cancelled_by', 'cancelled_at', 'notes'])]
class PayrollRun extends Model
{
    public const STATUSES = [
        'draft' => 'Draft',
        'finalized' => 'Final',
        'dibatalkan' => 'Dibatalkan',
    ];

    protected function casts(): array
    {
        return [
            'finalized_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function finalizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    public function canceller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function getPeriodLabelAttribute(): string
    {
        return Carbon::createFromDate((int) $this->period_year, (int) $this->period_month, 1)->translatedFormat('F Y');
    }

    public function getPeriodStartAttribute(): Carbon
    {
        return Carbon::createFromDate((int) $this->period_year, (int) $this->period_month, 1)->startOfMonth();
    }

    public function getPeriodEndAttribute(): Carbon
    {
        return $this->period_start->copy()->endOfMonth();
    }

    public function getTotalNetAttribute(): float
    {
        return (float) $this->payslips()->sum('net_amount');
    }

    /**
     * Generate/regenerate payslip untuk semua karyawan dengan EmployeeSalary
     * aktif. Lembur dihitung otomatis dari WorkLog (jam di atas ambang
     * KpiSetting::min_hours_day per hari), potongan kasbon dihitung otomatis
     * dari CashAdvance yang masih berstatus dicairkan/dipertanggungjawabkan
     * dengan saldo positif. BPJS & PPh21 dibiarkan 0 saat pertama kali
     * dibuat -- diisi manual oleh Administrator per karyawan. Regenerate
     * (selagi masih draft) mempertahankan BPJS/PPh21 yang sudah diisi.
     */
    public function generate(): void
    {
        abort_unless($this->status === 'draft', 400, 'Hanya payroll run berstatus draft yang dapat di-generate ulang.');

        $dailyThreshold = (float) (KpiSetting::current()->min_hours_day ?: 8);
        $periodStart = $this->period_start;
        $periodEnd = $this->period_end;

        $employeeSalaries = EmployeeSalary::where('is_active', true)->with('user')->get();

        foreach ($employeeSalaries as $salary) {
            $overtimeHours = Payslip::calculateOvertimeHours($salary->user_id, $periodStart, $periodEnd, $dailyThreshold);
            $overtimeAmount = round($overtimeHours * (float) $salary->overtime_hourly_rate, 2);

            [$cashAdvanceDeduction, $includedCashAdvanceIds] = Payslip::calculateCashAdvanceDeduction($salary->user_id);

            $baseSalary = (float) $salary->base_salary;
            $fixedAllowance = (float) $salary->fixed_allowance;
            $grossAmount = round($baseSalary + $fixedAllowance + $overtimeAmount, 2);

            $existing = Payslip::where('payroll_run_id', $this->id)->where('user_id', $salary->user_id)->first();
            $bpjsDeduction = $existing ? (float) $existing->bpjs_deduction : 0.0;
            $pph21Deduction = $existing ? (float) $existing->pph21_deduction : 0.0;
            $totalDeduction = round($cashAdvanceDeduction + $bpjsDeduction + $pph21Deduction, 2);
            $netAmount = round($grossAmount - $totalDeduction, 2);

            Payslip::updateOrCreate(
                ['payroll_run_id' => $this->id, 'user_id' => $salary->user_id],
                [
                    'base_salary' => $baseSalary,
                    'fixed_allowance' => $fixedAllowance,
                    'overtime_hours' => $overtimeHours,
                    'overtime_rate' => (float) $salary->overtime_hourly_rate,
                    'overtime_amount' => $overtimeAmount,
                    'cash_advance_deduction' => $cashAdvanceDeduction,
                    'included_cash_advance_ids' => $includedCashAdvanceIds,
                    'bpjs_deduction' => $bpjsDeduction,
                    'pph21_deduction' => $pph21Deduction,
                    'gross_amount' => $grossAmount,
                    'total_deduction' => $totalDeduction,
                    'net_amount' => $netAmount,
                ]
            );
        }

        // Hapus payslip draft untuk karyawan yang sudah tidak aktif lagi (kalau regenerate).
        $activeUserIds = $employeeSalaries->pluck('user_id');
        Payslip::where('payroll_run_id', $this->id)->whereNotIn('user_id', $activeUserIds)->delete();
    }

    /**
     * Auto-posting GL (SRS 4.14, lanjutan GL/Chart of Account, keputusan
     * scope eksplisit user 2026-10-06): Debit Beban Gaji & Tunjangan
     * (total gross semua payslip), Kredit Uang Muka/Kasbon Karyawan
     * (total potongan kasbon -- mengurangi piutang ke karyawan yang
     * sudah dibukukan saat CashAdvance::disburse()), Kredit Hutang
     * Pajak (gabungan BPJS + PPh21, tidak ada akun BPJS terpisah di
     * Chart of Account default), dan Kredit Hutang Gaji (total net --
     * gaji belum dibayar fisik, konsisten dengan tidak ada transaksi
     * Kas/Bank di sini karena pembayaran gaji tetap manual di luar
     * sistem). Baris dengan total 0 (mis. tidak ada kasbon yang
     * dipotong bulan ini) dilewati -- tetap balance karena gross selalu
     * sama dengan total_deduction + net per payslip (lihat Payslip).
     */
    public function finalize(User $user): void
    {
        abort_unless($this->status === 'draft', 400, 'Hanya payroll run berstatus draft yang dapat difinalisasi.');
        abort_if($this->payslips()->count() === 0, 400, 'Belum ada slip gaji pada payroll run ini.');

        DB::transaction(function () use ($user) {
            foreach ($this->payslips as $payslip) {
                $payslip->closeIncludedCashAdvances($user);
            }

            $this->status = 'finalized';
            $this->finalized_by = $user->id;
            $this->finalized_at = now();
            $this->save();

            $totalGross = round((float) $this->payslips()->sum('gross_amount'), 2);
            $totalCashAdvance = round((float) $this->payslips()->sum('cash_advance_deduction'), 2);
            $totalTaxBpjs = round((float) $this->payslips()->sum('bpjs_deduction') + (float) $this->payslips()->sum('pph21_deduction'), 2);
            $totalNet = round((float) $this->payslips()->sum('net_amount'), 2);

            $lines = [
                ['chart_of_account_id' => JournalEntry::account('beban_gaji')->id, 'debit' => $totalGross, 'credit' => 0],
            ];
            if ($totalCashAdvance > 0) {
                $lines[] = ['chart_of_account_id' => JournalEntry::account('kasbon_karyawan')->id, 'debit' => 0, 'credit' => $totalCashAdvance];
            }
            if ($totalTaxBpjs > 0) {
                $lines[] = ['chart_of_account_id' => JournalEntry::account('hutang_pajak')->id, 'debit' => 0, 'credit' => $totalTaxBpjs];
            }
            if ($totalNet > 0) {
                $lines[] = ['chart_of_account_id' => JournalEntry::account('hutang_gaji')->id, 'debit' => 0, 'credit' => $totalNet];
            }

            JournalEntry::createAutoPosted(
                "Payroll {$this->period_label}",
                $lines,
                $user,
                null,
                ['payroll_run_id' => $this->id]
            );

            Audit::log($this, 'finalized', "Payroll {$this->period_label} difinalisasi oleh {$user->name}.", actor: $user);
        });
    }

    public function cancel(User $user): void
    {
        abort_unless($this->status === 'draft', 400, 'Payroll run yang sudah final tidak dapat dibatalkan dari sini -- hanya draft.');

        $this->status = 'dibatalkan';
        $this->cancelled_by = $user->id;
        $this->cancelled_at = now();
        $this->save();

        Audit::log($this, 'cancelled', "Payroll {$this->period_label} dibatalkan oleh {$user->name}.");
    }
}
