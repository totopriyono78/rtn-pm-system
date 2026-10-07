<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['unit_id', 'contract_id', 'customer_purchase_order_id', 'pic_user_id', 'name', 'description', 'budget', 'project_value', 'start_date', 'end_date', 'status', 'type'])]
class Project extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'budget' => 'decimal:2',
            'project_value' => 'decimal:2',
        ];
    }

    public const STATUSES = [
        'planning' => 'Perencanaan',
        'ongoing' => 'Berjalan',
        'completed' => 'Selesai',
        'on_hold' => 'Ditunda',
    ];

    /**
     * Tipe proyek -- SRS 4.7. Nullable/opsional, murni metadata deskriptif
     * untuk sekarang (belum dipakai di logic lain).
     */
    public const TYPES = [
        'assessment' => 'Assessment',
        'preventive' => 'Preventive Maintenance',
        'corrective' => 'Corrective Maintenance',
        'overhaul' => 'Overhaul',
        'commissioning' => 'Commissioning',
        // Lahir otomatis dari Sales Order yang dikonfirmasi -- SRS 4.6.
        'pengadaan_baru' => 'Pengadaan Pompa Baru',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Diisi langsung kalau proyek ini lahir dari Contract tipe "spesifik"
     * (selalu 1:1). Untuk jalur "payung", pakai getContractAttribute() yang
     * menormalkan kedua jalur jadi satu.
     */
    public function directContract(): BelongsTo
    {
        return $this->belongsTo(Contract::class, 'contract_id');
    }

    public function customerPurchaseOrder(): BelongsTo
    {
        return $this->belongsTo(CustomerPurchaseOrder::class);
    }

    public function budgetLines(): HasMany
    {
        return $this->hasMany(ProjectBudgetLine::class);
    }

    /**
     * Normalisasi Contract asal proyek ini, apa pun jalurnya: langsung
     * (kontrak spesifik) atau tidak langsung lewat rantai
     * customerPurchaseOrder->customerQuotation->releaseOrder->contract
     * (kontrak payung).
     */
    public function getContractAttribute(): ?Contract
    {
        if ($this->contract_id) {
            return $this->directContract;
        }

        return $this->customerPurchaseOrder?->customerQuotation?->releaseOrder?->contract;
    }

    /**
     * Klien (Customer) pemilik proyek ini, ditarik lewat Contract -- lapisan
     * komersial yang berjalan PARALEL dengan hierarki Unit (organisasi
     * internal). Unit tetap dipakai untuk scoping visibility staff per
     * cabang; Customer/Site di sini adalah "siapa kliennya" (SRS 4.7).
     * Null kalau proyek belum terkait Contract sama sekali.
     */
    public function getCustomerAttribute(): ?Customer
    {
        return $this->contract?->customer;
    }

    /**
     * Site (lokasi fisik klien) yang terkait proyek ini -- diambil dari site
     * milik Contract-nya. Kalau Contract tidak diisi site spesifik, fallback
     * ke site yang dipakai di activity-activity proyek ini.
     */
    public function getSitesAttribute()
    {
        $contractSites = $this->contract?->sites;
        if ($contractSites && $contractSites->isNotEmpty()) {
            return $contractSites;
        }

        return $this->activities->pluck('site')->filter()->unique('id')->values();
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

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function cashAdvances(): HasMany
    {
        return $this->hasMany(CashAdvance::class);
    }

    /**
     * Total yang sudah ditagih (semua status kecuali cancelled) dan yang
     * sudah lunas -- dipakai di halaman Detail Proyek untuk rekap singkat
     * progres penagihan, mirip pola used_budget/remaining_budget.
     */
    public function getTotalInvoicedAttribute(): float
    {
        return (float) $this->invoices()->where('status', '!=', 'cancelled')->sum('total_amount');
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->invoices()->where('status', 'paid')->sum('total_amount');
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

    /**
     * Semua proyek milik Customer tertentu, lewat SALAH SATU dari 2 jalur
     * Contract yang mungkin (lihat getContractAttribute()): kontrak
     * spesifik langsung, atau kontrak payung lewat rantai
     * CustomerPurchaseOrder -> CustomerQuotation -> ReleaseOrder -> Contract.
     * Dipakai Client Portal (SRS 4.3) untuk men-scope apa yang boleh dilihat
     * akun client tertentu.
     */
    public function scopeForCustomer(Builder $query, int $customerId): Builder
    {
        return $query->where(function (Builder $q) use ($customerId) {
            $q->whereHas('directContract', fn (Builder $q2) => $q2->where('customer_id', $customerId))
                ->orWhereHas(
                    'customerPurchaseOrder.customerQuotation.releaseOrder.contract',
                    fn (Builder $q2) => $q2->where('customer_id', $customerId)
                );
        });
    }
}
