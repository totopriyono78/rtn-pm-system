<?php

namespace App\Livewire\Purchasing;

use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ManagePurchaseOrders extends Component
{
    use WithPagination;

    public string $vendorFilter = '';

    public string $statusFilter = '';

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
        ]);
    }
}
