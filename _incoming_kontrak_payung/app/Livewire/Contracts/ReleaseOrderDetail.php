<?php

namespace App\Livewire\Contracts;

use App\Models\Item;
use App\Models\ReleaseOrder;
use App\Models\ReleaseOrderItem;
use App\Models\RequestForQuotation;
use App\Models\RfqItem;
use App\Models\Site;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ReleaseOrderDetail extends Component
{
    public ReleaseOrder $releaseOrder;

    // --- item RO ---
    public bool $showItemModal = false;

    public string $itemDescription = '';

    public string $itemQty = '1';

    public string $itemUnit = '';

    public string $itemSiteId = '';

    public string $itemSpecNotes = '';

    // --- minta harga ke vendor (RFQ costing) ---
    public bool $showRfqModal = false;

    /** @var array<int, array{item_id: string, qty: string}> */
    public array $rfqLines = [];

    public function mount(ReleaseOrder $releaseOrder): void
    {
        abort_unless(auth()->user()->hasAnyPermission(['manage-contracts', 'manage-purchasing', 'view-contract-value']), 403);
        $this->releaseOrder = $releaseOrder;
    }

    public function render()
    {
        $this->releaseOrder->load([
            'contract.customer',
            'items.site',
            'quotations.approver',
            'rfqs.items.item',
            'rfqs.items.awardedVendorQuotationItem.vendorQuotation.vendor',
        ]);

        return view('livewire.contracts.release-order-detail', [
            'canManageContract' => auth()->user()->hasPermissionTo('manage-contracts'),
            'canManagePurchasing' => auth()->user()->hasPermissionTo('manage-purchasing'),
            'sites' => Site::where('is_active', true)->orderBy('name')->get(),
            'items' => Item::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    // ===== Item RO =====

    public function openAddItem(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-contracts'), 403);
        $this->reset(['itemDescription', 'itemUnit', 'itemSiteId', 'itemSpecNotes']);
        $this->itemQty = '1';
        $this->showItemModal = true;
    }

    public function saveItem(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-contracts'), 403);

        $this->validate([
            'itemDescription' => ['required', 'string', 'max:255'],
            'itemQty' => ['required', 'numeric', 'min:0.01'],
            'itemUnit' => ['nullable', 'string', 'max:50'],
            'itemSiteId' => ['nullable', Rule::exists('sites', 'id')],
            'itemSpecNotes' => ['nullable', 'string'],
        ]);

        ReleaseOrderItem::create([
            'release_order_id' => $this->releaseOrder->id,
            'site_id' => $this->itemSiteId ?: null,
            'description' => $this->itemDescription,
            'qty' => $this->itemQty,
            'unit' => $this->itemUnit ?: null,
            'spec_notes' => $this->itemSpecNotes ?: null,
        ]);

        $this->showItemModal = false;
        session()->flash('success', 'Item Release Order ditambahkan.');
    }

    public function removeItem(int $id): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-contracts'), 403);
        ReleaseOrderItem::where('release_order_id', $this->releaseOrder->id)->findOrFail($id)->delete();
        session()->flash('success', 'Item dihapus.');
    }

    // ===== Minta harga ke vendor (RFQ costing, TANPA Project) =====

    public function openRfqModal(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-purchasing'), 403);
        $this->rfqLines = [['item_id' => '', 'qty' => '1']];
        $this->showRfqModal = true;
    }

    public function addRfqLine(): void
    {
        $this->rfqLines[] = ['item_id' => '', 'qty' => '1'];
    }

    public function removeRfqLine(int $index): void
    {
        unset($this->rfqLines[$index]);
        $this->rfqLines = array_values($this->rfqLines);
    }

    public function saveRfq(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-purchasing'), 403);

        $this->validate([
            'rfqLines' => ['required', 'array', 'min:1'],
            'rfqLines.*.item_id' => ['required', Rule::exists('items', 'id')],
            'rfqLines.*.qty' => ['required', 'numeric', 'min:0.01'],
        ]);

        DB::transaction(function () {
            $rfq = RequestForQuotation::create([
                'release_order_id' => $this->releaseOrder->id,
                'code' => RequestForQuotation::generateCode(),
                'status' => 'draft',
                'created_by' => Auth::id(),
                'notes' => 'Costing untuk Penawaran RO '.$this->releaseOrder->ro_number,
            ]);

            foreach ($this->rfqLines as $line) {
                RfqItem::create([
                    'request_for_quotation_id' => $rfq->id,
                    'item_id' => $line['item_id'],
                    'qty' => $line['qty'],
                ]);
            }

            $this->releaseOrder->update(['status' => 'dalam_penawaran']);
        });

        $this->showRfqModal = false;
        session()->flash('success', 'RFQ costing dibuat. Lanjutkan undang vendor & pilih pemenang di halaman detail RFQ — harga hasilnya jadi dasar Penawaran ke customer.');
    }

    public function createQuotation()
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-contracts'), 403);

        $revisionNo = \App\Models\CustomerQuotation::nextRevisionNo($this->releaseOrder);

        $quotation = \App\Models\CustomerQuotation::create([
            'release_order_id' => $this->releaseOrder->id,
            'code' => \App\Models\CustomerQuotation::generateCode($this->releaseOrder, $revisionNo),
            'revision_no' => $revisionNo,
            'status' => 'draft',
            'created_by' => Auth::id(),
        ]);

        return $this->redirect(route('contracts.quotations.show', $quotation), navigate: false);
    }
}
