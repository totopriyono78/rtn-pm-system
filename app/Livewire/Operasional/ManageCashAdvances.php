<?php

namespace App\Livewire\Operasional;

use App\Models\CashAdvance;
use App\Models\CashBankAccount;
use App\Models\Project;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Kasbon & Expense teknisi (SRS 4.16) -- sisi approval/pencairan/verifikasi
 * oleh Project Manager/Administrator. Tidak ada integrasi Cash & Bank/GL
 * (modul 4.14 belum ada) -- "dicairkan"/"ditutup" di sini murni status
 * administratif, uang fisik tetap ditangani manual di luar sistem.
 */
#[Layout('layouts.app')]
class ManageCashAdvances extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public string $projectFilter = '';

    public bool $showRejectModal = false;

    public ?int $rejectingId = null;

    public string $rejectionReason = '';

    public ?int $reviewingId = null;

    public bool $showDisburseModal = false;

    public ?int $disbursingId = null;

    public string $cashBankAccountId = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-cash-advances'), 403);
    }

    public function render()
    {
        $user = auth()->user();

        $cashAdvances = CashAdvance::query()
            ->where(fn ($q) => $q->whereNull('project_id')->orWhereHas('project', fn ($p) => $p->visibleTo($user)))
            ->with(['project', 'requester', 'approver', 'expenses'])
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->projectFilter, fn ($q) => $q->where('project_id', $this->projectFilter))
            ->latest()
            ->paginate(10);

        $reviewing = $this->reviewingId ? CashAdvance::with('expenses')->find($this->reviewingId) : null;

        return view('livewire.operasional.manage-cash-advances', [
            'cashAdvances' => $cashAdvances,
            'projects' => Project::query()->visibleTo($user)->orderBy('name')->get(),
            'reviewing' => $reviewing,
            'cashBankAccounts' => CashBankAccount::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function approve(int $id): void
    {
        CashAdvance::findOrFail($id)->approve(auth()->user());
        session()->flash('success', 'Kasbon disetujui.');
    }

    public function openReject(int $id): void
    {
        $this->rejectingId = $id;
        $this->rejectionReason = '';
        $this->resetErrorBag();
        $this->showRejectModal = true;
    }

    public function saveReject(): void
    {
        $this->validate(['rejectionReason' => ['required', 'string', 'max:500']]);
        CashAdvance::findOrFail($this->rejectingId)->reject(auth()->user(), $this->rejectionReason);
        $this->showRejectModal = false;
        session()->flash('success', 'Kasbon ditolak.');
    }

    public function openDisburse(int $id): void
    {
        $this->disbursingId = $id;
        $this->cashBankAccountId = '';
        $this->resetErrorBag();
        $this->showDisburseModal = true;
    }

    public function saveDisburse(): void
    {
        $validated = $this->validate([
            'cashBankAccountId' => ['required', Rule::exists('cash_bank_accounts', 'id')],
        ], [], [
            'cashBankAccountId' => 'Akun Kas/Bank',
        ]);

        CashAdvance::findOrFail($this->disbursingId)->disburse(auth()->user(), CashBankAccount::findOrFail($validated['cashBankAccountId']));

        $this->showDisburseModal = false;
        session()->flash('success', 'Kasbon ditandai sudah dicairkan.');
    }

    public function openReview(int $id): void
    {
        $this->reviewingId = $id;
    }

    public function closeReview(): void
    {
        $this->reviewingId = null;
    }

    public function closeCashAdvance(int $id): void
    {
        CashAdvance::findOrFail($id)->close(auth()->user());
        $this->reviewingId = null;
        session()->flash('success', 'Kasbon ditutup.');
    }
}
