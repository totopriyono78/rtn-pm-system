<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['vendor_quotation_id', 'rfq_item_id', 'qty', 'unit_price', 'subtotal'])]
class VendorQuotationItem extends Model
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
        static::saving(function (VendorQuotationItem $line) {
            $line->subtotal = round(((float) $line->qty) * ((float) $line->unit_price), 2);
        });
    }

    public function vendorQuotation(): BelongsTo
    {
        return $this->belongsTo(VendorQuotation::class);
    }

    public function rfqItem(): BelongsTo
    {
        return $this->belongsTo(RfqItem::class);
    }

    /**
     * Baris rfq_items yang menjadikan penawaran ini sebagai pemenang
     * (null bila belum/tidak dipilih).
     */
    public function awardedFor(): HasMany
    {
        return $this->hasMany(RfqItem::class, 'awarded_vendor_quotation_item_id');
    }
}
