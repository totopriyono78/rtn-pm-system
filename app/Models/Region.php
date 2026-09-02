<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'name'])]
class Region extends Model
{
    use HasFactory;

    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_region');
    }
}
