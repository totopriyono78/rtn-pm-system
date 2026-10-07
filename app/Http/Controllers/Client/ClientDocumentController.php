<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Unduh/preview dokumen proyek dari Client Portal (SRS 4.3). Dipisah dari
 * ProjectDocumentController (guard 'web') karena aturan aksesnya berbeda
 * total: bukan berdasar region/permission karyawan, tapi berdasar apakah
 * dokumen ini milik proyek customer yang bersangkutan DAN ditandai
 * `is_client_visible` oleh tim internal.
 */
class ClientDocumentController extends Controller
{
    public function __invoke(Request $request, ProjectDocument $projectDocument): StreamedResponse
    {
        $client = Auth::guard('client')->user();

        // Scoping lewat accessor Project::getCustomerAttribute() karena
        // Customer bisa ditarik dari 2 jalur (kontrak spesifik langsung,
        // atau kontrak payung lewat rantai CPO->Quotation->RO->Contract) --
        // query whereHas tunggal tidak cukup untuk menormalkan keduanya.
        $project = Project::query()->find($projectDocument->project_id);
        $belongsToCustomer = $project && $project->customer?->id === $client->customer_id;

        abort_unless($belongsToCustomer, 403, 'Anda tidak memiliki akses ke dokumen ini.');
        abort_unless($projectDocument->is_client_visible, 403, 'Dokumen ini belum dibagikan untuk klien.');
        abort_unless(Storage::disk('local')->exists($projectDocument->disk_path), 404);

        if ($request->boolean('inline')) {
            return Storage::disk('local')->response($projectDocument->disk_path, $projectDocument->original_name);
        }

        return Storage::disk('local')->download($projectDocument->disk_path, $projectDocument->original_name);
    }
}
