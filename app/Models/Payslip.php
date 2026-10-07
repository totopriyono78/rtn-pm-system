<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Slip gaji satu karyawan untuk satu PayrollRun (SRS 4.17). Komponen
 * otomatis: gaji pokok + tunjangan tetap (snapshot dari EmployeeSalary) dan
 * lembur (dihitung dari WorkLog), serta potongan kasbon belum lunas
 * (dihitung dari CashAdvance). Komponen manual: BPJS & PPh21 -- sengaja
 * TIDAK dihitung otomatis karena aturan pajak/BPJS butuh konfirmasi akurat,
 * bukan asumsi sistem.
 */
#[Fillable([
    'payroll_run_id', 'user_id', 'base_salary', 'fixed_allowance',
    'overtime_hours', 'overtime_rate', 'overtime_amount',
    'cash_advance_deduction', 'included_cash_advance_ids',
    'bpjs_deduction', 'pph21_deduction',
    'gross_amount', 'total_deduction', 'net_amount', 'notes',
])]
class Payslip extends Model
{
    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'fixed_allowance' => 'decimal:2',
            'overtime_hours' => 'decimal:2',
            'overtime_rate' => 'decimal:2',
            'overtime_amount' => 'decimal:2',
            'cash_advance_deduction' => 'decimal:2',
            'included_cash_advance_ids' => 'array',
            'bpjs_deduction' => 'decimal:2',
            'pph21_deduction' => 'decimal:2',
            'gross_amount' => 'decimal:2',
            'total_deduction' => 'decimal:2',
            'net_amount' => 'decimal:2',
        ];
    }

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Update kolom manual BPJS/PPh21 lalu hitung ulang total potongan &
     * gaji bersih. Hanya boleh dipanggil selagi PayrollRun masih draft.
     */
    public function updateManualDeductions(float $bpjsDeduction, float $pph21Deduction, ?string $notes = null): void
    {
        abort_unless($this->payrollRun->status === 'draft', 400, 'Slip gaji hanya dapat diubah selagi payroll run masih draft.');

        $this->bpjs_deduction = $bpjsDeduction;
        $this->pph21_deduction = $pph21Deduction;
        $this->notes = $notes;
        $this->total_deduction = round((float) $this->cash_advance_deduction + $bpjsDeduction + $pph21Deduction, 2);
        $this->net_amount = round((float) $this->gross_amount - $this->total_deduction, 2);
        $this->save();
    }

    /**
     * Tutup (status "selesai") semua CashAdvance yang potongannya sudah
     * dihitung pada slip gaji ini, supaya tidak terpotong dobel di payroll
     * bulan berikutnya. Dipanggil saat PayrollRun::finalize().
     */
    public function closeIncludedCashAdvances(User $closer): void
    {
        $ids = $this->included_cash_advance_ids ?? [];

        if (empty($ids)) {
            return;
        }

        CashAdvance::whereIn('id', $ids)
            ->whereIn('status', ['dicairkan', 'dipertanggungjawabkan'])
            ->get()
            ->each(function (CashAdvance $cashAdvance) use ($closer) {
                $cashAdvance->status = 'selesai';
                $cashAdvance->closed_by = $closer->id;
                $cashAdvance->closed_at = now();
                $cashAdvance->save();
            });
    }

    /**
     * Total jam lembur dalam periode: untuk setiap hari, jumlah durasi
     * WorkLog user tersebut yang melebihi ambang jam kerja normal/hari
     * (KpiSetting::min_hours_day, default 8) dihitung sebagai lembur.
     */
    public static function calculateOvertimeHours(int $userId, Carbon $periodStart, Carbon $periodEnd, float $dailyThresholdHours): float
    {
        $dailyMinutes = WorkLog::where('user_id', $userId)
            ->whereBetween('log_date', [$periodStart->toDateString(), $periodEnd->toDateString()])
            ->select('log_date', DB::raw('SUM(duration_minutes) as minutes'))
            ->groupBy('log_date')
            ->pluck('minutes', 'log_date');

        $thresholdMinutes = $dailyThresholdHours * 60;

        $overtimeMinutes = $dailyMinutes->sum(fn ($minutes) => max(0, (float) $minutes - $thresholdMinutes));

        return round($overtimeMinutes / 60, 2);
    }

    /**
     * Total saldo kasbon yang masih harus dipotong dari gaji: CashAdvance
     * berstatus dicairkan/dipertanggungjawabkan (uang sudah keluar, belum
     * ditutup) dengan saldo positif (teknisi masih berhutang). Status
     * "selesai" tidak disertakan karena sudah pernah ditutup sebelumnya.
     *
     * @return array{0: float, 1: array<int, int>}
     */
    public static function calculateCashAdvanceDeduction(int $userId): array
    {
        $cashAdvances = CashAdvance::where('requested_by', $userId)
            ->whereIn('status', ['dicairkan', 'dipertanggungjawabkan'])
            ->get()
            ->filter(fn (CashAdvance $cashAdvance) => $cashAdvance->balance > 0);

        $total = round((float) $cashAdvances->sum('balance'), 2);
        $ids = $cashAdvances->pluck('id')->values()->all();

        return [$total, $ids];
    }
}
