<?php

namespace App\Livewire\Admin;

use App\Models\PayrollRun;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar periode Payroll (SRS 4.17) -- Administrator membuat payroll run
 * baru per bulan, yang otomatis men-generate slip gaji untuk semua
 * karyawan dengan komponen gaji aktif (lihat EmployeeSalaries).
 */
#[Layout('layouts.app')]
class PayrollRuns extends Component
{
    use WithPagination;

    public bool $showCreateModal = false;

    public string $periodMonth = '';

    public string $periodYear = '';

    public const MONTHS = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-payroll'), 403);
        $this->periodMonth = (string) now()->month;
        $this->periodYear = (string) now()->year;
    }

    public function openCreate(): void
    {
        $this->resetErrorBag();
        $this->showCreateModal = true;
    }

    public function create(): void
    {
        $validated = $this->validate([
            'periodMonth' => ['required', 'integer', 'min:1', 'max:12'],
            'periodYear' => ['required', 'integer', 'min:2000', 'max:2100'],
        ], [], [
            'periodMonth' => 'Bulan',
            'periodYear' => 'Tahun',
        ]);

        $exists = PayrollRun::where('period_month', $validated['periodMonth'])
            ->where('period_year', $validated['periodYear'])
            ->where('status', '!=', 'dibatalkan')
            ->exists();

        if ($exists) {
            $this->addError('periodMonth', 'Payroll run untuk periode ini sudah ada (dan belum dibatalkan).');

            return;
        }

        $run = PayrollRun::create([
            'period_month' => $validated['periodMonth'],
            'period_year' => $validated['periodYear'],
            'status' => 'draft',
            'generated_by' => auth()->id(),
        ]);

        $run->generate();

        $this->showCreateModal = false;

        $this->redirect(route('admin.payroll.show', $run));
    }

    public function render()
    {
        $runs = PayrollRun::with('generator', 'finalizer')
            ->orderByDesc('period_year')
            ->orderByDesc('period_month')
            ->paginate(12);

        return view('livewire.admin.payroll-runs', [
            'runs' => $runs,
        ]);
    }
}
