<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Akun login Client Portal (SRS 4.3) -- dipakai lewat guard 'client' yang
 * terpisah dari guard 'web' (karyawan internal). Satu akun selalu terikat
 * ke satu Customer; semua data yang ditampilkan di portal (proyek, invoice,
 * dokumen) di-scope lewat relasi Project -> Contract -> Customer ini.
 */
#[Fillable(['customer_id', 'name', 'email', 'password', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class ClientUser extends Authenticatable
{
    use Notifiable;

    protected $guard_name = 'client';

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
