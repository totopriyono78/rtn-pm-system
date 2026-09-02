<?php

namespace App\Livewire\Admin;

use App\Models\Region;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

#[Layout('layouts.app')]
class ManageUsers extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $role = '';

    public bool $is_active = true;

    /** @var array<int> */
    public array $selectedRegions = [];

    /** @var array<string> */
    public array $extraPermissions = [];

    public function render()
    {
        $users = User::query()
            ->when($this->search, fn ($q) => $q->where(fn ($q2) => $q2->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%")))
            ->with('roles', 'regions')
            ->orderBy('name')
            ->paginate(10);

        return view('livewire.admin.manage-users', [
            'users' => $users,
            'roles' => Role::orderBy('name')->get(),
            'regions' => Region::orderBy('name')->get(),
            'allPermissions' => \Spatie\Permission\Models\Permission::orderBy('name')->get(),
        ]);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $userId): void
    {
        $user = User::with('roles', 'regions')->findOrFail($userId);
        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->password = '';
        $this->role = $user->roles->first()?->name ?? '';
        $this->is_active = $user->is_active;
        $this->selectedRegions = $user->regions->pluck('id')->toArray();

        // permission langsung (override) di luar yang sudah didapat dari role
        $roleGranted = $this->role ? Role::findByName($this->role)->permissions->pluck('name')->toArray() : [];
        $this->extraPermissions = $user->getDirectPermissions()->pluck('name')
            ->reject(fn ($p) => in_array($p, $roleGranted))
            ->values()->toArray();

        $this->showModal = true;
    }

    public function toggleActive(int $userId): void
    {
        $user = User::findOrFail($userId);

        if ($user->id === auth()->id()) {
            session()->flash('error', 'Anda tidak dapat menonaktifkan akun sendiri.');

            return;
        }

        $user->is_active = ! $user->is_active;
        $user->save();
    }

    public function save(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->editingId)],
            'password' => [$this->editingId ? 'nullable' : 'required', 'string', 'min:6'],
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
        ]);

        if ($this->editingId) {
            $user = User::findOrFail($this->editingId);
            $user->name = $this->name;
            $user->email = $this->email;
            if ($this->password) {
                $user->password = $this->password;
            }
            $user->is_active = $this->is_active;
            $user->save();
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => $this->password,
                'is_active' => $this->is_active,
            ]);
        }

        $user->syncRoles([$this->role]);

        $roleGranted = Role::findByName($this->role)->permissions->pluck('name')->toArray();
        $user->syncPermissions(array_values(array_diff($this->extraPermissions, $roleGranted)));

        $user->regions()->sync($this->selectedRegions);

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Data user berhasil disimpan.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'email', 'password', 'role', 'is_active', 'selectedRegions', 'extraPermissions']);
        $this->is_active = true;
        $this->resetErrorBag();
    }
}
