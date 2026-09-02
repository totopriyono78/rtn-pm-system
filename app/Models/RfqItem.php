<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['request_for_quotation_id', 'item_id', 'qty', 'awarded_vendor_quotation_item_id'])]
class RfqItem extends Model
{
    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
        ];
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(RequestForQuotation::class, 'request_for_quotation_id');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Semua harga yang ditawarkan berbagai vendor untuk baris item ini —
     * dipakai untuk tabel perbandingan harga per vendor.
     */
    public function vendorQuotationItems(): HasMany
    {
        return $this->hasMany(VendorQuotationItem::class);
    }

    public function awardedVendorQuotationItem(): BelongsTo
    {
        return $this->belongsTo(VendorQuotationItem::class, 'awarded_vendor_quotation_item_id');
    }
}
