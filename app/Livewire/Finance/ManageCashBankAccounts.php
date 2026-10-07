<?php

namespace App\Livewire\Finance;

use App\Models\CashBankAccount;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Master akun Kas/Bank -- bagian ringan dari Finance & Accounting (SRS
 * 4.14, Cash & Bank). Administrator mendaftarkan akun (kas fisik/rekening
 * bank) di sini sebelum bisa dipakai sebagai tujuan pelunasan Invoice atau
 * sumber pencairan Kasbon.
 */
#[Layout('layouts.app')]
class ManageCashBankAccounts extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    public string $name = '';

    public string $type = 'bank';

    public string $bankName = '';

    public string $accountNumber = '';

    public string $openingBalance = '0';

    public bool $isActive = true;

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-cash-bank'), 403);
    }

    public function render()
    {
        return view('livewire.finance.manage-cash-bank-accounts', [
            'accounts' => CashBankAccount::orderBy('name')->get(),
        ]);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $account = CashBankAccount::findOrFail($id);

        $this->editingId = $account->id;
        $this->name = $account->name;
        $this->type = $account->type;
        $this->bankName = (string) $account->bank_name;
        $this->accountNumber = (string) $account->account_number;
        $this->openingBalance = (string) $account->opening_balance;
        $this->isActive = $account->is_active;
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(array_keys(CashBankAccount::TYPES))],
            'bankName' => ['nullable', 'string', 'max:255'],
            'accountNumber' => ['nullable', 'string', 'max:100'],
            'openingBalance' => ['required', 'numeric'],
        ], [], [
            'name' => 'Nama akun',
            'bankName' => 'Nama bank',
            'accountNumber' => 'No. rekening',
            'openingBalance' => 'Saldo awal',
        ]);

        CashBankAccount::updateOrCreate(['id' => $this->editingId], [
            'name' => $validated['name'],
            'type' => $validated['type'],
            'bank_name' => $validated['bankName'] ?: null,
            'account_number' => $validated['accountNumber'] ?: null,
            'opening_balance' => $validated['openingBalance'],
            'is_active' => $this->isActive,
            'created_by' => $this->editingId ? CashBankAccount::findOrFail($this->editingId)->created_by : auth()->id(),
        ]);

        $this->showModal = false;
        session()->flash('success', 'Akun Kas/Bank tersimpan.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'name', 'bankName', 'accountNumber', 'openingBalance']);
        $this->type = 'bank';
        $this->openingBalance = '0';
        $this->isActive = true;
        $this->resetErrorBag();
    }
}
