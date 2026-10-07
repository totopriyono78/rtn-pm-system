<?php

namespace App\Livewire\Contracts;

use App\Models\Customer;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ManageCustomers extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $code = '';

    public string $name = '';

    public string $address = '';

    public string $npwp = '';

    public string $picName = '';

    public string $picPhone = '';

    public string $picEmail = '';

    public bool $isActive = true;

    public function mount(): void
    {
        abort_unless(auth()->user()->hasAnyPermission(['manage-customers', 'manage-contracts']), 403);
    }

    public function render()
    {
        $customers = Customer::withCount('contracts')
            ->when($this->search, fn ($q) => $q->where(fn ($q2) => $q2->where('name', 'like', "%{$this->search}%")
                ->orWhere('code', 'like', "%{$this->search}%")))
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.contracts.manage-customers', [
            'customers' => $customers,
            'canManage' => auth()->user()->hasPermissionTo('manage-customers'),
        ]);
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-customers'), 403);
        $this->resetForm();
        $this->code = Customer::generateCode();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-customers'), 403);

        $customer = Customer::findOrFail($id);
        $this->editingId = $customer->id;
        $this->code = $customer->code;
        $this->name = $customer->name;
        $this->address = (string) $customer->address;
        $this->npwp = (string) $customer->npwp;
        $this->picName = (string) $customer->pic_name;
        $this->picPhone = (string) $customer->pic_phone;
        $this->picEmail = (string) $customer->pic_email;
        $this->isActive = $customer->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-customers'), 403);

        $this->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('customers', 'code')->ignore($this->editingId)],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'npwp' => ['nullable', 'string', 'max:50'],
            'picName' => ['nullable', 'string', 'max:255'],
            'picPhone' => ['nullable', 'string', 'max:50'],
            'picEmail' => ['nullable', 'email', 'max:255'],
        ]);

        Customer::updateOrCreate(['id' => $this->editingId], [
            'code' => $this->code,
            'name' => $this->name,
            'address' => $this->address ?: null,
            'npwp' => $this->npwp ?: null,
            'pic_name' => $this->picName ?: null,
            'pic_phone' => $this->picPhone ?: null,
            'pic_email' => $this->picEmail ?: null,
            'is_active' => $this->isActive,
        ]);

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Data customer tersimpan.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'code', 'name', 'address', 'npwp', 'picName', 'picPhone', 'picEmail']);
        $this->isActive = true;
        $this->resetErrorBag();
    }
}
