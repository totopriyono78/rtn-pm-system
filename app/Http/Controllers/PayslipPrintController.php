<?php

namespace App\Http\Controllers;

use App\Models\Payslip;
use App\Support\NumberToWords;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class PayslipPrintController extends Controller
{
    /**
     * Tampilkan slip gaji dalam format cetak (dibuka di tab baru, dicetak/
     * disimpan sebagai PDF lewat fitur print bawaan browser). Akses sama
     * seperti halaman Payroll lainnya -- khusus Administrator.
     */
    public function __invoke(Request $request, Payslip $payslip): View
    {
        abort_unless($request->user()->hasPermissionTo('manage-payroll'), 403);

        $payslip->load('user', 'payrollRun');

        return view('payslips.payslip-print', [
            'payslip' => $payslip,
            'netWords' => NumberToWords::rupiah((float) $payslip->net_amount),
            'signatoryName' => config('company.signatory_name') ?: null,
            'signatoryTitle' => config('company.signatory_title'),
        ]);
    }
}
