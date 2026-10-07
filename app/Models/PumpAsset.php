<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Master Unit Pompa (Aset Klien) -- SRS v2.0 modul 4.4.1.
 *
 * Catatan penamaan: entity ini SENGAJA dinamai "PumpAsset" (bukan "Unit")
 * supaya tidak bertabrakan dengan model Unit yang sudah ada (unit organisasi
 * internal PT RTN / cabang, dipakai di hierarki Region->Unit->Project). Unit
 * Pompa di sini adalah ASET FISIK pompa yang terpasang di lokasi klien --
 * konsepnya beda sama sekali dari Unit organisasi.
 */
#[Fillable([
    'customer_id', 'site_id', 'serial_number', 'pump_type', 'pump_model',
    'pump_capacity', 'engine_type', 'engine_model', 'install_date',
    'last_service_date', 'condition', 'notes', 'is_active',
])]
class PumpAsset extends Model
{
    use HasFactory;

    public const CONDITIONS = [
        'baik' => 'Baik',
        'perlu_perhatian' => 'Perlu Perhatian',
        'rusak' => 'Rusak / Tidak Beroperasi',
    ];

    protected function casts(): array
    {
        return [
            'install_date' => 'date',
            'last_service_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function serviceLogs(): HasMany
    {
        return $this->hasMany(PumpAssetServiceLog::class)->orderByDesc('service_date');
    }

    public function label(): string
    {
        return trim($this->pump_type.' '.$this->pump_model).($this->serial_number ? " (SN: {$this->serial_number})" : '');
    }
}
