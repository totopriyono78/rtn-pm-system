<?php

namespace App\Livewire\Client;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Detail satu proyek di Client Portal (SRS 4.3) -- info ringkas, dokumen
 * yang ditandai is_client_visible, dan invoice terkait proyek ini.
 */
#[Layout('layouts.client')]
class ClientProjectDetail extends Component
{
    public Project $project;

    public function mount(Project $project): void
    {
        $client = Auth::guard('client')->user();

        abort_unless(
            $project->customer?->id === $client->customer_id,
            403,
            'Anda tidak memiliki akses ke proyek ini.'
        );

        $this->project = $project;
    }

    public function render()
    {
        $this->project->load([
            'unit.region',
            'pic',
            'directContract.customer',
            'directContract.sites',
            'customerPurchaseOrder.customerQuotation.releaseOrder.contract.customer',
            'customerPurchaseOrder.customerQuotation.releaseOrder.contract.sites',
            'documents' => fn ($q) => $q->where('is_client_visible', true)->latestVersions()->with('uploader')->orderByDesc('created_at'),
            'invoices' => fn ($q) => $q->orderByDesc('invoice_date'),
        ]);

        return view('livewire.client.client-project-detail');
    }
}
