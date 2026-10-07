<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'type', 'document_number', 'purchase_order_id', 'project_id', 'vendor_name',
    'driver_name', 'vehicle_plate', 'gate_date', 'notes', 'disk_path', 'original_name', 'created_by',
])]
class DeliveryGatepass extends Model
{
    public const TYPES = [
        'surat_jalan' => 'Surat Jalan',
        'gatepass_in' => 'Gatepass Masuk',
        'gatepass_out' => 'Gatepass Keluar',
    ];

    protected function casts(): array
    {
        return [
            'gate_date' => 'date',
        ];
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
