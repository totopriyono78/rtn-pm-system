<?php

namespace App\Livewire\Finance;

use App\Models\CashBankAccount;
use App\Models\CashBankTransaction;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Buku Kas/Bank -- bagian ringan dari Finance & Accounting (SRS 4.14,
 * Cash & Bank). Daftar transaksi masuk/keluar per akun + saldo berjalan.
 * Transaksi otomatis dibuat saat Invoice ditandai lunas
 * (Invoice::markPaid), Kasbon dicairkan (CashAdvance::disburse), dan PO
 * ditandai dibayar (PurchaseOrder::payVendor); transaksi lain
 * (operasional, pemasukan/pengeluaran lain) dicatat manual lewat halaman
 * ini. Tidak ada edit/delete transaksi -- koreksi salah catat dilakukan
 * lewat entri balik (transaksi baru arah berlawanan).
 */
#[Layout('layouts.app')]
class CashBankLedger extends Component
{
    use WithPagination;

    public string $accountFilter = '';

    public string $typeFilter = '';

    public bool $showModal = false;

    public string $cashBankAccountId = '';

    public string $type = 'out';

    public string $category = 'operational_expense';

    public string $amount = '';

    public string $transactionDate = '';

    public string $description = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-cash-bank'), 403);
        $this->transactionDate = now()->format('Y-m-d');
    }

    public function render()
    {
        $transactions = CashBankTransaction::query()
            ->with('account', 'invoice', 'cashAdvance', 'purchaseOrder.vendor', 'creator')
            ->when($this->accountFilter, fn ($q) => $q->where('cash_bank_account_id', $this->accountFilter))
            ->when($this->typeFilter, fn ($q) => $q->where('type', $this->typeFilter))
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(15);

        return view('livewire.finance.cash-bank-ledger', [
            'transactions' => $transactions,
            'accounts' => CashBankAccount::orderBy('name')->get(),
        ]);
    }

    public function openCreate(): void
    {
        $this->reset(['cashBankAccountId', 'amount', 'description']);
        $this->type = 'out';
        $this->category = 'operational_expense';
        $this->transactionDate = now()->format('Y-m-d');
        $this->resetErrorBag();
        $this->showModal = true;
    }

    public function updatedType(): void
    {
        // Reset kategori ke default yang masuk akal setiap ganti arah transaksi,
        // supaya tidak kebawa kategori "keluar" saat tipe sudah "masuk" (atau sebaliknya).
        $this->category = $this->type === 'in' ? 'other_income' : 'operational_expense';
    }

    public function save(): void
    {
        $validated = $this->validate([
            'cashBankAccountId' => ['required', Rule::exists('cash_bank_accounts', 'id')],
            'type' => ['required', Rule::in(array_keys(CashBankTransaction::TYPES))],
            'category' => ['required', Rule::in(array_keys(CashBankTransaction::CATEGORIES))],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'transactionDate' => ['required', 'date'],
            'description' => ['nullable', 'string', 'max:500'],
        ], [], [
            'cashBankAccountId' => 'Akun',
            'transactionDate' => 'Tanggal',
        ]);

        CashBankTransaction::create([
            'cash_bank_account_id' => $validated['cashBankAccountId'],
            'type' => $validated['type'],
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'transaction_date' => $validated['transactionDate'],
            'description' => $validated['description'] ?: null,
            'created_by' => auth()->id(),
        ]);

        $this->showModal = false;
        session()->flash('success', 'Transaksi tercatat.');
    }
}
