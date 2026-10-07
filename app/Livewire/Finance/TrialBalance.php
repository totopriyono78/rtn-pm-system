<?php

namespace App\Livewire\Finance;

use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Neraca Saldo (Trial Balance) -- read-only, per tanggal "as of".
 * Menjumlahkan debit/kredit dari jurnal `posted` saja per akun, lalu
 * menampilkan saldo wajar (disesuaikan normal_balance). Total debit =
 * total kredit secara matematis harus selalu sama kalau semua jurnal
 * yang posted memang balance saat dibuat (divalidasi di
 * JournalEntry::post()) -- baris "Selisih" di bawah murni sebagai
 * sanity-check visual, bukan berarti sistem bisa out-of-balance.
 */
#[Layout('layouts.app')]
class TrialBalance extends Component
{
    public string $asOfDate = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->hasAnyPermission(['manage-general-ledger', 'view-financial-reports']), 403);
        $this->asOfDate = now()->format('Y-m-d');
    }

    public function render()
    {
        $accounts = ChartOfAccount::orderBy('code')->get()->map(function (ChartOfAccount $account) {
            $sumDebit = (float) JournalEntryLine::where('chart_of_account_id', $account->id)
                ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->where('entry_date', '<=', $this->asOfDate))
                ->sum('debit');
            $sumCredit = (float) JournalEntryLine::where('chart_of_account_id', $account->id)
                ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->where('entry_date', '<=', $this->asOfDate))
                ->sum('credit');

            $account->display_debit = $account->normal_balance === 'debit' ? round(max($sumDebit - $sumCredit, 0), 2) : 0.0;
            $account->display_credit = $account->normal_balance === 'credit' ? round(max($sumCredit - $sumDebit, 0), 2) : 0.0;
            // Saldo lawan arah (misal akun debit tapi kreditnya lebih besar) tetap ditampilkan di sisi yang sesuai, bukan disembunyikan.
            if ($account->normal_balance === 'debit' && $sumCredit > $sumDebit) {
                $account->display_credit = round($sumCredit - $sumDebit, 2);
                $account->display_debit = 0.0;
            } elseif ($account->normal_balance === 'credit' && $sumDebit > $sumCredit) {
                $account->display_debit = round($sumDebit - $sumCredit, 2);
                $account->display_credit = 0.0;
            }

            return $account;
        })->filter(fn (ChartOfAccount $account) => $account->display_debit > 0 || $account->display_credit > 0 || $account->is_active);

        $totalDebit = round((float) $accounts->sum('display_debit'), 2);
        $totalCredit = round((float) $accounts->sum('display_credit'), 2);

        return view('livewire.finance.trial-balance', [
            'accounts' => $accounts,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
        ]);
    }
}
