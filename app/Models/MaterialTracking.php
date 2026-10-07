<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['purchase_order_item_id', 'project_id', 'item_id', 'qty', 'status', 'updated_by', 'expected_arrival_date'])]
class MaterialTracking extends Model
{
    public const STATUSES = [
        'ordered' => 'Ordered',
        'shipping' => 'Shipping',
        'arrived' => 'Arrived',
        'installed' => 'Installed',
    ];

    /**
     * Status yang dianggap "sudah diterima" -- dipakai di accessor reminder
     * H-7 di bawah supaya definisinya konsisten dengan widget Dashboard
     * (NOT_RECEIVED_STATUSES) tanpa duplikasi daftar status.
     */
    public const RECEIVED_STATUSES = ['arrived', 'installed'];

    protected function casts(): array
    {
        return [
            'qty' => 'decimal:2',
            'expected_arrival_date' => 'date',
        ];
    }

    /**
     * Reminder H-7 (SRS 4.12) -- true kalau sudah lewat tanggal estimasi
     * tiba TAPI belum berstatus diterima. Selalu false kalau Purchasing
     * belum mengisi expected_arrival_date (opt-in, bukan wajib).
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->expected_arrival_date !== null
            && $this->expected_arrival_date->isPast()
            && ! in_array($this->status, self::RECEIVED_STATUSES, true);
    }

    public function getIsDueSoonAttribute(): bool
    {
        return $this->expected_arrival_date !== null
            && ! $this->is_overdue
            && ! in_array($this->status, self::RECEIVED_STATUSES, true)
            && $this->expected_arrival_date->lte(now()->addDays(7));
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
