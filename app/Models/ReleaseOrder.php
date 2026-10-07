<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['contract_id', 'ro_number', 'ro_date', 'notes', 'document_path', 'status', 'created_by'])]
class ReleaseOrder extends Model
{
    use HasFactory;

    public const STATUSES = [
        'baru' => 'Baru',
        'dalam_penawaran' => 'Dalam Penawaran',
        'selesai' => 'Selesai (PO Diterima)',
        'batal' => 'Batal',
    ];

    protected function casts(): array
    {
        return [
            'ro_date' => 'date',
        ];
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReleaseOrderItem::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(CustomerQuotation::class);
    }

    public function rfqs(): HasMany
    {
        return $this->hasMany(RequestForQuotation::class, 'release_order_id');
    }

    /**
     * Revisi penawaran terbaru untuk RO ini (kalau ada) — dipakai sebagai
     * default tampilan di halaman detail RO.
     */
    public function latestQuotation(): ?CustomerQuotation
    {
        return $this->quotations()->orderByDesc('revision_no')->first();
    }
}
