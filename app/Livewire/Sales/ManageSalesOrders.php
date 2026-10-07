<?php

namespace App\Livewire\Sales;

use App\Models\Customer;
use App\Models\Prospect;
use App\Models\SalesOrder;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ManageSalesOrders extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public bool $showModal = false;

    public string $prospectId = '';

    public string $customerId = '';

    public string $soDate = '';

    public string $description = '';

    public string $notes = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-sales-orders'), 403);
    }

    public function render()
    {
        $salesOrders = SalesOrder::with(['customer', 'prospect'])
            ->withCount('items')
            ->when($this->search, fn ($q) => $q->where(fn ($q2) => $q2->where('so_number', 'like', "%{$this->search}%")
                ->orWhereHas('customer', fn ($q3) => $q3->where('name', 'like', "%{$this->search}%"))))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(10);

        return view('livewire.sales.manage-sales-orders', [
            'salesOrders' => $salesOrders,
            'customers' => Customer::where('is_active', true)->orderBy('name')->get(),
            'wonProspects' => Prospect::where('status', 'won')->whereNotNull('customer_id')->orderBy('company_name')->get(),
        ]);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->soDate = now()->format('Y-m-d');
        $this->showModal = true;
    }

    /**
     * Livewire lifecycle hook -- otomatis terpanggil saat $prospectId
     * berubah (wire:model.live di view), untuk prefill Customer & deskripsi
     * dari Prospect yang dipilih.
     */
    public function updatedProspectId(string $value): void
    {
        if (! $value) {
            return;
        }

        $prospect = Prospect::findOrFail($value);
        $this->customerId = (string) $prospect->customer_id;
        $this->description = 'Pengadaan pompa baru untuk '.$prospect->company_name;
    }

    public function save(): void
    {
        $this->validate([
            'prospectId' => ['nullable', Rule::exists('prospects', 'id')],
            'customerId' => ['required', Rule::exists('customers', 'id')],
            'soDate' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $salesOrder = SalesOrder::create([
            'so_number' => SalesOrder::generateNumberSuggestion(),
            'prospect_id' => $this->prospectId ?: null,
            'customer_id' => $this->customerId,
            'so_date' => $this->soDate,
            'description' => $this->description ?: null,
            'total_value' => 0,
            'status' => 'draft',
            'notes' => $this->notes ?: null,
            'created_by' => auth()->id(),
        ]);

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Sales Order "'.$salesOrder->so_number.'" dibuat. Lanjutkan dengan menambah item pompa di halaman detail.');
        $this->redirect(route('sales.orders.show', $salesOrder), navigate: false);
    }

    public function resetForm(): void
    {
        $this->reset(['prospectId', 'customerId', 'description', 'notes']);
        $this->resetErrorBag();
    }
}
