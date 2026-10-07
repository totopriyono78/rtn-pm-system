<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Log Safety Talk / Toolbox Meeting -- SRS 4.9. Diisi teknisi/Lead
 * Technician per activity (sebelum/selama bekerja), dilihat PM lewat tab
 * Laporan di Detail Proyek sebagai bukti kepatuhan K3.
 */
#[Fillable([
    'activity_id', 'conducted_by', 'meeting_date', 'topic', 'attendees', 'notes',
    'photo_disk_path', 'photo_original_name', 'photo_mime_type', 'photo_size_bytes',
])]
class SafetyTalk extends Model
{
    protected function casts(): array
    {
        return [
            'meeting_date' => 'date',
        ];
    }

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }

    public function conductor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'conducted_by');
    }
}
