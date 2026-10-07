<?php

namespace App\Livewire\Teknisi;

use App\Models\CashAdvance;
use App\Models\CashAdvanceExpense;
use App\Models\Project;
use App\Models\ProjectBudgetLine;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

/**
 * Kasbon & Expense teknisi (SRS 4.16) -- sisi pemohon. Teknisi/Lead
 * Technician mengajukan kasbon, lalu setelah dicairkan mengisi rincian
 * pengeluaran (pertanggungjawaban) satu per satu sampai diajukan untuk
 * diverifikasi. Lihat Operasional\ManageCashAdvances untuk sisi approval.
 */
#[Layout('layouts.app')]
class CashAdvances extends Component
{
    use WithFileUploads, WithPagination;

    public bool $showModal = false;

    public string $projectId = '';

    public string $amountRequested = '';

    public string $purpose = '';

    public bool $showExpenseModal = false;

    public ?int $expenseCashAdvanceId = null;

    public string $expenseCategory = '';

    public string $expenseDescription = '';

    public string $expenseAmount = '';

    public string $expenseDate = '';

    /** @var mixed */
    public $expenseReceipt = null;

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('request-cash-advance'), 403);
    }

    public function render()
    {
        $user = auth()->user();

        $cashAdvances = CashAdvance::where('requested_by', $user->id)
            ->with(['project', 'approver', 'expenses'])
            ->latest()
            ->paginate(10);

        return view('livewire.teknisi.cash-advances', [
            'cashAdvances' => $cashAdvances,
            'projects' => Project::query()->visibleTo($user)->orderBy('name')->get(),
        ]);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate([
            'projectId' => ['nullable', Rule::exists('projects', 'id')],
            'amountRequested' => ['required', 'numeric', 'min:1000'],
            'purpose' => ['required', 'string', 'max:1000'],
        ]);

        CashAdvance::create([
            'project_id' => $this->projectId ?: null,
            'requested_by' => Auth::id(),
            'amount_requested' => $this->amountRequested,
            'purpose' => $this->purpose,
            'status' => 'diajukan',
        ]);

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Kasbon diajukan, menunggu approval.');
    }

    public function cancel(int $id): void
    {
        $cashAdvance = CashAdvance::where('requested_by', Auth::id())->findOrFail($id);
        $cashAdvance->cancel();
        session()->flash('success', 'Pengajuan kasbon dibatalkan.');
    }

    public function openExpenseModal(int $cashAdvanceId): void
    {
        $cashAdvance = CashAdvance::where('requested_by', Auth::id())->findOrFail($cashAdvanceId);
        abort_unless($cashAdvance->status === 'dicairkan', 400, 'Kasbon harus sudah dicairkan sebelum menambah rincian pengeluaran.');

        $this->expenseCashAdvanceId = $cashAdvance->id;
        $this->expenseCategory = '';
        $this->expenseDescription = '';
        $this->expenseAmount = '';
        $this->expenseDate = now()->format('Y-m-d');
        $this->expenseReceipt = null;
        $this->resetErrorBag();
        $this->showExpenseModal = true;
    }

    public function saveExpense(): void
    {
        $maxKb = ((int) env('MAX_UPLOAD_SIZE_MB', 50)) * 1024;

        $this->validate([
            'expenseCategory' => ['required', Rule::in(array_keys(ProjectBudgetLine::CATEGORIES))],
            'expenseDescription' => ['required', 'string', 'max:255'],
            'expenseAmount' => ['required', 'numeric', 'min:0.01'],
            'expenseDate' => ['required', 'date'],
            'expenseReceipt' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:'.$maxKb],
        ]);

        $cashAdvance = CashAdvance::where('requested_by', Auth::id())->findOrFail($this->expenseCashAdvanceId);
        abort_unless($cashAdvance->status === 'dicairkan', 400, 'Kasbon harus sudah dicairkan sebelum menambah rincian pengeluaran.');

        $receiptData = [];
        if ($this->expenseReceipt) {
            $path = $this->expenseReceipt->store("cash-advances/{$cashAdvance->id}/bukti", 'local');
            $receiptData = [
                'receipt_disk_path' => $path,
                'receipt_original_name' => $this->expenseReceipt->getClientOriginalName(),
                'receipt_mime_type' => $this->expenseReceipt->getMimeType(),
                'receipt_size_bytes' => $this->expenseReceipt->getSize(),
            ];
        }

        CashAdvanceExpense::create(array_merge([
            'cash_advance_id' => $cashAdvance->id,
            'category' => $this->expenseCategory,
            'description' => $this->expenseDescription,
            'amount' => $this->expenseAmount,
            'expense_date' => $this->expenseDate,
        ], $receiptData));

        $this->showExpenseModal = false;
        session()->flash('success', 'Rincian pengeluaran tersimpan.');
    }

    public function submitForReview(int $id): void
    {
        $cashAdvance = CashAdvance::where('requested_by', Auth::id())->findOrFail($id);
        $cashAdvance->submitForReview();
        session()->flash('success', 'Pertanggungjawaban kasbon diajukan, menunggu verifikasi.');
    }

    public function resetForm(): void
    {
        $this->reset(['projectId', 'amountRequested', 'purpose']);
        $this->resetErrorBag();
    }
}
