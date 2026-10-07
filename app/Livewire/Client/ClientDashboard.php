<?php

namespace App\Livewire\Client;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Halaman utama Client Portal (SRS 4.3) -- daftar seluruh proyek milik
 * customer pemilik akun yang login, dengan ringkasan status & progres.
 * Tidak menampilkan data finansial internal (budget, biaya) -- hanya
 * status, progres, dan jadwal yang relevan untuk klien.
 */
#[Layout('layouts.client')]
class ClientDashboard extends Component
{
    public function render()
    {
        $client = Auth::guard('client')->user();

        $projects = Project::query()
            ->forCustomer($client->customer_id)
            ->with('unit.region', 'pic')
            ->orderByDesc('start_date')
            ->get();

        return view('livewire.client.client-dashboard', [
            'client' => $client,
            'projects' => $projects,
        ]);
    }
}
