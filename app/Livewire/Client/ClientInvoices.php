<?php

namespace App\Livewire\Client;

use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Daftar seluruh Invoice milik customer (lintas proyek) di Client Portal
 * (SRS 4.3) -- hanya invoice berstatus 'sent'/'paid' yang ditampilkan,
 * karena 'draft' masih internal (belum resmi dikirim ke customer).
 */
#[Layout('layouts.client')]
class ClientInvoices extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public function render()
    {
        $client = Auth::guard('client')->user();

        $projectIds = Project::query()->forCustomer($client->customer_id)->pluck('id');

        $invoices = Invoice::query()
            ->whereIn('project_id', $projectIds)
            ->whereIn('status', ['sent', 'paid'])
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->with('project')
            ->orderByDesc('invoice_date')
            ->paginate(10);

        return view('livewire.client.client-invoices', [
            'invoices' => $invoices,
        ]);
    }
}
