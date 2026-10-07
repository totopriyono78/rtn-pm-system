<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Presensi teknisi: check-in TERPISAH sebelum Submit Laporan. Hanya valid
 * kalau koordinat device saat check-in berada dalam radius Site milik
 * Activity terkait. Di luar radius ditolak keras di server (lihat
 * Teknisi\CheckIn::checkIn()) kecuali PM/Admin melakukan override manual.
 */
#[Fillable([
    'assignment_id', 'user_id', 'site_id', 'checked_in_at', 'latitude', 'longitude',
    'accuracy_meters', 'distance_meters', 'is_within_radius', 'status', 'override_by', 'override_reason',
])]
class Attendance extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'accuracy_meters' => 'decimal:2',
            'distance_meters' => 'decimal:2',
            'is_within_radius' => 'boolean',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function overrideBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'override_by');
    }
}
