<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

/**
 * CustomerPurchaseOrder: PO dari CUSTOMER ke PT RTN (bukan PurchaseOrder yang
 * sudah ada, itu tetap berarti PO PT RTN ke Vendor). Dicatat manual oleh
 * Project Controller setelah dokumen PO fisik/email diterima dari customer.
 * Mencatatnya sebagai "converted" otomatis membuat Project baru di bawah
 * kontrak payung terkait — lihat convertToProject().
 */
#[Fillable(['customer_quotation_id', 'po_number', 'po_date', 'value', 'start_date', 'end_date', 'document_path', 'status', 'created_by'])]
class CustomerPurchaseOrder extends Model
{
    use HasFactory;

    public const STATUSES = [
        'received' => 'Diterima',
        'converted' => 'Sudah Jadi Proyek',
        'cancelled' => 'Dibatalkan',
    ];

    protected function casts(): array
    {
        return [
            'po_date' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
            'value' => 'decimal:2',
        ];
    }

    public function customerQuotation(): BelongsTo
    {
        return $this->belongsTo(CustomerQuotation::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function project(): HasOne
    {
        return $this->hasOne(Project::class);
    }

    /**
     * Buat Project baru dari PO customer ini (jalur kontrak payung).
     * Idempotent-safe: menolak kalau sudah pernah dikonversi sebelumnya.
     */
    public function convertToProject(User $user, ?string $projectName = null): Project
    {
        abort_unless($this->status === 'received', 400, 'PO Customer ini sudah dikonversi menjadi proyek atau dibatalkan.');

        $releaseOrder = $this->customerQuotation->releaseOrder;
        $contract = $releaseOrder->contract;

        return DB::transaction(function () use ($user, $projectName, $releaseOrder, $contract) {
            $project = Project::create([
                'unit_id' => $contract->unit_id,
                'contract_id' => null,
                'customer_purchase_order_id' => $this->id,
                'pic_user_id' => null,
                'name' => $projectName ?: ($contract->customer->name.' - RO '.$releaseOrder->ro_number),
                'description' => $releaseOrder->notes,
                'budget' => null,
                'project_value' => $this->value,
                'start_date' => $this->start_date,
                'end_date' => $this->end_date,
                'status' => 'planning',
            ]);

            $this->status = 'converted';
            $this->save();

            $releaseOrder->update(['status' => 'selesai']);

            return $project;
        });
    }
}
