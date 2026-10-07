<?php

namespace App\Livewire\Operasional;

use App\Models\DeliveryGatepass;
use App\Models\Project;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

/**
 * Admin Purchase (SRS v2.0 modul 4.13): surat jalan & gatepass, dengan
 * tracking dasar (nomor dokumen, supir, plat kendaraan).
 */
#[Layout('layouts.app')]
class ManageGatepasses extends Component
{
    use WithFileUploads, WithPagination;

    public string $typeFilter = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $type = 'surat_jalan';

    public string $documentNumber = '';

    public string $purchaseOrderId = '';

    public string $projectId = '';

    public string $vendorName = '';

    public string $driverName = '';

    public string $vehiclePlate = '';

    public string $gateDate = '';

    public string $notes = '';

    public $file = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-delivery-gatepass'), 403);
    }

    public function render()
    {
        $records = DeliveryGatepass::query()
            ->with(['purchaseOrder', 'project'])
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->latest('gate_date')
            ->paginate(10);

        return view('livewire.operasional.manage-gatepasses', [
            'records' => $records,
            'purchaseOrders' => PurchaseOrder::with('vendor')->latest()->limit(100)->get(),
            'projects' => Project::orderByDesc('id')->limit(100)->get(),
            'maxUploadMb' => (int) env('MAX_UPLOAD_SIZE_MB', 50),
        ]);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $record = DeliveryGatepass::findOrFail($id);
        $this->editingId = $record->id;
        $this->type = $record->type;
        $this->documentNumber = (string) $record->document_number;
        $this->purchaseOrderId = (string) $record->purchase_order_id;
        $this->projectId = (string) $record->project_id;
        $this->vendorName = (string) $record->vendor_name;
        $this->driverName = (string) $record->driver_name;
        $this->vehiclePlate = (string) $record->vehicle_plate;
        $this->gateDate = optional($record->gate_date)->format('Y-m-d') ?? '';
        $this->notes = (string) $record->notes;
        $this->showModal = true;
    }

    public function save(): void
    {
        $maxKb = ((int) env('MAX_UPLOAD_SIZE_MB', 50)) * 1024;

        $this->validate([
            'type' => ['required', Rule::in(array_keys(DeliveryGatepass::TYPES))],
            'documentNumber' => ['nullable', 'string', 'max:100'],
            'purchaseOrderId' => ['nullable', Rule::exists('purchase_orders', 'id')],
            'projectId' => ['nullable', Rule::exists('projects', 'id')],
            'vendorName' => ['nullable', 'string', 'max:255'],
            'driverName' => ['nullable', 'string', 'max:255'],
            'vehiclePlate' => ['nullable', 'string', 'max:50'],
            'gateDate' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:'.$maxKb],
        ]);

        $record = DeliveryGatepass::updateOrCreate(['id' => $this->editingId], [
            'type' => $this->type,
            'document_number' => $this->documentNumber ?: null,
            'purchase_order_id' => $this->purchaseOrderId ?: null,
            'project_id' => $this->projectId ?: null,
            'vendor_name' => $this->vendorName ?: null,
            'driver_name' => $this->driverName ?: null,
            'vehicle_plate' => $this->vehiclePlate ?: null,
            'gate_date' => $this->gateDate,
            'notes' => $this->notes ?: null,
            'created_by' => $this->editingId ? DeliveryGatepass::find($this->editingId)->created_by : auth()->id(),
        ]);

        if ($this->file) {
            if ($record->disk_path) {
                Storage::disk('local')->delete($record->disk_path);
            }
            $path = $this->file->store('gatepass-files', 'local');
            $record->update([
                'disk_path' => $path,
                'original_name' => $this->file->getClientOriginalName(),
            ]);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Data tersimpan.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'documentNumber', 'purchaseOrderId', 'projectId', 'vendorName', 'driverName', 'vehiclePlate', 'gateDate', 'notes', 'file']);
        $this->type = 'surat_jalan';
        $this->resetErrorBag();
    }
}
