<?php

namespace App\Livewire\Contracts;

use App\Models\Contract;
use App\Models\Customer;
use App\Models\Site;
use App\Models\Unit;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ManageContracts extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public string $typeFilter = '';

    public bool $showModal = false;

    public string $customerId = '';

    public string $unitId = '';

    public string $contractNumber = '';

    public string $contractType = 'spesifik';

    public string $contractDate = '';

    public string $startDate = '';

    public string $endDate = '';

    public string $scopeDescription = '';

    public string $fixedValue = '';

    public string $maxValue = '';

    /** @var array<int> */
    public array $siteIds = [];

    /** @var mixed */
    public $document;

    public function mount(): void
    {
        abort_unless(auth()->user()->hasAnyPermission(['manage-contracts', 'view-contract-value']), 403);
    }

    public function render()
    {
        $contracts = Contract::with(['customer', 'unit'])
            ->withCount('releaseOrders')
            ->when($this->search, fn ($q) => $q->where(fn ($q2) => $q2->where('contract_number', 'like', "%{$this->search}%")
                ->orWhereHas('customer', fn ($q3) => $q3->where('name', 'like', "%{$this->search}%"))))
            ->when($this->typeFilter, fn ($q) => $q->where('contract_type', $this->typeFilter))
            ->latest()
            ->paginate(10);

        return view('livewire.contracts.manage-contracts', [
            'contracts' => $contracts,
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'units' => Unit::with('region')->orderBy('name')->get(),
            'sites' => Site::where('is_active', true)->orderBy('name')->get(),
            'canManage' => auth()->user()->hasPermissionTo('manage-contracts'),
        ]);
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-contracts'), 403);
        $this->resetForm();
        $this->contractNumber = Contract::generateNumberSuggestion();
        $this->contractDate = now()->format('Y-m-d');
        $this->showModal = true;
    }

    public function save(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-contracts'), 403);

        $this->validate([
            'customerId' => ['required', Rule::exists('customers', 'id')],
            'unitId' => ['nullable', Rule::exists('units', 'id')],
            'contractNumber' => ['required', 'string', 'max:255', Rule::unique('contracts', 'contract_number')],
            'contractType' => ['required', Rule::in(array_keys(Contract::TYPES))],
            'contractDate' => ['required', 'date'],
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date', 'after_or_equal:startDate'],
            'scopeDescription' => ['nullable', 'string'],
            'fixedValue' => ['nullable', 'numeric', 'min:0', 'required_if:contractType,spesifik'],
            'maxValue' => ['nullable', 'numeric', 'min:0', 'required_if:contractType,payung'],
            'siteIds' => ['nullable', 'array'],
            'siteIds.*' => [Rule::exists('sites', 'id')],
            'document' => ['nullable', 'file', 'mimes:pdf,doc,docx,jpg,jpeg,png', 'max:20480'],
        ]);

        $documentPath = null;
        if ($this->document) {
            $documentPath = $this->document->store('contracts', 'local');
        }

        $contract = Contract::create([
            'customer_id' => $this->customerId,
            'unit_id' => $this->unitId ?: null,
            'contract_number' => $this->contractNumber,
            'contract_type' => $this->contractType,
            'contract_date' => $this->contractDate,
            'start_date' => $this->startDate ?: null,
            'end_date' => $this->endDate ?: null,
            'scope_description' => $this->scopeDescription ?: null,
            'fixed_value' => $this->contractType === 'spesifik' ? $this->fixedValue : null,
            'max_value' => $this->contractType === 'payung' ? $this->maxValue : null,
            'status' => 'active',
            'document_path' => $documentPath,
            'created_by' => auth()->id(),
        ]);

        if (! empty($this->siteIds)) {
            $contract->sites()->sync($this->siteIds);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Contract tersimpan. Silakan lengkapi detailnya (site, dokumen) di halaman detail.');
    }

    public function downloadDocument(int $id)
    {
        abort_unless(auth()->user()->hasAnyPermission(['manage-contracts', 'view-contract-value']), 403);

        $contract = Contract::findOrFail($id);
        abort_unless($contract->document_path, 404);

        return Storage::disk('local')->download($contract->document_path, $contract->contract_number.'.pdf');
    }

    public function resetForm(): void
    {
        $this->reset(['customerId', 'unitId', 'contractNumber', 'startDate', 'endDate', 'scopeDescription', 'fixedValue', 'maxValue', 'siteIds', 'document']);
        $this->contractType = 'spesifik';
        $this->resetErrorBag();
    }
}
