<?php

namespace App\Livewire\Admin;

use App\Models\PayrollRun;
use App\Models\Payslip;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Detail satu periode Payroll (SRS 4.17) -- daftar slip gaji per karyawan,
 * dengan kolom manual BPJS/PPh21 yang dapat diedit selagi masih draft.
 * Finalisasi menutup otomatis kasbon yang sudah dipotong di slip gaji
 * terkait (lihat Payslip::closeIncludedCashAdvances).
 */
#[Layout('layouts.app')]
class PayrollRunDetail extends Component
{
    public PayrollRun $payrollRun;

    public ?int $editingPayslipId = null;

    public string $bpjsDeduction = '0';

    public string $pph21Deduction = '0';

    public string $notes = '';

    public function mount(PayrollRun $payrollRun): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-payroll'), 403);
        $this->payrollRun = $payrollRun;
    }

    public function editPayslip(int $payslipId): void
    {
        $payslip = Payslip::findOrFail($payslipId);

        $this->editingPayslipId = $payslipId;
        $this->bpjsDeduction = (string) $payslip->bpjs_deduction;
        $this->pph21Deduction = (string) $payslip->pph21_deduction;
        $this->notes = (string) ($payslip->notes ?? '');
        $this->resetErrorBag();
    }

    public function cancelEditPayslip(): void
    {
        $this->editingPayslipId = null;
    }

    public function savePayslip(): void
    {
        $validated = $this->validate([
            'bpjsDeduction' => ['required', 'numeric', 'min:0'],
            'pph21Deduction' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [], [
            'bpjsDeduction' => 'Potongan BPJS',
            'pph21Deduction' => 'Potongan PPh21',
        ]);

        $payslip = Payslip::findOrFail($this->editingPayslipId);
        $payslip->updateManualDeductions(
            (float) $validated['bpjsDeduction'],
            (float) $validated['pph21Deduction'],
            $validated['notes'] ?: null
        );

        $this->editingPayslipId = null;
        session()->flash('success', 'Slip gaji diperbarui.');
    }

    public function regenerate(): void
    {
        $this->payrollRun->generate();
        session()->flash('success', 'Payroll run di-generate ulang dari data terbaru (komponen gaji, lembur, kasbon). Potongan BPJS/PPh21 yang sudah diisi tetap dipertahankan.');
    }

    public function finalize(): void
    {
        $this->payrollRun->finalize(auth()->user());
        session()->flash('success', 'Payroll run difinalisasi. Kasbon yang sudah dipotong di slip gaji otomatis ditutup.');
    }

    public function cancelRun(): void
    {
        $this->payrollRun->cancel(auth()->user());
        session()->flash('success', 'Payroll run dibatalkan.');
    }

    public function render()
    {
        $payslips = $this->payrollRun->payslips()->with('user')->get()->sortBy(fn (Payslip $p) => $p->user->name)->values();

        return view('livewire.admin.payroll-run-detail', [
            'payslips' => $payslips,
        ]);
    }
}
