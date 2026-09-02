<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Support\NumberToWords;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PurchaseOrderPrintController extends Controller
{
    /**
     * Tampilkan Purchase Order dalam format cetak resmi yang ditujukan ke
     * vendor (dibuka di tab baru, dicetak/disimpan sebagai PDF lewat fitur
     * print bawaan browser). Akses mengikuti aturan yang sama dengan halaman
     * purchasing lain: butuh permission view-purchasing, kolom harga hanya
     * tampil bila user memiliki permission view-harga.
     */
    public function __invoke(Request $request, PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load('project.unit.region', 'vendor', 'items.item', 'creator', 'approver');

        $canViewHarga = $request->user()->hasPermissionTo('view-harga');

        $signatoryName = $purchaseOrder->approver?->name;
        if (! $signatoryName) {
            $signatoryName = config('company.signatory_name') ?: null;
        }

        return view('purchasing.purchase-order-print', [
            'po' => $purchaseOrder,
            'canViewHarga' => $canViewHarga,
            'totalWords' => $canViewHarga ? NumberToWords::rupiah((float) $purchaseOrder->total) : null,
            'signatoryName' => $signatoryName,
            'signatoryTitle' => config('company.signatory_title'),
        ]);
    }
}
