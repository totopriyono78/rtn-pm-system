<?php

namespace App\Livewire\Sales;

use App\Models\Prospect;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ManageProspects extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public bool $showModal = false;

    public string $companyName = '';

    public string $contactName = '';

    public string $contactPhone = '';

    public string $contactEmail = '';

    public string $address = '';

    public string $source = '';

    public string $estimatedValue = '';

    public string $assignedTo = '';

    public string $notes = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-prospects'), 403);
    }

    public function render()
    {
        $prospects = Prospect::with(['assignee', 'customer'])
            ->when($this->search, fn ($q) => $q->where(fn ($q2) => $q2->where('company_name', 'like', "%{$this->search}%")
                ->orWhere('contact_name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%")))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(10);

        return view('livewire.sales.manage-prospects', [
            'prospects' => $prospects,
            'marketingUsers' => User::role('Marketing')->orderBy('name')->get(),
        ]);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'companyName' => ['required', 'string', 'max:255'],
            'contactName' => ['nullable', 'string', 'max:255'],
            'contactPhone' => ['nullable', 'string', 'max:50'],
            'contactEmail' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'source' => ['nullable', 'string', 'max:100'],
            'estimatedValue' => ['nullable', 'numeric', 'min:0'],
            'assignedTo' => ['nullable', Rule::exists('users', 'id')],
            'notes' => ['nullable', 'string'],
        ]);

        $prospect = Prospect::create([
            'code' => Prospect::generateCode(),
            'company_name' => $this->companyName,
            'contact_name' => $this->contactName ?: null,
            'contact_phone' => $this->contactPhone ?: null,
            'contact_email' => $this->contactEmail ?: null,
            'address' => $this->address ?: null,
            'source' => $this->source ?: null,
            'status' => 'prospek',
            'estimated_value' => $this->estimatedValue ?: null,
            'notes' => $this->notes ?: null,
            'assigned_to' => $this->assignedTo ?: null,
            'created_by' => auth()->id(),
        ]);

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Prospect "'.$prospect->company_name.'" berhasil ditambahkan.');
    }

    public function resetForm(): void
    {
        $this->reset(['companyName', 'contactName', 'contactPhone', 'contactEmail', 'address', 'source', 'estimatedValue', 'assignedTo', 'notes']);
        $this->resetErrorBag();
    }
}
