<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['activity_id', 'user_id', 'assignment_id', 'type', 'report_date', 'start_time', 'end_time', 'duration_minutes', 'notes'])]
class Report extends Model
{
    use HasFactory;

    public const TYPES = [
        'daily' => 'Daily Report',
        'final' => 'Final Report',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Report $report) {
            if ($report->start_time && $report->end_time) {
                $start = strtotime($report->report_date->format('Y-m-d').' '.$report->start_time);
                $end = strtotime($report->report_date->format('Y-m-d').' '.$report->end_time);
                $report->duration_minutes = max(0, intdiv($end - $start, 60));
            }
        });
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ReportFile::class);
    }

    public function workLog(): HasOne
    {
        return $this->hasOne(WorkLog::class);
    }
}
