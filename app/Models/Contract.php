<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'customer_id', 'unit_id', 'contract_number', 'contract_type', 'contract_date',
    'start_date', 'end_date', 'scope_description', 'fixed_value', 'max_value',
    'status', 'document_path', 'created_by',
])]
class Contract extends Model
{
    use HasFactory;

    public const TYPES = [
        'spesifik' => 'Kontrak Spesifik',
        'payung' => 'Kontrak Payung',
    ];

    public const STATUSES = [
        'draft' => 'Draft',
        'active' => 'Aktif',
        'expired' => 'Berakhir',
        'terminated' => 'Diberhentikan',
    ];

    protected function casts(): array
    {
        return [
            'contract_date' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
            'fixed_value' => 'decimal:2',
            'max_value' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class, 'contract_site');
    }

    public function releaseOrders(): HasMany
    {
        return $this->hasMany(ReleaseOrder::class);
    }

    /**
     * Project yang lahir LANGSUNG dari kontrak ini (jalur spesifik, selalu
     * 1:1). Untuk jalur payung, Project didapat tidak langsung lewat rantai
     * releaseOrders->quotations->customerPurchaseOrder->project — lihat
     * getProjectsAttribute().
     */
    public function directProject(): HasMany
    {
        return $this->hasMany(Project::class, 'contract_id');
    }

    public function isPayung(): bool
    {
        return $this->contract_type === 'payung';
    }

    /**
     * Total nilai seluruh CustomerPurchaseOrder yang sudah diterima (belum
     * dibatalkan) di bawah kontrak payung ini — dipakai untuk menghitung
     * sisa plafon, mirip pola used_budget/remaining_budget di Project.
     */
    public function getUsedValueAttribute(): float
    {
        if (! $this->isPayung()) {
            return (float) ($this->fixed_value ?? 0);
        }

        return (float) CustomerPurchaseOrder::query()
            ->whereHas('customerQuotation.releaseOrder', fn ($q) => $q->where('contract_id', $this->id))
            ->where('status', '!=', 'cancelled')
            ->sum('value');
    }

    public function getRemainingValueAttribute(): ?float
    {
        if ($this->max_value === null) {
            return null;
        }

        return (float) $this->max_value - $this->used_value;
    }

    public static function generateNumberSuggestion(): string
    {
        $year = now()->format('Y');
        $count = static::whereYear('contract_date', $year)->count() + 1;

        return sprintf('KTR-%s-%04d', $year, $count);
    }
}
