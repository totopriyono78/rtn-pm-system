<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name', 'category', 'unit_of_measure', 'unit_price', 'is_active'])]
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
}
