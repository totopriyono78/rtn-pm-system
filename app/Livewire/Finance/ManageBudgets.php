<?php

namespace App\Livewire\Finance;

use App\Models\Budget;
use App\Models\ChartOfAccount;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Budgeting formal -- bagian terakhir Finance & Accounting (SRS 4.14).
 * Administrator (`manage-budgets`) membuat/mengubah draft; Direktur
 * (`approve-budgets`) menyetujui. Mengelola dan menyetujui sengaja
 * dipisah (beda orang/peran), berbeda dari pola Cash & Bank/GL yang
 * Administrator-only end-to-end.
 */
#[Layout('layouts.app')]
class ManageBudgets extends Component
{
    use WithPagination;

    public ?int $detailId = null;

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $periodYear = '';

    public string $notes = '';

    public array $lines = [];

    public function mount(): void
    {
        abort_unless(auth()->user()->hasAnyPermission(['manage-budgets', 'approve-budgets']), 403);
    }

    public function render()
    {
        $budgets = Budget::with('creator', 'approver')
            ->withSum('lines', 'planned_amount')
            ->latest('period_year')
            ->latest('id')
            ->paginate(10);

        $detail = $this->detailId ? Budget::with('lines.chartOfAccount')->find($this->detailId) : null;

        return view('livewire.finance.manage-budgets', [
            'budgets' => $budgets,
            'detail' => $detail,
            'accounts' => ChartOfAccount::where('is_active', true)->orderBy('code')->get(),
            'canManage' => auth()->user()->hasPermissionTo('manage-budgets'),
            'canApprove' => auth()->user()->hasPermissionTo('approve-budgets'),
        ]);
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-budgets'), 403);

        $this->resetForm();
        $this->periodYear = now()->format('Y');
        $this->lines = [
            ['chart_of_account_id' => '', 'planned_amount' => '', 'notes' => ''],
        ];
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-budgets'), 403);

        $budget = Budget::with('lines')->findOrFail($id);
        abort_unless($budget->status === 'draft', 400, 'Hanya anggaran berstatus draft yang dapat diubah.');

        $this->editingId = $budget->id;
        $this->periodYear = (string) $budget->period_year;
        $this->notes = (string) $budget->notes;
        $this->lines = $budget->lines->map(fn ($line) => [
            'chart_of_account_id' => (string) $line->chart_of_account_id,
            'planned_amount' => (string) $line->planned_amount,
            'notes' => (string) $line->notes,
        ])->values()->all();
        $this->showModal = true;
    }

    public function addLine(): void
    {
        $this->lines[] = ['chart_of_account_id' => '', 'planned_amount' => '', 'notes' => ''];
    }

    public function removeLine(int $index): void
    {
        if (count($this->lines) <= 1) {
            return;
        }
        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    #[Computed]
    public function totalPlanned(): float
    {
        return round(array_sum(array_map(fn ($l) => (float) ($l['planned_amount'] ?? 0), $this->lines)), 2);
    }

    public function save(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-budgets'), 403);

        $this->validate([
            'periodYear' => ['required', 'integer', 'min:2020', 'max:2100'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.chart_of_account_id' => ['required', Rule::exists('chart_of_accounts', 'id')],
            'lines.*.planned_amount' => ['required', 'numeric', 'min:0.01'],
        ], [], [
            'periodYear' => 'Tahun Anggaran',
        ]);

        $accountIds = array_column($this->lines, 'chart_of_account_id');
        if (count($accountIds) !== count(array_unique($accountIds))) {
            $this->addError('lines', 'Satu akun tidak boleh muncul lebih dari sekali dalam satu anggaran.');

            return;
        }

        $budget = Budget::updateOrCreate(['id' => $this->editingId], [
            'code' => $this->editingId ? Budget::findOrFail($this->editingId)->code : Budget::generateCode(),
            'period_year' => $this->periodYear,
            'status' => 'draft',
            'notes' => $this->notes ?: null,
            'created_by' => $this->editingId ? Budget::findOrFail($this->editingId)->created_by : auth()->id(),
        ]);

        $budget->replaceLines($this->lines);

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Anggaran tersimpan sebagai draft.');
    }

    public function approveBudget(int $id): void
    {
        abort_unless(auth()->user()->hasPermissionTo('approve-budgets'), 403);

        Budget::findOrFail($id)->load('lines')->approve(auth()->user());
        session()->flash('success', 'Anggaran disetujui.');
    }

    public function cancelBudget(int $id): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-budgets'), 403);

        Budget::findOrFail($id)->cancel(auth()->user());
        session()->flash('success', 'Anggaran dibatalkan.');
    }

    public function openDetail(int $id): void
    {
        $this->detailId = $id;
    }

    public function closeDetail(): void
    {
        $this->detailId = null;
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'periodYear', 'notes', 'lines']);
        $this->resetErrorBag();
    }
}
