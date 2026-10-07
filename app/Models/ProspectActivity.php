<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['prospect_id', 'user_id', 'type', 'from_status', 'to_status', 'notes', 'activity_date'])]
class ProspectActivity extends Model
{
    public const TYPES = [
        'stage_change' => 'Perpindahan Tahap',
        'call' => 'Telepon',
        'meeting' => 'Pertemuan',
        'email' => 'Email',
        'note' => 'Catatan',
    ];

    protected function casts(): array
    {
        return [
            'activity_date' => 'datetime',
        ];
    }

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
