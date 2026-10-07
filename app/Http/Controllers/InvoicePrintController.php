<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Support\NumberToWords;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class InvoicePrintController extends Controller
{
    /**
     * Tampilkan Invoice dalam format cetak resmi yang ditujukan ke customer
     * (dibuka di tab baru, dicetak/disimpan sebagai PDF lewat fitur print
     * bawaan browser). Akses mengikuti aturan yang sama dengan halaman
     * invoice lain: butuh permission manage-invoices atau view-contract-value,
     * dan proyek harus berada dalam scope region/unit user (visibleTo).
     */
    public function __invoke(Request $request, Invoice $invoice): View
    {
        abort_unless(
            \App\Models\Project::query()->visibleTo($request->user())->whereKey($invoice->project_id)->exists(),
            403
        );

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
