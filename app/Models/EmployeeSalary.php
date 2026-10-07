<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Komponen gaji tetap per karyawan (SRS 4.17) -- gaji pokok, tunjangan
 * tetap, dan tarif lembur per jam, diinput manual oleh Administrator.
 * Dipakai sebagai dasar perhitungan Payroll setiap bulan. Hanya karyawan
 * dengan is_active=true yang disertakan saat PayrollRun::generate().
 */
#[Fillable(['user_id', 'base_salary', 'fixed_allowance', 'overtime_hourly_rate', 'is_active', 'updated_by'])]
class EmployeeSalary extends Model
{
    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'fixed_allowance' => 'decimal:2',
            'overtime_hourly_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
