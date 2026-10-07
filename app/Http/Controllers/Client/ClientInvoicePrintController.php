<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Project;
use App\Support\NumberToWords;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Cetak Invoice dari Client Portal (SRS 4.3). Dipisah dari
 * InvoicePrintController (guard 'web') supaya tidak perlu menyentuh
 * controller internal yang sudah berjalan -- hanya aturan aksesnya yang
 * berbeda (scoped ke customer pemilik akun, bukan permission karyawan).
 * View cetak yang dipakai tetap sama (invoices.invoice-print).
 */
class ClientInvoicePrintController extends Controller
{
    public function __invoke(Request $request, Invoice $invoice): View
    {
        $client = Auth::guard('client')->user();

        $project = Project::query()->find($invoice->project_id);
        abort_unless($project && $project->customer?->id === $client->customer_id, 403);

        $invoice->load(
            'project.unit.region',
            'project.directContract.customer',
            'project.customerPurchaseOrder.customerQuotation.releaseOrder.contract.customer',
            'supportingDocument',
            'creator'
        );

        return view('invoices.invoice-print', [
            'invoice' => $invoice,
            'customer' => $invoice->project->customer,
            'totalWords' => NumberToWords::rupiah((float) $invoice->total_amount),
            'signatoryName' => config('company.signatory_name') ?: null,
            'signatoryTitle' => config('company.signatory_title'),
        ]);
    }
}
