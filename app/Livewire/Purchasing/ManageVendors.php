<?php

namespace App\Livewire\Purchasing;

use App\Models\Vendor;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ManageVendors extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $code = '';

    public string $name = '';

    public string $contactPerson = '';

    public string $phone = '';

    public string $email = '';

    public string $address = '';

    public string $npwp = '';

    public string $notes = '';

    public bool $isActive = true;

    public function render()
    {
        $vendors = Vendor::withCount('purchaseOrders')
            ->when($this->search, fn ($q) => $q->where(fn ($q2) => $q2->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%")))
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.purchasing.manage-vendors', [
            'vendors' => $vendors,
            'canManage' => auth()->user()->hasPermissionTo('manage-purchasing'),
        ]);
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-purchasing'), 403);
        $this->resetForm();
        $this->code = Vendor::generateCode();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-purchasing'), 403);

        $vendor = Vendor::findOrFail($id);
        $this->editingId = $vendor->id;
        $this->code = $vendor->code;
        $this->name = $vendor->name;
        $this->contactPerson = (string) $vendor->contact_person;
        $this->phone = (string) $vendor->phone;
        $this->email = (string) $vendor->email;
        $this->address = (string) $vendor->address;
        $this->npwp = (string) $vendor->npwp;
        $this->notes = (string) $vendor->notes;
        $this->isActive = $vendor->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-purchasing'), 403);

        $this->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('vendors', 'code')->ignore($this->editingId)],
            'name' => ['required', 'string', 'max:255'],
            'contactPerson' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'npwp' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        Vendor::updateOrCreate(['id' => $this->editingId], [
            'code' => $this->code,
            'name' => $this->name,
            'contact_person' => $this->contactPerson ?: null,
            'phone' => $this->phone ?: null,
            'email' => $this->email ?: null,
            'address' => $this->address ?: null,
            'npwp' => $this->npwp ?: null,
            'notes' => $this->notes ?: null,
            'is_active' => $this->isActive,
        ]);

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Data vendor tersimpan.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'code', 'name', 'contactPerson', 'phone', 'email', 'address', 'npwp', 'notes']);
        $this->isActive = true;
        $this->resetErrorBag();
    }
}
