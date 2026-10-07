<?php

namespace App\Livewire\Purchasing;

use App\Models\CashBankAccount;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ManagePurchaseOrders extends Component
{
    use WithPagination;

    public string $vendorFilter = '';

    public string $statusFilter = '';

    public bool $showPayModal = false;

    public ?int $payingId = null;

    public string $cashBankAccountId = '';

    public string $paymentReference = '';

    public function render()
    {
        $orders = PurchaseOrder::with('vendor', 'project')
            ->when($this->vendorFilter, fn ($q) => $q->where('vendor_id', $this->vendorFilter))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(10);

        return view('livewire.purchasing.manage-purchase-orders', [
            'orders' => $orders,
            'vendors' => Vendor::orderBy('name')->get(),
            'canViewHarga' => auth()->user()->hasPermissionTo('view-harga'),
            'canPay' => auth()->user()->hasPermissionTo('manage-vendor-payments'),
            'cashBankAccounts' => CashBankAccount::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function openPay(int $id): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-vendor-payments'), 403);

        $this->payingId = $id;
        $this->cashBankAccountId = '';
        $this->paymentReference = '';
        $this->resetErrorBag();
        $this->showPayModal = true;
    }

    public function savePay(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-vendor-payments'), 403);

        $validated = $this->validate([
            'cashBankAccountId' => ['required', Rule::exists('cash_bank_accounts', 'id')],
            'paymentReference' => ['nullable', 'string', 'max:255'],
        ], [], [
            'cashBankAccountId' => 'Akun Kas/Bank',
        ]);

        PurchaseOrder::findOrFail($this->payingId)->payVendor(
            auth()->user(),
            CashBankAccount::findOrFail($validated['cashBankAccountId']),
            $this->paymentReference ?: null
        );

        $this->showPayModal = false;
        session()->flash('success', 'Purchase Order ditandai lunas dibayar.');
    }
}
