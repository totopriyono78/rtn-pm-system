<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'contact_person', 'phone', 'email', 'address', 'npwp', 'notes', 'is_active'])]
class Vendor extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function vendorQuotations(): HasMany
    {
        return $this->hasMany(VendorQuotation::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public static function generateCode(): string
    {
        $count = static::count() + 1;

        return sprintf('VDR-%04d', $count);
    }

    /**
     * Total nilai seluruh PO (tidak termasuk yang dibatalkan) untuk vendor
     * ini — dipakai di halaman riwayat vendor.
     */
    public function getTotalPoValueAttribute(): float
    {
        return (float) $this->purchaseOrders()->where('status', '!=', 'cancelled')->sum('total');
    }
}
