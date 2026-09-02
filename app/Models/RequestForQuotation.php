<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * Request for Quotation (RFQ): Purchasing menyusun daftar material/jasa
 * yang dibutuhkan untuk sebuah proyek (items), mengundang beberapa vendor
 * untuk menawar (vendorQuotations), lalu memilih pemenang per baris item
 * (bisa berbeda vendor untuk tiap item). Setelah semua item punya pemenang,
 * RFQ diajukan ke Direktur untuk approval; saat disetujui, sistem otomatis
 * menerbitkan satu Purchase Order per vendor terpilih dan membuat entri
 * Material Tracking untuk tiap baris PO.
 */
#[Fillable(['project_id', 'code', 'status', 'created_by', 'notes', 'submitted_at', 'approved_by', 'approved_at'])]
class RequestForQuotation extends Model
{
    use HasFactory;

    public const STATUSES = [
        'draft' => 'Draft',
        'submitted' => 'Menunggu Approval',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RfqItem::class, 'request_for_quotation_id');
    }

    public function vendorQuotations(): HasMany
    {
        return $this->hasMany(VendorQuotation::class, 'request_for_quotation_id');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'request_for_quotation_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public static function generateCode(): string
    {
        $year = now()->format('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;

        return sprintf('RFQ-%s-%04d', $year, $count);
    }

    /**
     * Apakah semua baris item sudah punya vendor pemenang — syarat sebelum
     * RFQ bisa diajukan untuk approval.
     */
    public function isFullyAwarded(): bool
    {
        return $this->items->isNotEmpty() && $this->items->every(fn (RfqItem $line) => $line->awarded_vendor_quotation_item_id !== null);
    }

    public function submitForApproval(User $user): void
    {
        abort_unless($this->status === 'draft', 400, 'Hanya RFQ berstatus draft yang dapat diajukan.');
        abort_unless($this->isFullyAwarded(), 400, 'Pilih vendor pemenang untuk setiap item terlebih dahulu.');

        $this->status = 'submitted';
        $this->submitted_at = now();
        $this->save();
    }

    /**
     * Setujui RFQ: kelompokkan baris item pemenang per vendor, terbitkan
     * satu Purchase Order per vendor beserta baris itemnya, lalu buat entri
     * Material Tracking otomatis untuk tiap baris PO (sesuai alur SRS 4.5
     * yang diperluas: RFQ -> pemenang per item -> approval -> PO -> tracking).
     */
    public function approve(User $approver): void
    {
        abort_unless($this->status === 'submitted', 400, 'Hanya RFQ berstatus menunggu approval yang dapat disetujui.');

        DB::transaction(function () use ($approver) {
            $this->status = 'approved';
            $this->approved_by = $approver->id;
            $this->approved_at = now();
            $this->save();

            $lines = $this->items()->with('awardedVendorQuotationItem.vendorQuotation')->get();

            $groupedByVendor = $lines->groupBy(fn (RfqItem $line) => $line->awardedVendorQuotationItem->vendorQuotation->vendor_id);

            foreach ($groupedByVendor as $vendorId => $vendorLines) {
                $po = PurchaseOrder::create([
                    'code' => PurchaseOrder::generateCode(),
                    'request_for_quotation_id' => $this->id,
                    'vendor_id' => $vendorId,
                    'project_id' => $this->project_id,
                    'status' => 'issued',
                    'total' => 0,
                    'created_by' => $approver->id,
                    'approved_by' => $approver->id,
                    'approved_at' => now(),
                ]);

                foreach ($vendorLines as $line) {
                    $won = $line->awardedVendorQuotationItem;

                    $poItem = PurchaseOrderItem::create([
                        'purchase_order_id' => $po->id,
                        'item_id' => $line->item_id,
                        'qty' => $won->qty,
                        'unit_price' => $won->unit_price,
                        'subtotal' => $won->subtotal,
                    ]);

                    $tracking = MaterialTracking::create([
                        'purchase_order_item_id' => $poItem->id,
                        'project_id' => $this->project_id,
                        'item_id' => $line->item_id,
                        'qty' => $won->qty,
                        'status' => 'ordered',
                        'updated_by' => $approver->id,
                    ]);

                    MaterialTrackingLog::create([
                        'material_tracking_id' => $tracking->id,
                        'from_status' => null,
                        'to_status' => 'ordered',
                        'changed_by' => $approver->id,
                        'note' => 'Dibuat otomatis dari '.$po->code.' ('.$this->code.' yang disetujui).',
                        'created_at' => now(),
                    ]);
                }

                $po->recalcTotal();
            }
        });
    }

    public function reject(User $approver, ?string $note = null): void
    {
        abort_unless($this->status === 'submitted', 400, 'Hanya RFQ berstatus menunggu approval yang dapat ditolak.');

        $this->status = 'rejected';
        $this->approved_by = $approver->id;
        $this->approved_at = now();
        if ($note) {
            $this->notes = trim(($this->notes ? $this->notes."\n" : '')."Ditolak: {$note}");
        }
        $this->save();
    }
}
