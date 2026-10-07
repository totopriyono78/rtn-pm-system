<?php

namespace App\Http\Controllers;

use App\Models\DeliveryGatepass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DeliveryGatepassFileController extends Controller
{
    /**
     * Unduh scan surat jalan / gatepass (private disk). Hanya untuk user
     * dengan permission manage-delivery-gatepass.
     */
    public function __invoke(Request $request, DeliveryGatepass $deliveryGatepass): StreamedResponse
    {
        abort_unless($request->user()->hasPermissionTo('manage-delivery-gatepass'), 403);
        abort_unless($deliveryGatepass->disk_path && Storage::disk('local')->exists($deliveryGatepass->disk_path), 404);

        return Storage::disk('local')->download($deliveryGatepass->disk_path, $deliveryGatepass->original_name);
    }
}
