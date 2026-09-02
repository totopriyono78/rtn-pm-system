<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable(['request_for_quotation_id', 'vendor_id', 'project_id', 'code', 'status', 'total', 'created_by', 'approved_by', 'approved_at'])]
class PurchaseOrder extends Model
{
    use HasFactory;

    public const STATUSES = [
        'issued' => 'Diterbitkan',
        'cancelled' => 'Dibatalkan',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'approved_at' => 'datetime',
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

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function materialTrackings(): HasManyThrough
    {
        return $this->hasManyThrough(
            MaterialTracking::class,
            PurchaseOrderItem::class,
            'purchase_order_id',
            'purchase_order_item_id'
        );
    }

    public static function generateCode(): string
    {
        $year = now()->format('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;

        return sprintf('PO-%s-%04d', $year, $count);
    }

    public function recalcTotal(): void
    {
        $this->total = $this->items()->sum('subtotal');
        $this->save();
    }
}
