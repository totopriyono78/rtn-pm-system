<?php

namespace App\Livewire\Purchasing;

use App\Models\Vendor;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class VendorDetail extends Component
{
    public Vendor $vendor;

    public function mount(Vendor $vendor): void
    {
        $this->vendor = $vendor;
    }

    public function render()
    {
        $this->vendor->load([
            'vendorQuotations.rfq.project',
            'vendorQuotations.items',
            'purchaseOrders' => fn ($q) => $q->latest(),
            'purchaseOrders.project',
        ]);

        $canViewHarga = auth()->user()->hasPermissionTo('view-harga');

        return view('livewire.purchasing.vendor-detail', [
            'canViewHarga' => $canViewHarga,
            'totalPoValue' => $this->vendor->purchaseOrders->where('status', '!=', 'cancelled')->sum('total'),
            'activePoCount' => $this->vendor->purchaseOrders->where('status', 'issued')->count(),
        ]);
    }
}
