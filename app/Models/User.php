<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $guard_name = 'web';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Region-region yang boleh diakses user ini (untuk pembatasan lintas region).
     * Kosong + tidak punya permission view-all-project berarti tidak melihat proyek apapun,
     * kecuali role Administrator/Direktur yang memang default full/read-all.
     */
    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class, 'user_region');
    }

    public function projectsAsPic(): HasMany
    {
        return $this->hasMany(Project::class, 'pic_user_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function workLogs(): HasMany
    {
        return $this->hasMany(WorkLog::class);
    }

    /**
     * Apakah user boleh melihat proyek lintas region (tidak dibatasi region assignment).
     */
    public function canViewAllProjects(): bool
    {
        return $this->hasPermissionTo('view-all-project');
    }

    public function roleLabel(): string
    {
        return $this->getRoleNames()->first() ?? '-';
    }
}
