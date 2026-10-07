<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['release_order_id', 'site_id', 'description', 'qty', 'unit', 'spec_notes'])]
class ReleaseOrderItem extends Model
{
    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
        ];
    }

    public function releaseOrder(): BelongsTo
    {
        return $this->belongsTo(ReleaseOrder::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
