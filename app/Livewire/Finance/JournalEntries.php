<?php

namespace App\Livewire\Finance;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Jurnal Umum / Entry Voucher -- bagian "General Ledger/Chart of Account"
 * dari Finance & Accounting (SRS 4.14). Dicatat manual (bukan
 * auto-posting dari modul lain, keputusan eksplisit user 2026-10-06).
 * Mengelola (create/edit draft/posting/batalkan) butuh `manage-general-
 * ledger` (Administrator); melihat saja cukup `view-financial-reports`
 * (Administrator + Direktur).
 */
#[Layout('layouts.app')]
class JournalEntries extends Component
{
    use WithPagination;

    public string $statusFilter = '';
    public string $sourceFilter = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $entryDate = '';

    public string $reference = '';

    public string $description = '';

    public array $lines = [];

    public function mount(): void
    {
        abort_unless(auth()->user()->hasAnyPermission(['manage-general-ledger', 'view-financial-reports']), 403);
    }

    public function render()
    {
        $entries = JournalEntry::with('creator', 'poster', 'invoice', 'purchaseOrder.vendor', 'cashAdvance.requester', 'payrollRun')
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->sourceFilter, fn ($q) => $q->where('source', $this->sourceFilter))
            ->latest('entry_date')
            ->latest('id')
            ->paginate(10);

        return view('livewire.finance.journal-entries', [
            'entries' => $entries,
            'accounts' => ChartOfAccount::where('is_active', true)->orderBy('code')->get(),
            'canManage' => auth()->user()->hasPermissionTo('manage-general-ledger'),
        ]);
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-general-ledger'), 403);

        $this->resetForm();
        $this->entryDate = now()->format('Y-m-d');
        $this->lines = [
            ['chart_of_account_id' => '', 'debit' => '', 'credit' => '', 'description' => ''],
            ['chart_of_account_id' => '', 'debit' => '', 'credit' => '', 'description' => ''],
        ];
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-general-ledger'), 403);

        $entry = JournalEntry::with('lines')->findOrFail($id);
        abort_unless($entry->status === 'draft', 400, 'Hanya jurnal berstatus draft yang dapat diubah.');

        $this->editingId = $entry->id;
        $this->entryDate = $entry->entry_date->format('Y-m-d');
        $this->reference = (string) $entry->reference;
        $this->description = (string) $entry->description;
        $this->lines = $entry->lines->map(fn ($line) => [
            'chart_of_account_id' => (string) $line->chart_of_account_id,
            'debit' => $line->debit > 0 ? (string) $line->debit : '',
            'credit' => $line->credit > 0 ? (string) $line->credit : '',
            'description' => (string) $line->description,
        ])->values()->all();
        $this->showModal = true;
    }

    public function addLine(): void
    {
        $this->lines[] = ['chart_of_account_id' => '', 'debit' => '', 'credit' => '', 'description' => ''];
    }

    public function removeLine(int $index): void
    {
        if (count($this->lines) <= 2) {
            return;
        }
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    #[Computed]
    public function totalDebit(): float
    {
        return round(array_sum(array_map(fn ($l) => (float) ($l['debit'] ?? 0), $this->lines)), 2);
    }

    #[Computed]
    public function totalCredit(): float
    {
        return round(array_sum(array_map(fn ($l) => (float) ($l['credit'] ?? 0), $this->lines)), 2);
    }

    public function save(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-general-ledger'), 403);

        $this->validate([
            'entryDate' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.chart_of_account_id' => ['required', Rule::exists('chart_of_accounts', 'id')],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
        ], [], [
            'entryDate' => 'Tanggal',
        ]);

        foreach ($this->lines as $i => $line) {
            $debit = (float) ($line['debit'] ?? 0);
            $credit = (float) ($line['credit'] ?? 0);
            if ($debit > 0 && $credit > 0) {
                $this->addError("lines.{$i}.debit", 'Satu baris tidak boleh mengisi debit dan kredit sekaligus.');

                return;
            }
            if ($debit <= 0 && $credit <= 0) {
                $this->addError("lines.{$i}.debit", 'Isi salah satu, debit atau kredit, lebih dari 0.');

                return;
            }
        }

        if (abs($this->totalDebit - $this->totalCredit) >= 0.005 || $this->totalDebit <= 0) {
            $this->addError('lines', 'Total debit dan kredit harus sama (balance) dan lebih dari 0. Debit: '.number_format($this->totalDebit, 0, ',', '.').' -- Kredit: '.number_format($this->totalCredit, 0, ',', '.'));

            return;
        }

        $entry = JournalEntry::updateOrCreate(['id' => $this->editingId], [
            'entry_number' => $this->editingId ? JournalEntry::findOrFail($this->editingId)->entry_number : JournalEntry::generateNumberSuggestion(),
            'entry_date' => $this->entryDate,
            'reference' => $this->reference ?: null,
            'description' => $this->description ?: null,
            'status' => 'draft',
            'created_by' => $this->editingId ? JournalEntry::findOrFail($this->editingId)->created_by : auth()->id(),
        ]);

        $entry->replaceLines($this->lines);

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Jurnal tersimpan sebagai draft.');
    }

    public function postEntry(int $id): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-general-ledger'), 403);

        JournalEntry::findOrFail($id)->load('lines')->post(auth()->user());
        session()->flash('success', 'Jurnal berhasil di-posting.');
    }

    public function cancelEntry(int $id): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-general-ledger'), 403);

        JournalEntry::findOrFail($id)->cancel(auth()->user());
        session()->flash('success', 'Jurnal dibatalkan.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'entryDate', 'reference', 'description', 'lines']);
        $this->resetErrorBag();
    }
}
