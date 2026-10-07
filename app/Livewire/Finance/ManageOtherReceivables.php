<?php

namespace App\Livewire\Finance;

use App\Models\CashBankAccount;
use App\Models\OtherReceivable;
use App\Models\Project;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Piutang Lain-lain -- bagian "AR murni" dari Finance & Accounting (SRS
 * 4.14, lanjutan GL/Chart of Account). Khusus Administrator
 * (`manage-other-receivables`), konsisten dengan pola Cash & Bank/
 * Payroll/GL -- transaksi finansial agregat perusahaan, bukan per-record
 * seperti Invoice/Kasbon individual.
 */
#[Layout('layouts.app')]
class ManageOtherReceivables extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    // Form "Ajukan"
    public bool $showCreateModal = false;

    public string $debtorType = 'karyawan';

    public string $debtorName = '';

    public string $userId = '';

    public string $projectId = '';

    public string $description = '';

    public string $amount = '';

    public string $dueDate = '';

    // Modal tolak
    public bool $showRejectModal = false;

    public ?int $rejectingId = null;

    public string $rejectionReason = '';

    // Modal diberikan
    public bool $showGiveModal = false;

    public ?int $givingId = null;

    public string $giveCashBankAccountId = '';

    // Modal catat pembayaran
    public bool $showPaymentModal = false;

    public ?int $payingId = null;

    public string $paymentCashBankAccountId = '';

    public string $paymentAmount = '';

    public string $paymentDate = '';

    public string $paymentNotes = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-other-receivables'), 403);
    }

    public function render()
    {
        $receivables = OtherReceivable::with(['debtor', 'project', 'requester', 'payments'])
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(10);

        return view('livewire.finance.manage-other-receivables', [
            'receivables' => $receivables,
            'users' => User::orderBy('name')->get(),
            'projects' => Project::orderBy('name')->get(),
            'cashBankAccounts' => CashBankAccount::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function openCreate(): void
    {
        $this->reset(['debtorType', 'debtorName', 'userId', 'projectId', 'description', 'amount', 'dueDate']);
        $this->debtorType = 'karyawan';
        $this->resetErrorBag();
        $this->showCreateModal = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'debtorType' => ['required', Rule::in(array_keys(OtherReceivable::DEBTOR_TYPES))],
            'debtorName' => ['required', 'string', 'max:255'],
            'userId' => ['nullable', Rule::exists('users', 'id')],
            'projectId' => ['nullable', Rule::exists('projects', 'id')],
            'description' => ['required', 'string', 'max:1000'],
            'amount' => ['required', 'numeric', 'min:1'],
            'dueDate' => ['nullable', 'date'],
        ], [], [
            'debtorType' => 'Jenis debitur',
            'debtorName' => 'Nama debitur',
            'description' => 'Keperluan',
            'amount' => 'Jumlah',
            'dueDate' => 'Jatuh tempo',
        ]);

        OtherReceivable::create([
            'code' => OtherReceivable::generateCode(),
            'debtor_type' => $validated['debtorType'],
            'debtor_name' => $validated['debtorName'],
            'user_id' => $validated['userId'] ?: null,
            'project_id' => $validated['projectId'] ?: null,
            'description' => $validated['description'],
            'amount' => $validated['amount'],
            'due_date' => $validated['dueDate'] ?: null,
            'status' => 'diajukan',
            'requested_by' => auth()->id(),
        ]);

        $this->showCreateModal = false;
        session()->flash('success', 'Pengajuan piutang lain-lain tersimpan, menunggu approval.');
    }

    public function approve(int $id): void
    {
        OtherReceivable::findOrFail($id)->approve(auth()->user());
        session()->flash('success', 'Pengajuan disetujui.');
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
        OtherReceivable::findOrFail($this->rejectingId)->reject(auth()->user(), $this->rejectionReason);
        $this->showRejectModal = false;
        session()->flash('success', 'Pengajuan ditolak.');
    }

    public function cancel(int $id): void
    {
        OtherReceivable::findOrFail($id)->cancel(auth()->user());
        session()->flash('success', 'Pengajuan dibatalkan.');
    }

    public function openGive(int $id): void
    {
        $this->givingId = $id;
        $this->giveCashBankAccountId = '';
        $this->resetErrorBag();
        $this->showGiveModal = true;
    }

    public function saveGive(): void
    {
        $validated = $this->validate([
            'giveCashBankAccountId' => ['required', Rule::exists('cash_bank_accounts', 'id')],
        ], [], [
            'giveCashBankAccountId' => 'Akun Kas/Bank',
        ]);

        OtherReceivable::findOrFail($this->givingId)->give(auth()->user(), CashBankAccount::findOrFail($validated['giveCashBankAccountId']));

        $this->showGiveModal = false;
        session()->flash('success', 'Piutang ditandai sudah diberikan.');
    }

    public function openPayment(int $id): void
    {
        $this->payingId = $id;
        $this->paymentCashBankAccountId = '';
        $this->paymentAmount = '';
        $this->paymentDate = now()->format('Y-m-d');
        $this->paymentNotes = '';
        $this->resetErrorBag();
        $this->showPaymentModal = true;
    }

    public function savePayment(): void
    {
        $receivable = OtherReceivable::findOrFail($this->payingId);

        $validated = $this->validate([
            'paymentCashBankAccountId' => ['required', Rule::exists('cash_bank_accounts', 'id')],
            'paymentAmount' => ['required', 'numeric', 'min:1'],
            'paymentDate' => ['required', 'date'],
            'paymentNotes' => ['nullable', 'string', 'max:500'],
        ], [], [
            'paymentCashBankAccountId' => 'Akun Kas/Bank',
            'paymentAmount' => 'Jumlah pembayaran',
            'paymentDate' => 'Tanggal',
        ]);

        if ((float) $validated['paymentAmount'] > $receivable->outstanding + 0.005) {
            $this->addError('paymentAmount', 'Jumlah pembayaran melebihi sisa piutang (Rp '.number_format($receivable->outstanding, 0, ',', '.').').');

            return;
        }

        $receivable->recordPayment(
            auth()->user(),
            CashBankAccount::findOrFail($validated['paymentCashBankAccountId']),
            (float) $validated['paymentAmount'],
            $validated['paymentDate'],
            $validated['paymentNotes'] ?: null
        );

        $this->showPaymentModal = false;
        session()->flash('success', 'Pembayaran tercatat.');
    }
}
