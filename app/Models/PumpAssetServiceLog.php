<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['pump_asset_id', 'project_id', 'service_date', 'description', 'technician_user_id', 'created_by'])]
class PumpAssetServiceLog extends Model
{
    protected function casts(): array
    {
        return [
            'service_date' => 'date',
        ];
    }

    public function pumpAsset(): BelongsTo
    {
        return $this->belongsTo(PumpAsset::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
