<?php

namespace App\Livewire\Finance;

use App\Models\ChartOfAccount;
use App\Models\JournalEntryLine;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Buku Besar (General Ledger) per akun -- read-only, hanya menghitung
 * baris jurnal yang sudah `posted`. Bisa diakses `manage-general-ledger`
 * (Administrator) maupun `view-financial-reports` (Administrator +
 * Direktur) karena sifatnya murni melihat, tidak bisa mengubah apa pun.
 */
#[Layout('layouts.app')]
class GeneralLedgerReport extends Component
{
    public string $chartOfAccountId = '';

    public string $startDate = '';

    public string $endDate = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->hasAnyPermission(['manage-general-ledger', 'view-financial-reports']), 403);
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
    }

    public function render()
    {
        $accounts = ChartOfAccount::orderBy('code')->get();
        $account = $this->chartOfAccountId ? ChartOfAccount::find($this->chartOfAccountId) : null;

        $openingBalance = 0.0;
        $lines = collect();
        $closingBalance = 0.0;

        if ($account) {
            $postedBefore = fn () => JournalEntryLine::where('chart_of_account_id', $account->id)
                ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->where('entry_date', '<', $this->startDate));

            $beforeDebit = (float) $postedBefore()->sum('debit');
            $beforeCredit = (float) $postedBefore()->sum('credit');
            $openingBalance = $account->normal_balance === 'debit'
                ? round($beforeDebit - $beforeCredit, 2)
                : round($beforeCredit - $beforeDebit, 2);

            $lines = JournalEntryLine::where('chart_of_account_id', $account->id)
                ->whereHas('journalEntry', fn ($q) => $q->where('status', 'posted')->whereBetween('entry_date', [$this->startDate, $this->endDate]))
                ->with('journalEntry')
                ->get()
                ->sortBy(fn ($line) => $line->journalEntry->entry_date->format('Y-m-d').'-'.$line->id);

            $running = $openingBalance;
            $lines = $lines->map(function ($line) use (&$running, $account) {
                $delta = $account->normal_balance === 'debit'
                    ? (float) $line->debit - (float) $line->credit
                    : (float) $line->credit - (float) $line->debit;
                $running = round($running + $delta, 2);
                $line->running_balance = $running;

                return $line;
            });

            $closingBalance = $running;
        }

        return view('livewire.finance.general-ledger-report', [
            'accounts' => $accounts,
            'account' => $account,
            'openingBalance' => $openingBalance,
            'lines' => $lines,
            'closingBalance' => $closingBalance,
        ]);
    }
}
