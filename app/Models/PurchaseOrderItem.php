<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['purchase_order_id', 'item_id', 'qty', 'unit_price', 'subtotal'])]
class PurchaseOrderItem extends Model
{
    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (PurchaseOrderItem $line) {
            $line->subtotal = round(((float) $line->qty) * ((float) $line->unit_price), 2);
        });
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function materialTracking(): HasOne
    {
        return $this->hasOne(MaterialTracking::class);
    }
}
