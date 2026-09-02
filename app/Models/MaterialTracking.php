<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['purchase_order_item_id', 'project_id', 'item_id', 'qty', 'status', 'updated_by'])]
class MaterialTracking extends Model
{
    public const STATUSES = [
        'ordered' => 'Ordered',
        'shipping' => 'Shipping',
        'arrived' => 'Arrived',
        'installed' => 'Installed',
    ];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
        ];
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrderItem::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(MaterialTrackingLog::class)->latest('created_at');
    }

    public function changeStatus(string $newStatus, User $user, ?string $note = null): void
    {
        $old = $this->status;
        $this->status = $newStatus;
        $this->updated_by = $user->id;
        $this->save();

        $this->logs()->create([
            'from_status' => $old,
            'to_status' => $newStatus,
            'changed_by' => $user->id,
            'note' => $note,
            'created_at' => now(),
        ]);
    }
}
