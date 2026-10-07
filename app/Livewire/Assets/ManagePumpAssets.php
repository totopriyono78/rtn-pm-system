<?php

namespace App\Livewire\Assets;

use App\Models\Customer;
use App\Models\Project;
use App\Models\PumpAsset;
use App\Models\Site;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * CMS Master Unit Pompa (Aset Klien) -- SRS v2.0 modul 4.4.1. Lihat catatan
 * penamaan di App\Models\PumpAsset supaya tidak bingung dengan Unit (unit
 * organisasi internal, beda konsep sama sekali).
 */
#[Layout('layouts.app')]
class ManagePumpAssets extends Component
{
    use WithPagination;

    public string $search = '';

    public string $conditionFilter = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $customerId = '';

    public string $siteId = '';

    public string $serialNumber = '';

    public string $pumpType = '';

    public string $pumpModel = '';

    public string $pumpCapacity = '';

    public string $engineType = '';

    public string $engineModel = '';

    public string $installDate = '';

    public string $condition = 'baik';

    public string $notes = '';

    public bool $isActive = true;

    public bool $showServiceLogModal = false;

    public ?int $serviceLogAssetId = null;

    public string $serviceDate = '';

    public string $serviceDescription = '';

    public string $serviceTechnicianId = '';

    public string $serviceProjectId = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-pump-assets'), 403);
    }

    public function render()
    {
        $assets = PumpAsset::query()
            ->with(['customer', 'site'])
            ->when($this->search, fn ($q) => $q->where(fn ($q2) => $q2->where('serial_number', 'like', "%{$this->search}%")
                ->orWhere('pump_type', 'like', "%{$this->search}%")
                ->orWhere('pump_model', 'like', "%{$this->search}%")))
            ->when($this->conditionFilter, fn ($q) => $q->where('condition', $this->conditionFilter))
            ->orderBy('pump_type')
            ->paginate(10);

        return view('livewire.assets.manage-pump-assets', [
            'assets' => $assets,
            'customers' => Customer::orderBy('name')->get(),
            'sites' => Site::orderBy('name')->get(),
            'serviceLogAsset' => $this->serviceLogAssetId ? PumpAsset::with('serviceLogs.technician', 'serviceLogs.project')->find($this->serviceLogAssetId) : null,
            'technicians' => User::role('Teknisi')->orderBy('name')->get(),
            'projects' => Project::orderByDesc('id')->limit(100)->get(),
        ]);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $asset = PumpAsset::findOrFail($id);
        $this->editingId = $asset->id;
        $this->customerId = (string) $asset->customer_id;
        $this->siteId = (string) $asset->site_id;
        $this->serialNumber = (string) $asset->serial_number;
        $this->pumpType = $asset->pump_type;
        $this->pumpModel = (string) $asset->pump_model;
        $this->pumpCapacity = (string) $asset->pump_capacity;
        $this->engineType = (string) $asset->engine_type;
        $this->engineModel = (string) $asset->engine_model;
        $this->installDate = optional($asset->install_date)->format('Y-m-d') ?? '';
        $this->condition = $asset->condition;
        $this->notes = (string) $asset->notes;
        $this->isActive = $asset->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'customerId' => ['nullable', Rule::exists('customers', 'id')],
            'siteId' => ['nullable', Rule::exists('sites', 'id')],
            'serialNumber' => ['nullable', 'string', 'max:100'],
            'pumpType' => ['required', 'string', 'max:255'],
            'pumpModel' => ['nullable', 'string', 'max:255'],
            'pumpCapacity' => ['nullable', 'string', 'max:255'],
            'engineType' => ['nullable', 'string', 'max:255'],
            'engineModel' => ['nullable', 'string', 'max:255'],
            'installDate' => ['nullable', 'date'],
            'condition' => ['required', Rule::in(array_keys(PumpAsset::CONDITIONS))],
            'notes' => ['nullable', 'string'],
        ]);

        PumpAsset::updateOrCreate(['id' => $this->editingId], [
            'customer_id' => $this->customerId ?: null,
            'site_id' => $this->siteId ?: null,
            'serial_number' => $this->serialNumber ?: null,
            'pump_type' => $this->pumpType,
            'pump_model' => $this->pumpModel ?: null,
            'pump_capacity' => $this->pumpCapacity ?: null,
            'engine_type' => $this->engineType ?: null,
            'engine_model' => $this->engineModel ?: null,
            'install_date' => $this->installDate ?: null,
            'condition' => $this->condition,
            'notes' => $this->notes ?: null,
            'is_active' => $this->isActive,
        ]);

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Unit pompa tersimpan.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'customerId', 'siteId', 'serialNumber', 'pumpType', 'pumpModel', 'pumpCapacity', 'engineType', 'engineModel', 'installDate', 'notes']);
        $this->condition = 'baik';
        $this->isActive = true;
        $this->resetErrorBag();
    }

    public function openServiceLog(int $assetId): void
    {
        $this->serviceLogAssetId = $assetId;
        $this->serviceDate = now()->format('Y-m-d');
        $this->serviceDescription = '';
        $this->serviceTechnicianId = '';
        $this->serviceProjectId = '';
        $this->showServiceLogModal = true;
    }

    public function saveServiceLog(): void
    {
        $this->validate([
            'serviceDate' => ['required', 'date'],
            'serviceDescription' => ['required', 'string'],
            'serviceTechnicianId' => ['nullable', Rule::exists('users', 'id')],
            'serviceProjectId' => ['nullable', Rule::exists('projects', 'id')],
        ]);

        $asset = PumpAsset::findOrFail($this->serviceLogAssetId);

        $asset->serviceLogs()->create([
            'service_date' => $this->serviceDate,
            'description' => $this->serviceDescription,
            'technician_user_id' => $this->serviceTechnicianId ?: null,
            'project_id' => $this->serviceProjectId ?: null,
            'created_by' => auth()->id(),
        ]);

        if (! $asset->last_service_date || $this->serviceDate > $asset->last_service_date->format('Y-m-d')) {
            $asset->update(['last_service_date' => $this->serviceDate]);
        }

        $this->showServiceLogModal = false;
        session()->flash('success', 'Riwayat service ditambahkan.');
    }

    public function closeServiceLog(): void
    {
        $this->showServiceLogModal = false;
        $this->serviceLogAssetId = null;
    }
}
