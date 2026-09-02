<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['project_id', 'name', 'status', 'planned_hours', 'order_no'])]
class Activity extends Model
{
    use HasFactory;

    public const STATUSES = [
        'belum_dimulai' => 'Belum Dimulai',
        'sedang_dikerjakan' => 'Sedang Dikerjakan',
        'selesai' => 'Selesai',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function workLogs(): HasMany
    {
        return $this->hasMany(WorkLog::class);
    }

    public function getActualHoursAttribute(): float
    {
        return round($this->workLogs()->sum('duration_minutes') / 60, 2);
    }
}
