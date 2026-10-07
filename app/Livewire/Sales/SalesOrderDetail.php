<?php

namespace App\Livewire\Sales;

use App\Models\SalesOrder;
use App\Models\SalesOrderDocument;
use App\Models\SalesOrderItem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class SalesOrderDetail extends Component
{
    use WithFileUploads;

    public SalesOrder $salesOrder;

    // --- item pompa ---
    public bool $showItemModal = false;

    public string $itemName = '';

    public string $specification = '';

    public string $quantity = '1';

    public string $unit = 'unit';

    public string $unitPrice = '';

    // --- dokumen teknis ---
    public bool $showDocumentModal = false;

    public string $documentCategory = 'datasheet';

    /** @var mixed */
    public $documentFile;

    // --- cancel ---
    public bool $showCancelModal = false;

    public function mount(SalesOrder $salesOrder): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-sales-orders'), 403);
        $this->salesOrder = $salesOrder;
    }

    public function render()
    {
        $this->salesOrder->load(['customer', 'prospect', 'unit', 'creator', 'items', 'documents.uploader', 'contract.directProject']);

        return view('livewire.sales.sales-order-detail');
    }

    // ===== Item =====

    public function openAddItem(): void
    {
        abort_unless($this->salesOrder->status === 'draft', 400, 'Hanya Sales Order draft yang bisa diubah.');
        $this->reset(['itemName', 'specification', 'unitPrice']);
        $this->quantity = '1';
        $this->unit = 'unit';
        $this->showItemModal = true;
    }

    public function saveItem(): void
    {
        abort_unless($this->salesOrder->status === 'draft', 400, 'Hanya Sales Order draft yang bisa diubah.');

        $this->validate([
            'itemName' => ['required', 'string', 'max:255'],
            'specification' => ['nullable', 'string'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'unit' => ['nullable', 'string', 'max:50'],
            'unitPrice' => ['required', 'numeric', 'min:0'],
        ]);

        SalesOrderItem::create([
            'sales_order_id' => $this->salesOrder->id,
            'item_name' => $this->itemName,
            'specification' => $this->specification ?: null,
            'quantity' => $this->quantity,
            'unit' => $this->unit ?: null,
            'unit_price' => $this->unitPrice,
            'subtotal' => (float) $this->quantity * (float) $this->unitPrice,
        ]);

        $this->salesOrder->recalcTotal();

        $this->showItemModal = false;
        session()->flash('success', 'Item pompa ditambahkan.');
    }

    public function removeItem(int $id): void
    {
        abort_unless($this->salesOrder->status === 'draft', 400, 'Hanya Sales Order draft yang bisa diubah.');

        SalesOrderItem::where('sales_order_id', $this->salesOrder->id)->findOrFail($id)->delete();
        $this->salesOrder->recalcTotal();
        session()->flash('success', 'Item dihapus.');
    }

    // ===== Dokumen teknis =====

    public function openDocumentModal(): void
    {
        $this->reset(['documentFile']);
        $this->documentCategory = 'datasheet';
        $this->showDocumentModal = true;
    }

    public function saveDocument(): void
    {
        $this->validate([
            'documentCategory' => ['required', Rule::in(array_keys(SalesOrderDocument::CATEGORIES))],
            'documentFile' => ['required', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,dwg', 'max:20480'],
        ]);

        $path = $this->documentFile->store('sales-order-documents', 'local');

        SalesOrderDocument::create([
            'sales_order_id' => $this->salesOrder->id,
            'uploaded_by' => auth()->id(),
            'category' => $this->documentCategory,
            'disk_path' => $path,
            'original_name' => $this->documentFile->getClientOriginalName(),
            'mime_type' => $this->documentFile->getMimeType(),
            'size_bytes' => $this->documentFile->getSize(),
        ]);

        $this->showDocumentModal = false;
        session()->flash('success', 'Dokumen teknis diunggah.');
    }

    public function removeDocument(int $id): void
    {
        $document = SalesOrderDocument::where('sales_order_id', $this->salesOrder->id)->findOrFail($id);
        Storage::disk('local')->delete($document->disk_path);
        $document->delete();
        session()->flash('success', 'Dokumen dihapus.');
    }

    // ===== Konfirmasi / batal =====

    public function confirm()
    {
        $project = $this->salesOrder->confirm();
        session()->flash('success', 'Sales Order dikonfirmasi. Contract & Project baru berhasil dibuat.');

        return $this->redirect(route('projects.show', $project), navigate: false);
    }

    public function cancel(): void
    {
        $this->salesOrder->cancel();
        $this->showCancelModal = false;
        session()->flash('success', 'Sales Order dibatalkan.');
    }
}
