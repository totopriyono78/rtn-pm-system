<?php

namespace App\Livewire\Purchasing;

use App\Models\Item;
use App\Models\RequestForQuotation;
use App\Models\RfqItem;
use App\Models\Vendor;
use App\Models\VendorQuotation;
use App\Models\VendorQuotationItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class RfqDetail extends Component
{
    public RequestForQuotation $rfq;

    // --- tambah baris material ---
    public bool $showAddItemModal = false;

    public string $newItemId = '';

    public string $newItemQty = '1';

    // --- form penawaran vendor baru ---
    public bool $showVendorModal = false;

    public string $vendorId = '';

    public string $referenceNumber = '';

    public string $quotedAt = '';

    public string $vendorNotes = '';

    /** @var array<int, string> keyed by rfq_item_id */
    public array $vendorPrices = [];

    // --- reject ---
    public bool $showRejectModal = false;

    public string $rejectNote = '';

    public function mount(RequestForQuotation $rfq): void
    {
        $this->rfq = $rfq;
    }

    public function render()
    {
        $this->rfq->load([
            'project.purchaseOrders',
            'items.item',
            'items.awardedVendorQuotationItem.vendorQuotation.vendor',
            'items.vendorQuotationItems.vendorQuotation.vendor',
            'vendorQuotations.vendor',
            'vendorQuotations.items',
            'creator',
            'approver',
            'purchaseOrders.vendor',
            'purchaseOrders.items.item',
        ]);

        $user = auth()->user();

        return view('livewire.purchasing.rfq-detail', [
            'canManage' => $user->hasPermissionTo('manage-purchasing'),
            'canApprove' => $user->hasPermissionTo('approve-purchasing'),
            'canViewHarga' => $user->hasPermissionTo('view-harga'),
            'items' => Item::where('is_active', true)->orderBy('name')->get(),
            'vendors' => Vendor::where('is_active', true)->orderBy('name')->get(),
            'isFullyAwarded' => $this->rfq->isFullyAwarded(),
            'awardedCount' => $this->rfq->items->filter(fn (RfqItem $l) => $l->awarded_vendor_quotation_item_id !== null)->count(),
        ]);
    }

    // ===== Daftar material dibutuhkan =====

    public function openAddItemModal(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-purchasing'), 403);
        $this->reset(['newItemId']);
        $this->newItemQty = '1';
        $this->showAddItemModal = true;
    }

    public function addMaterialLine(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-purchasing'), 403);
        abort_unless($this->rfq->status === 'draft', 400, 'RFQ sudah tidak berstatus draft.');

        $this->validate([
            'newItemId' => ['required', Rule::exists('items', 'id')],
            'newItemQty' => ['required', 'numeric', 'min:0.01'],
        ]);

        RfqItem::create([
            'request_for_quotation_id' => $this->rfq->id,
            'item_id' => $this->newItemId,
            'qty' => $this->newItemQty,
        ]);

        $this->showAddItemModal = false;
        session()->flash('success', 'Item ditambahkan ke RFQ.');
    }

    public function removeMaterialLine(int $rfqItemId): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-purchasing'), 403);
        abort_unless($this->rfq->status === 'draft', 400, 'RFQ sudah tidak berstatus draft.');

        RfqItem::where('request_for_quotation_id', $this->rfq->id)->findOrFail($rfqItemId)->delete();
        session()->flash('success', 'Item dihapus dari RFQ.');
    }

    // ===== Penawaran vendor =====

    public function openVendorModal(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-purchasing'), 403);
        abort_unless($this->rfq->status === 'draft', 400, 'RFQ sudah tidak berstatus draft.');

        $this->reset(['vendorId', 'referenceNumber', 'quotedAt', 'vendorNotes']);
        $this->vendorPrices = collect($this->rfq->items)->mapWithKeys(fn (RfqItem $l) => [$l->id => ''])->all();
        $this->showVendorModal = true;
    }

    public function saveVendorQuotation(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-purchasing'), 403);
        abort_unless($this->rfq->status === 'draft', 400, 'RFQ sudah tidak berstatus draft.');

        $this->validate([
            'vendorId' => ['required', Rule::exists('vendors', 'id')],
            'quotedAt' => ['nullable', 'date'],
            'vendorPrices' => ['required', 'array'],
            'vendorPrices.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $filled = collect($this->vendorPrices)->filter(fn ($price) => $price !== '' && (float) $price > 0);

        if ($filled->isEmpty()) {
            $this->addError('vendorPrices', 'Isi harga untuk minimal satu item.');

            return;
        }

        DB::transaction(function () use ($filled) {
            $vq = VendorQuotation::create([
                'request_for_quotation_id' => $this->rfq->id,
                'vendor_id' => $this->vendorId,
                'reference_number' => $this->referenceNumber ?: null,
                'quoted_at' => $this->quotedAt ?: null,
                'notes' => $this->vendorNotes ?: null,
            ]);

            $rfqItemsById = $this->rfq->items->keyBy('id');

            foreach ($filled as $rfqItemId => $price) {
                $line = $rfqItemsById->get($rfqItemId);
                if (! $line) {
                    continue;
                }

                VendorQuotationItem::create([
                    'vendor_quotation_id' => $vq->id,
                    'rfq_item_id' => $rfqItemId,
                    'qty' => $line->qty,
                    'unit_price' => $price,
                    'subtotal' => $line->qty * $price,
                ]);
            }
        });

        $this->showVendorModal = false;
        session()->flash('success', 'Penawaran vendor tersimpan.');
    }

    /**
     * Perbarui harga satu baris penawaran vendor — dipakai untuk mencatat
     * hasil negosiasi tanpa perlu input ulang seluruh penawaran.
     */
    public function updateNegotiatedPrice(int $vendorQuotationItemId, $value): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-purchasing'), 403);
        abort_unless($this->rfq->status === 'draft', 400, 'RFQ sudah tidak berstatus draft.');

        $price = is_numeric($value) ? (float) $value : null;
        if ($price === null || $price < 0) {
            return;
        }

        $line = VendorQuotationItem::whereHas('vendorQuotation', fn ($q) => $q->where('request_for_quotation_id', $this->rfq->id))
            ->findOrFail($vendorQuotationItemId);

        $line->unit_price = $price;
        $line->save();

        session()->flash('success', 'Harga hasil negosiasi diperbarui.');
    }

    // ===== Pemenang per item =====

    public function awardItem(int $rfqItemId, int $vendorQuotationItemId): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-purchasing'), 403);
        abort_unless($this->rfq->status === 'draft', 400, 'RFQ sudah tidak berstatus draft.');

        $line = RfqItem::where('request_for_quotation_id', $this->rfq->id)->findOrFail($rfqItemId);

        $offer = VendorQuotationItem::where('rfq_item_id', $rfqItemId)->findOrFail($vendorQuotationItemId);

        $line->awarded_vendor_quotation_item_id = $offer->id;
        $line->save();
    }

    public function clearAward(int $rfqItemId): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-purchasing'), 403);
        abort_unless($this->rfq->status === 'draft', 400, 'RFQ sudah tidak berstatus draft.');

        RfqItem::where('request_for_quotation_id', $this->rfq->id)->findOrFail($rfqItemId)
            ->update(['awarded_vendor_quotation_item_id' => null]);
    }

    // ===== Approval =====

    public function submitForApproval(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-purchasing'), 403);
        $this->rfq->submitForApproval(Auth::user());
        session()->flash('success', 'RFQ diajukan untuk approval Direktur.');
    }

    public function approve(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('approve-purchasing'), 403);
        $this->rfq->approve(Auth::user());
        session()->flash('success', 'RFQ disetujui. Purchase Order otomatis diterbitkan ke vendor terpilih.');
    }

    public function reject(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('approve-purchasing'), 403);
        $this->rfq->reject(Auth::user(), $this->rejectNote);
        $this->showRejectModal = false;
        session()->flash('success', 'RFQ ditolak.');
    }
}
