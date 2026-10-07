<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Sales Order & Dokumen Teknis -- SRS 4.6. Dibuat Sales Support untuk
 * Customer yang sudah deal (dari Prospect yang won, atau langsung untuk
 * Customer lama yang order ulang pompa baru). Sekali dikonfirmasi
 * (draft -> confirmed), OTOMATIS membentuk 1 Contract (tipe spesifik) lalu
 * 1 Project -- menyambung ke infrastruktur Contract/Project Divisi 2 yang
 * sudah ada (lihat confirm()), sama polanya dengan
 * CustomerPurchaseOrder::convertToProject(). Tidak ada hard-delete setelah
 * confirmed: hanya draft yang bisa diubah/dibatalkan (mengikuti pola
 * no-destructive-delete yang dipakai di seluruh modul finance/kontrak).
 */
#[Fillable([
    'so_number', 'prospect_id', 'customer_id', 'unit_id', 'so_date', 'description',
    'total_value', 'status', 'notes', 'created_by', 'confirmed_at', 'contract_id',
])]
class SalesOrder extends Model
{
    use HasFactory;

    public const STATUSES = [
        'draft' => 'Draft',
        'confirmed' => 'Terkonfirmasi (Project Terbentuk)',
        'cancelled' => 'Dibatalkan',
    ];

    protected function casts(): array
    {
        return [
            'so_date' => 'date',
            'total_value' => 'decimal:2',
            'confirmed_at' => 'datetime',
        ];
    }

    public function prospect(): BelongsTo
    {
        return $this->belongsTo(Prospect::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SalesOrderItem::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(SalesOrderDocument::class);
    }

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public static function generateNumberSuggestion(): string
    {
        $year = now()->format('Y');
        $count = static::whereYear('so_date', $year)->count() + 1;

        return sprintf('SO-%s-%04d', $year, $count);
    }

    public function recalcTotal(): void
    {
        $this->total_value = $this->items()->sum('subtotal');
        $this->save();
    }

    /**
     * Unit internal "Divisi 1: Sales & Pengadaan Pompa Baru" -- dibuat
     * otomatis kalau belum ada (lazy, firstOrCreate), supaya Project yang
     * lahir dari Sales Order tetap punya unit_id (wajib diisi di tabel
     * projects) tanpa memaksa Sales Support memilih Region/Unit organisasi
     * internal yang sebenarnya tidak relevan bagi mereka.
     */
    public static function resolveDefaultUnit(): Unit
    {
        $region = Region::query()->first() ?? Region::create([
            'code' => 'PST',
            'name' => 'Region Pusat',
        ]);

        return Unit::query()->firstOrCreate(
            ['code' => 'SLS'],
            ['region_id' => $region->id, 'name' => 'Sales & Pengadaan Pompa Baru']
        );
    }

    /**
     * Konfirmasi Sales Order ini: otomatis membentuk 1 Contract (tipe
     * spesifik, nilai = total_value) lalu 1 Project dari Contract tersebut
     * -- meniru persis alur manual di ContractDetail::saveProject(), supaya
     * Project yang terbentuk identik perlakuannya dengan Project dari jalur
     * Contract biasa (ikut discan di Dashboard, laporan, dsb).
     */
    public function confirm(): Project
    {
        abort_unless($this->status === 'draft', 400, 'Hanya Sales Order berstatus draft yang bisa dikonfirmasi.');
        abort_if($this->items()->count() === 0, 400, 'Tambahkan minimal satu item sebelum mengonfirmasi Sales Order.');

        return DB::transaction(function () {
            $unit = $this->unit ?? static::resolveDefaultUnit();

            $contract = Contract::create([
                'customer_id' => $this->customer_id,
                'unit_id' => $unit->id,
                'contract_number' => 'KTR-'.$this->so_number,
                'contract_type' => 'spesifik',
                'contract_date' => $this->so_date,
                'start_date' => $this->so_date,
                'end_date' => null,
                'scope_description' => $this->description,
                'fixed_value' => $this->total_value,
                'max_value' => null,
                'status' => 'active',
                'document_path' => null,
                'created_by' => $this->created_by,
            ]);

            $project = Project::create([
                'unit_id' => $unit->id,
                'contract_id' => $contract->id,
                'pic_user_id' => null,
                'name' => $this->customer->name.' - '.$this->so_number,
                'description' => $this->description,
                'budget' => null,
                'project_value' => $this->total_value,
                'start_date' => $this->so_date,
                'end_date' => null,
                'status' => 'planning',
                'type' => 'pengadaan_baru',
            ]);

            $this->unit_id = $unit->id;
            $this->contract_id = $contract->id;
            $this->status = 'confirmed';
            $this->confirmed_at = now();
            $this->save();

            return $project;
        });
    }

    public function cancel(): void
    {
        abort_unless($this->status === 'draft', 400, 'Hanya Sales Order berstatus draft yang bisa dibatalkan.');

        $this->status = 'cancelled';
        $this->save();
    }
}
