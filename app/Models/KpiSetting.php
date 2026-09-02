<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'mode',
    'min_hours_day',
    'min_hours_week',
    'min_hours_month',
    'average_margin_percent',
    'include_zero_hour_employees',
    'show_threshold_badges',
    'updated_by_user_id',
])]
class KpiSetting extends Model
{
    public const MODE_AVERAGE = 'average';

    public const MODE_TARGET = 'target';

    public const MODES = [
        self::MODE_AVERAGE => 'Rata-rata Tim (relatif)',
        self::MODE_TARGET => 'Target Tetap (angka baku perusahaan)',
    ];

    protected function casts(): array
    {
        return [
            'min_hours_day' => 'float',
            'min_hours_week' => 'float',
            'min_hours_month' => 'float',
            'average_margin_percent' => 'integer',
            'include_zero_hour_employees' => 'boolean',
            'show_threshold_badges' => 'boolean',
        ];
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_user_id');
    }

    /**
     * Pengaturan KPI selalu satu baris (singleton, id=1). Dibuat otomatis
     * dengan nilai default kalau belum pernah diatur sama sekali, jadi
     * halaman Dashboard KPI & Settings tidak perlu seeding manual.
     */
    public static function current(): self
    {
        return static::query()->firstOrCreate(['id' => 1]);
    }

    public function isTargetMode(): bool
    {
        return $this->mode === self::MODE_TARGET;
    }
}
