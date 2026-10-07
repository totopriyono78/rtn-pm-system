<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'division', 'icon', 'short_description', 'description', 'image_path', 'is_published', 'sort_order'])]
class WebsiteService extends Model
{
    use HasFactory;

    public const DIVISIONS = [
        'service_maintenance' => 'Service & Maintenance Pompa',
        'sales_pompa' => 'Sales & Pengadaan Pompa Baru',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
