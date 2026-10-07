<?php

namespace App\Livewire\Contracts;

use App\Models\CustomerPurchaseOrder;
use App\Models\CustomerQuotation;
use App\Models\CustomerQuotationItem;
use App\Models\DocumentSignature;
use App\Models\UserSignature;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class CustomerQuotationDetail extends Component
{
    public CustomerQuotation $quotation;

    // --- item penawaran ---
    public bool $showItemModal = false;

    public string $releaseOrderItemId = '';

    public string $itemDescription = '';

    public string $itemQty = '1';

    public string $itemUnit = '';

    public string $vendorReferenceNote = '';

    public string $unitPrice = '';

    // --- reject / keputusan customer ---
    public bool $showRejectModal = false;

    public string $rejectNote = '';

    public bool $showCustomerDecisionModal = false;

    public string $customerDecisionNote = '';

    // --- catat PO customer ---
    public bool $showPoModal = false;

    public string $poNumber = '';

    public string $poDate = '';

    public string $poValue = '';

    public string $poStartDate = '';

    public string $poEndDate = '';

    public function mount(CustomerQuotation $quotation): void
    {
        abort_unless(auth()->user()->hasAnyPermission(['manage-contracts', 'approve-quotation', 'view-contract-value']), 403);
        $this->quotation = $quotation;
    }

    public function render()
    {
        $this->quotation->load([
            'releaseOrder.contract.customer',
            'items.releaseOrderItem',
            'creator',
            'approver',
            'customerPurchaseOrder.project',
            'signatures.user',
        ]);

        $user = auth()->user();

        return view('livewire.contracts.customer-quotation-detail', [
            'canManage' => $user->hasPermissionTo('manage-contracts'),
            'canApprove' => $user->hasPermissionTo('approve-quotation'),
            'mySignature' => UserSignature::where('user_id', $user->id)->where('is_default', true)->first(),
        ]);
    }

    // ===== Item =====

    public function openAddItem(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-contracts'), 403);
        abort_unless($this->quotation->status === 'draft', 400, 'Hanya penawaran draft yang bisa diubah.');

        $this->reset(['releaseOrderItemId', 'itemDescription', 'itemUnit', 'vendorReferenceNote', 'unitPrice']);
        $this->itemQty = '1';
        $this->showItemModal = true;
    }

    public function fillFromRoItem(int $roItemId): void
    {
        $roItem = $this->quotation->releaseOrder->items()->findOrFail($roItemId);
        $this->releaseOrderItemId = (string) $roItem->id;
        $this->itemDescription = $roItem->description;
        $this->itemQty = (string) $roItem->qty;
        $this->itemUnit = (string) $roItem->unit;
    }

    public function saveItem(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-contracts'), 403);
        abort_unless($this->quotation->status === 'draft', 400, 'Hanya penawaran draft yang bisa diubah.');

        $this->validate([
            'releaseOrderItemId' => ['nullable', Rule::exists('release_order_items', 'id')],
            'itemDescription' => ['required', 'string', 'max:255'],
            'itemQty' => ['required', 'numeric', 'min:0.01'],
            'itemUnit' => ['nullable', 'string', 'max:50'],
            'vendorReferenceNote' => ['nullable', 'string', 'max:255'],
            'unitPrice' => ['required', 'numeric', 'min:0'],
        ]);

        CustomerQuotationItem::create([
            'customer_quotation_id' => $this->quotation->id,
            'release_order_item_id' => $this->releaseOrderItemId ?: null,
            'description' => $this->itemDescription,
            'qty' => $this->itemQty,
            'unit' => $this->itemUnit ?: null,
            'vendor_reference_note' => $this->vendorReferenceNote ?: null,
            'unit_price' => $this->unitPrice,
            'subtotal' => (float) $this->itemQty * (float) $this->unitPrice,
        ]);

        $this->quotation->recalcTotal();

        $this->showItemModal = false;
        session()->flash('success', 'Item penawaran ditambahkan.');
    }

    public function removeItem(int $id): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-contracts'), 403);
        abort_unless($this->quotation->status === 'draft', 400, 'Hanya penawaran draft yang bisa diubah.');

        CustomerQuotationItem::where('customer_quotation_id', $this->quotation->id)->findOrFail($id)->delete();
        $this->quotation->recalcTotal();
        session()->flash('success', 'Item dihapus.');
    }

    // ===== Alur approval =====

    public function submitForApproval(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-contracts'), 403);
        $this->quotation->submitForApproval(Auth::user());
        $this->signDocument('Disiapkan oleh (Project Controller)');
        session()->flash('success', 'Penawaran diajukan untuk approval Direktur.');
    }

    public function approve(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('approve-quotation'), 403);
        $this->quotation->approve(Auth::user());
        $this->signDocument('Disetujui oleh (Direktur)');
        session()->flash('success', 'Penawaran disetujui & dianggap terkirim ke customer.');
    }

    public function reject(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('approve-quotation'), 403);
        $this->quotation->reject(Auth::user(), $this->rejectNote);
        $this->showRejectModal = false;
        session()->flash('success', 'Penawaran ditolak.');
    }

    public function openCustomerDecisionModal(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-contracts'), 403);
        $this->reset(['customerDecisionNote']);
        $this->showCustomerDecisionModal = true;
    }

    public function recordCustomerDecision(bool $accepted): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-contracts'), 403);
        $this->quotation->recordCustomerDecision($accepted, $this->customerDecisionNote ?: null);
        $this->showCustomerDecisionModal = false;
        session()->flash('success', $accepted ? 'Keputusan customer (diterima) dicatat. Silakan catat nomor PO customer.' : 'Keputusan customer (ditolak) dicatat. Anda bisa membuat revisi penawaran baru dari halaman Release Order.');
    }

    /**
     * Rekam tanda tangan digital (kalau user yang bersangkutan sudah punya
     * signature default tersimpan) sebagai jejak audit ringan di dokumen ini.
     * Tidak memblok alur bisnis kalau belum ada signature tersimpan.
     */
    private function signDocument(string $roleLabel): void
    {
        $signature = UserSignature::where('user_id', Auth::id())->where('is_default', true)->first();

        DocumentSignature::create([
            'signable_type' => CustomerQuotation::class,
            'signable_id' => $this->quotation->id,
            'user_id' => Auth::id(),
            'user_signature_id' => $signature?->id,
            'role_label' => $roleLabel,
            'signed_at' => now(),
            'ip_address' => request()->ip(),
        ]);
    }

    // ===== PO Customer & konversi ke Project =====

    public function openPoModal(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-contracts'), 403);
        abort_unless($this->quotation->status === 'customer_accepted', 400, 'Catat keputusan customer (diterima) terlebih dahulu.');

        $this->reset(['poNumber', 'poStartDate', 'poEndDate']);
        $this->poDate = now()->format('Y-m-d');
        $this->poValue = (string) $this->quotation->total;
        $this->showPoModal = true;
    }

    public function savePo(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-contracts'), 403);

        $this->validate([
            'poNumber' => ['required', 'string', 'max:255'],
            'poDate' => ['required', 'date'],
            'poValue' => ['required', 'numeric', 'min:0'],
            'poStartDate' => ['nullable', 'date'],
            'poEndDate' => ['nullable', 'date', 'after_or_equal:poStartDate'],
        ]);

        $po = CustomerPurchaseOrder::create([
            'customer_quotation_id' => $this->quotation->id,
            'po_number' => $this->poNumber,
            'po_date' => $this->poDate,
            'value' => $this->poValue,
            'start_date' => $this->poStartDate ?: null,
            'end_date' => $this->poEndDate ?: null,
            'status' => 'received',
            'created_by' => Auth::id(),
        ]);

        $this->showPoModal = false;
        session()->flash('success', 'PO Customer tercatat.');
    }

    public function convertToProject()
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-contracts'), 403);

        $po = $this->quotation->customerPurchaseOrder;
        $project = $po->convertToProject(Auth::user());

        session()->flash('success', 'Project baru berhasil dibuat dari PO customer ini.');

        return $this->redirect(route('projects.show', $project), navigate: false);
    }
}
