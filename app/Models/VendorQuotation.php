<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['request_for_quotation_id', 'vendor_id', 'reference_number', 'quoted_at', 'notes'])]
class VendorQuotation extends Model
{
    protected function casts(): array
    {
        return [
            'quoted_at' => 'date',
        ];
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(RequestForQuotation::class, 'request_for_quotation_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(VendorQuotationItem::class);
    }

    public function getTotalAttribute(): float
    {
        return (float) $this->items->sum('subtotal');
    }
}
