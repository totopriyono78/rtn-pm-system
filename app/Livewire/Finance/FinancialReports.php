<?php

namespace App\Livewire\Finance;

use App\Models\CashBankAccount;
use App\Models\CashBankTransaction;
use App\Models\Invoice;
use App\Models\PayrollRun;
use App\Models\PurchaseOrder;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Laporan Keuangan ringkas -- bagian "Reporting" dari Finance & Accounting
 * (SRS 4.14). Murni agregasi read-only dari data yang sudah ada (Cash &
 * Bank, Invoice/AR, Purchase Order/AP, Payroll) per periode bulan --
 * BUKAN laba-rugi/neraca formal (tidak ada General Ledger/Chart of
 * Account), tapi cukup untuk gambaran arus kas dan posisi piutang/hutang
 * bulanan tanpa perlu membangun skema akuntansi baru.
 *
 * Permission `view-financial-reports` digrant ke Administrator DAN
 * Direktur (bukan Administrator-only seperti Payroll/Cash & Bank) --
 * keputusan saya sendiri saat membangun: ini laporan read-only (tidak
 * bisa mengubah apa pun), levelnya sama seperti `view-contract-value`
 * yang sudah dipegang Direktur, dan secara alami Direktur sebagai
 * pimpinan perusahaan perlu melihat posisi kas/piutang/hutang tanpa
 * perlu akses kelola Payroll/Cash & Bank itu sendiri.
 */
#[Layout('layouts.app')]
class FinancialReports extends Component
{
    public const MONTHS = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    public string $periodMonth = '';

    public string $periodYear = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('view-financial-reports'), 403);
        $this->periodMonth = (string) now()->month;
        $this->periodYear = (string) now()->year;
    }

    public function render()
    {
        $start = Carbon::create((int) $this->periodYear, (int) $this->periodMonth, 1)->startOfMonth();
        $end = $start->copy()->endOfMonth();

        // ===== Arus Kas (Cash & Bank) periode terpilih =====
        $openingBalance = round(
            (float) CashBankAccount::sum('opening_balance')
            + (float) CashBankTransaction::where('type', 'in')->where('transaction_date', '<', $start->toDateString())->sum('amount')
            - (float) CashBankTransaction::where('type', 'out')->where('transaction_date', '<', $start->toDateString())->sum('amount'),
            2
        );

        $periodTransactions = CashBankTransaction::whereBetween('transaction_date', [$start->toDateString(), $end->toDateString()])->get();
        $totalIn = round((float) $periodTransactions->where('type', 'in')->sum('amount'), 2);
        $totalOut = round((float) $periodTransactions->where('type', 'out')->sum('amount'), 2);
        $closingBalance = round($openingBalance + $totalIn - $totalOut, 2);

        $inByCategory = $periodTransactions->where('type', 'in')->groupBy('category')
            ->map(fn ($rows) => round((float) $rows->sum('amount'), 2));
        $outByCategory = $periodTransactions->where('type', 'out')->groupBy('category')
            ->map(fn ($rows) => round((float) $rows->sum('amount'), 2));

        // ===== AR -- Piutang (Invoice) =====
        $totalInvoicedPeriod = round(
            (float) Invoice::whereBetween('invoice_date', [$start->toDateString(), $end->toDateString()])->where('status', '!=', 'cancelled')->sum('total_amount'),
            2
        );
        $totalReceivableNow = round((float) Invoice::where('status', 'sent')->sum('total_amount'), 2);

        // ===== AP -- Hutang Vendor (Purchase Order) =====
        $totalPoPeriod = round(
            (float) PurchaseOrder::whereBetween('created_at', [$start->toDateTimeString(), $end->toDateTimeString()])->where('status', '!=', 'cancelled')->sum('total'),
            2
        );
        $totalPayableNow = round((float) PurchaseOrder::where('status', 'issued')->where('payment_status', 'belum_dibayar')->sum('total'), 2);

        // ===== Payroll periode terpilih (kalau ada) =====
        $payrollRun = PayrollRun::where('period_month', $this->periodMonth)
            ->where('period_year', $this->periodYear)
            ->first();

        return view('livewire.finance.financial-reports', [
            'start' => $start,
            'end' => $end,
            'openingBalance' => $openingBalance,
            'totalIn' => $totalIn,
            'totalOut' => $totalOut,
            'closingBalance' => $closingBalance,
            'inByCategory' => $inByCategory,
            'outByCategory' => $outByCategory,
            'totalInvoicedPeriod' => $totalInvoicedPeriod,
            'totalReceivableNow' => $totalReceivableNow,
            'totalPoPeriod' => $totalPoPeriod,
            'totalPayableNow' => $totalPayableNow,
            'payrollRun' => $payrollRun,
        ]);
    }
}
