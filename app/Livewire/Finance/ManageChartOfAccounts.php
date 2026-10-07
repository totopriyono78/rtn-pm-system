<?php

namespace App\Livewire\Finance;

use App\Models\ChartOfAccount;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Master Chart of Account -- khusus Administrator (`manage-general-
 * ledger`). Daftar diseed otomatis dari ChartOfAccountSeeder (COA default
 * jasa maintenance), bisa ditambah/diubah/dinonaktifkan bebas lewat
 * halaman ini. Akun yang sudah dipakai di jurnal manapun (draft atau
 * posted) tidak bisa dihapus permanen -- hanya bisa dinonaktifkan,
 * supaya riwayat jurnal lama tetap konsisten merujuk ke akun yang benar.
 */
#[Layout('layouts.app')]
class ManageChartOfAccounts extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $code = '';

    public string $name = '';

    public string $type = 'asset';

    public bool $isActive = true;

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-general-ledger'), 403);
    }

    public function render()
    {
        $accounts = ChartOfAccount::orderBy('code')->get();

        return view('livewire.finance.manage-chart-of-accounts', [
            'accounts' => $accounts,
        ]);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $account = ChartOfAccount::findOrFail($id);
        $this->editingId = $account->id;
        $this->code = $account->code;
        $this->name = $account->name;
        $this->type = $account->type;
        $this->isActive = $account->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'code' => ['required', 'string', 'max:20', Rule::unique('chart_of_accounts', 'code')->ignore($this->editingId)],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(ChartOfAccount::TYPES))],
        ]);

        ChartOfAccount::updateOrCreate(['id' => $this->editingId], [
            'code' => $this->code,
            'name' => $this->name,
            'type' => $this->type,
            'is_active' => $this->isActive,
            'created_by' => $this->editingId ? ChartOfAccount::findOrFail($this->editingId)->created_by : auth()->id(),
        ]);

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Chart of Account tersimpan.');
    }

    public function deleteAccount(int $id): void
    {
        $account = ChartOfAccount::findOrFail($id);
        abort_if($account->isUsedInAnyJournal(), 400, 'Akun ini sudah dipakai di jurnal, tidak bisa dihapus -- nonaktifkan saja.');
        $account->delete();
        session()->flash('success', 'Chart of Account dihapus.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'code', 'name']);
        $this->type = 'asset';
        $this->isActive = true;
        $this->resetErrorBag();
    }
}
