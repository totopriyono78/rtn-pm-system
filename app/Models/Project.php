<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['unit_id', 'pic_user_id', 'name', 'description', 'budget', 'start_date', 'end_date', 'status'])]
class Project extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'budget' => 'decimal:2',
        ];
    }

    public const STATUSES = [
        'planning' => 'Perencanaan',
        'ongoing' => 'Berjalan',
        'completed' => 'Selesai',
        'on_hold' => 'Ditunda',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function pic(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_user_id');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class)->orderBy('order_no');
    }

    public function requestForQuotations(): HasMany
    {
        return $this->hasMany(RequestForQuotation::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function workLogs(): HasMany
    {
        return $this->hasMany(WorkLog::class);
    }

    public function materialTrackings(): HasMany
    {
        return $this->hasMany(MaterialTracking::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(ProjectDocument::class);
    }

    /**
     * Total nilai Purchase Order yang sudah diterbitkan (status issued) untuk
     * proyek ini — dipakai sebagai "actual penggunaan" budget.
     */
    public function getUsedBudgetAttribute(): float
    {
        return (float) $this->purchaseOrders->where('status', 'issued')->sum('total');
    }

    public function getRemainingBudgetAttribute(): ?float
    {
        if ($this->budget === null) {
            return null;
        }

        return (float) $this->budget - $this->used_budget;
    }

    /**
     * Persentase budget yang sudah terpakai. Null kalau proyek tidak diberi
     * budget (dianggap tidak dibatasi).
     */
    public function getBudgetUsagePercentAttribute(): ?float
    {
        if ($this->budget === null || (float) $this->budget <= 0) {
            return null;
        }

        return round(($this->used_budget / (float) $this->budget) * 100, 1);
    }

    /**
     * Persentase progress proyek berdasarkan jumlah activity yang berstatus 'selesai'.
     */
    public function getProgressPercentAttribute(): int
    {
        $total = $this->activities->count();
        if ($total === 0) {
            return 0;
        }
        $done = $this->activities->where('status', 'selesai')->count();

        return (int) round(($done / $total) * 100);
    }

    public function getPlannedHoursAttribute(): float
    {
        return (float) $this->activities->sum('planned_hours');
    }

    public function getActualHoursAttribute(): float
    {
        return round($this->workLogs->sum('duration_minutes') / 60, 2);
    }

    /**
     * Batasi query proyek sesuai region yang boleh diakses user (kecuali user
     * punya permission view-all-project, atau memang role Administrator/Direktur).
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->canViewAllProjects()) {
            return $query;
        }

        $regionIds = $user->regions()->pluck('regions.id');

        if ($regionIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereHas('unit', function (Builder $q) use ($regionIds) {
            $q->whereIn('region_id', $regionIds);
        });
    }
}
