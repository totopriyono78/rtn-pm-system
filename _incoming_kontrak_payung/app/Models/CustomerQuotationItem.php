<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['customer_quotation_id', 'release_order_item_id', 'description', 'qty', 'unit', 'vendor_reference_note', 'unit_price', 'subtotal'])]
class CustomerQuotationItem extends Model
{
    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(CustomerQuotation::class, 'customer_quotation_id');
    }

    public function releaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(ReleaseOrderItem::class);
    }
}
