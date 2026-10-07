<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['unit_id', 'code', 'name', 'address', 'latitude', 'longitude', 'radius_meters', 'radius_check_enabled', 'is_active'])]
class Site extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'radius_meters' => 'integer',
            'radius_check_enabled' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function contracts(): BelongsToMany
    {
        return $this->belongsToMany(Contract::class, 'contract_site');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Jarak dari titik (lat, lng) ke site ini dalam meter, pakai formula
     * Haversine. Dipakai untuk validasi radius presensi.
     */
    public function distanceInMetersFrom(float $lat, float $lng): float
    {
        $earthRadius = 6371000; // meter

        $latFrom = deg2rad((float) $this->latitude);
        $lngFrom = deg2rad((float) $this->longitude);
        $latTo = deg2rad($lat);
        $lngTo = deg2rad($lng);

        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $a = sin($latDelta / 2) ** 2 + cos($latFrom) * cos($latTo) * sin($lngDelta / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }

    public function isWithinRadius(float $lat, float $lng): bool
    {
        // Keputusan client 2026-10-07: validasi radius bisa dimatikan total
        // per site ("bisa diset sangat luas ... atau bisa dimatikan pakai
        // radius atau tidak") -- kalau dimatikan, presensi selalu dianggap
        // dalam radius, dari lokasi mana pun.
        if (! $this->radius_check_enabled) {
            return true;
        }

        return $this->distanceInMetersFrom($lat, $lng) <= $this->radius_meters;
    }
}
