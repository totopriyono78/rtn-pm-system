<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'category', 'applicable_pump_type', 'applicable_engine_type', 'unit_of_measure', 'unit_price', 'is_active'])]
class Item extends Model
{
    use HasFactory;

    public const CATEGORIES = [
        'sparepart' => 'Sparepart',
        'material' => 'Material',
        'jasa' => 'Jasa',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'unit_price' => 'decimal:2',
        ];
    }

    public function rfqItems(): HasMany
    {
        return $this->hasMany(RfqItem::class);
    }

    /**
     * Filter item yang cocok untuk tipe pompa/engine tertentu -- item yang
     * tidak diisi applicable_pump_type dianggap generik (cocok untuk semua).
     */
    public function scopeCompatibleWith($query, ?string $pumpType = null, ?string $engineType = null)
    {
        return $query->where(function ($q) use ($pumpType) {
            $q->whereNull('applicable_pump_type')->orWhere('applicable_pump_type', $pumpType);
        })->where(function ($q) use ($engineType) {
            $q->whereNull('applicable_engine_type')->orWhere('applicable_engine_type', $engineType);
        });
    }
}
