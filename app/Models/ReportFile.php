<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['report_id', 'category', 'disk_path', 'original_name', 'mime_type', 'size_bytes'])]
class ReportFile extends Model
{
    public const CATEGORIES = [
        'daily_report' => 'Daily Report',
        'final_report' => 'Final Report',
        'foto' => 'Foto',
        'drawing' => 'Drawing',
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }
}
