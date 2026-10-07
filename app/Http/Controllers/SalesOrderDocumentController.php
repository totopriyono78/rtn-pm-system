<?php

namespace App\Http\Controllers;

use App\Models\SalesOrderDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SalesOrderDocumentController extends Controller
{
    /**
     * Unduh (atau tampilkan inline lewat ?inline=1) dokumen teknis Sales
     * Order dari private disk -- pola sama dengan ProjectDocumentController.
     */
    public function __invoke(Request $request, SalesOrderDocument $salesOrderDocument): StreamedResponse
    {
        abort_unless($request->user()->hasPermissionTo('manage-sales-orders'), 403);
        abort_unless(Storage::disk('local')->exists($salesOrderDocument->disk_path), 404);

        if ($request->boolean('inline')) {
            return Storage::disk('local')->response($salesOrderDocument->disk_path, $salesOrderDocument->original_name);
        }

        return Storage::disk('local')->download($salesOrderDocument->disk_path, $salesOrderDocument->original_name);
    }
}
