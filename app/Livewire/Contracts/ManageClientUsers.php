<?php

namespace App\Livewire\Contracts;

use App\Models\ClientUser;
use App\Models\Customer;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Kelola akun login Client Portal (SRS 4.3) untuk satu Customer tertentu --
 * dibuka dari halaman Kelola Customer. Memakai permission 'manage-customers'
 * yang sudah ada (bukan permission baru) karena ini murni perluasan dari
 * pengelolaan data customer yang sudah dilindungi permission itu.
 */
#[Layout('layouts.app')]
class ManageClientUsers extends Component
{
    public Customer $customer;

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public bool $isActive = true;

    public function mount(Customer $customer): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-customers'), 403);
        $this->customer = $customer;
    }

    public function render()
    {
        $clientUsers = $this->customer->clientUsers()->orderBy('name')->get();

        return view('livewire.contracts.manage-client-users', [
            'clientUsers' => $clientUsers,
        ]);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $clientUser = ClientUser::where('customer_id', $this->customer->id)->findOrFail($id);
        $this->editingId = $clientUser->id;
        $this->name = $clientUser->name;
        $this->email = $clientUser->email;
        $this->isActive = $clientUser->is_active;
        $this->password = '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('client_users', 'email')->ignore($this->editingId)],
            'password' => [$this->editingId ? 'nullable' : 'required', 'string', 'min:6'],
        ]);

        $clientUser = ClientUser::updateOrCreate(['id' => $this->editingId], [
            'customer_id' => $this->customer->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_active' => $this->isActive,
        ] + ($this->password ? ['password' => $this->password] : []));

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Akun client tersimpan.');
    }

    public function toggleActive(int $id): void
    {
        $clientUser = ClientUser::where('customer_id', $this->customer->id)->findOrFail($id);
        $clientUser->update(['is_active' => ! $clientUser->is_active]);
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'email', 'password']);
        $this->isActive = true;
        $this->resetErrorBag();
    }
}
