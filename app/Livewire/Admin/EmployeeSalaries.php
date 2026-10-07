<?php

namespace App\Livewire\Admin;

use App\Models\EmployeeSalary;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Komponen gaji tetap per karyawan (SRS 4.17) -- Administrator menetapkan
 * gaji pokok, tunjangan tetap, dan tarif lembur per jam untuk setiap
 * karyawan. Hanya karyawan is_active=true di sini yang akan disertakan
 * saat Payroll bulanan di-generate.
 */
#[Layout('layouts.app')]
class EmployeeSalaries extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $editingUserId = null;

    public string $baseSalary = '0';

    public string $fixedAllowance = '0';

    public string $overtimeHourlyRate = '0';

    public bool $isActive = true;

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-payroll'), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function edit(int $userId): void
    {
        $user = User::with('employeeSalary')->findOrFail($userId);
        $salary = $user->employeeSalary;

        $this->editingUserId = $userId;
        $this->baseSalary = $salary ? (string) $salary->base_salary : '0';
        $this->fixedAllowance = $salary ? (string) $salary->fixed_allowance : '0';
        $this->overtimeHourlyRate = $salary ? (string) $salary->overtime_hourly_rate : '0';
        $this->isActive = $salary ? $salary->is_active : true;
        $this->resetErrorBag();
    }

    public function cancelEdit(): void
    {
        $this->editingUserId = null;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'baseSalary' => ['required', 'numeric', 'min:0'],
            'fixedAllowance' => ['required', 'numeric', 'min:0'],
            'overtimeHourlyRate' => ['required', 'numeric', 'min:0'],
        ], [], [
            'baseSalary' => 'Gaji pokok',
            'fixedAllowance' => 'Tunjangan tetap',
            'overtimeHourlyRate' => 'Tarif lembur per jam',
        ]);

        EmployeeSalary::updateOrCreate(
            ['user_id' => $this->editingUserId],
            [
                'base_salary' => $validated['baseSalary'],
                'fixed_allowance' => $validated['fixedAllowance'],
                'overtime_hourly_rate' => $validated['overtimeHourlyRate'],
                'is_active' => $this->isActive,
                'updated_by' => auth()->id(),
            ]
        );

        session()->flash('success', 'Komponen gaji berhasil disimpan.');
        $this->editingUserId = null;
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->with('employeeSalary')
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.admin.employee-salaries', [
            'users' => $users,
        ]);
    }
}
