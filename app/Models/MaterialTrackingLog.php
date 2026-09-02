<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialTrackingLog extends Model
{
    public $timestamps = false;

    protected $fillable = ['material_tracking_id', 'from_status', 'to_status', 'changed_by', 'note', 'created_at'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function materialTracking(): BelongsTo
    {
        return $this->belongsTo(MaterialTracking::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
