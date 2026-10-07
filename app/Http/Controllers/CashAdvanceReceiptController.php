<?php

namespace App\Http\Controllers;

use App\Models\CashAdvanceExpense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CashAdvanceReceiptController extends Controller
{
    /**
     * Unduh bukti/struk pengeluaran kasbon (private disk). Akses: pemilik
     * kasbon (yang mengajukan), atau pemegang manage-cash-advances (PM/
     * Administrator yang memverifikasi pertanggungjawaban).
     */
    public function __invoke(Request $request, CashAdvanceExpense $cashAdvanceExpense): StreamedResponse
    {
        $user = $request->user();
        $cashAdvanceExpense->loadMissing('cashAdvance');
        $cashAdvance = $cashAdvanceExpense->cashAdvance;

        abort_unless($cashAdvanceExpense->receipt_disk_path, 404);

        $isOwner = $cashAdvance->requested_by === $user->id;
        $canManage = $user->hasPermissionTo('manage-cash-advances');

        abort_unless($isOwner || $canManage, 403, 'Anda tidak memiliki akses ke berkas ini.');

        abort_unless(Storage::disk('local')->exists($cashAdvanceExpense->receipt_disk_path), 404);

        return Storage::disk('local')->download($cashAdvanceExpense->receipt_disk_path, $cashAdvanceExpense->receipt_original_name);
    }
}
