<?php

namespace App\Livewire\Contracts;

use App\Models\CashBankAccount;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectDocument;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Invoice & Billing -- SRS 4.15. Dikelola Project Controller (permission
 * manage-invoices) sebagai lanjutan alami dari siklus Divisi 2 yang sudah
 * ada: Pelaksanaan -> BAPP RO -> BAL Bulanan -> Penagihan. Direktur dan
 * pemegang view-contract-value lain bisa melihat daftar ini read-only.
 */
#[Layout('layouts.app')]
class ManageInvoices extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $projectFilter = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $projectId = '';

    public string $projectDocumentId = '';

    public string $invoiceNumber = '';

    public string $invoiceDate = '';

    public string $dueDate = '';

    public string $periodLabel = '';

    public string $subtotal = '';

    public string $taxPercent = '11';

    public string $notes = '';

    public bool $showMarkPaidModal = false;

    public ?int $markingPaidId = null;

    public string $cashBankAccountId = '';

    public string $paymentReference = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->hasAnyPermission(['manage-invoices', 'view-contract-value']), 403);
    }

    public function render()
    {
        $user = auth()->user();

        $invoices = Invoice::query()
            ->whereHas('project', fn ($q) => $q->visibleTo($user))
            ->with(['project.unit.region', 'project.directContract.customer', 'project.customerPurchaseOrder.customerQuotation.releaseOrder.contract.customer', 'supportingDocument', 'creator'])
            ->when($this->search, fn ($q) => $q->where(fn ($q2) => $q2->where('invoice_number', 'like', "%{$this->search}%")
                ->orWhereHas('project', fn ($q3) => $q3->where('name', 'like', "%{$this->search}%"))))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->when($this->projectFilter, fn ($q) => $q->where('project_id', $this->projectFilter))
            ->latest('invoice_date')
            ->paginate(10);

        // Dokumen pendukung (BAPP RO/BAL Bulanan) khusus untuk proyek yang
        // sedang dipilih di form -- supaya select-nya tidak menampilkan
        // dokumen dari proyek lain yang tidak relevan.
        $supportingDocuments = $this->projectId
            ? ProjectDocument::where('project_id', $this->projectId)
                ->whereIn('category', ['bapp_ro', 'bal_bulanan'])
                ->latestVersions()
                ->latest()
                ->get()
            : collect();

        return view('livewire.contracts.manage-invoices', [
            'invoices' => $invoices,
            'projects' => Project::query()->visibleTo($user)->orderBy('name')->get(),
            'supportingDocuments' => $supportingDocuments,
            'canManage' => $user->hasPermissionTo('manage-invoices'),
            'cashBankAccounts' => CashBankAccount::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function updatedProjectId(): void
    {
        $this->projectDocumentId = '';
    }

    public function openCreate(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-invoices'), 403);
        $this->resetForm();
        $this->invoiceNumber = Invoice::generateNumberSuggestion();
        $this->invoiceDate = now()->format('Y-m-d');
        $this->dueDate = now()->addDays(14)->format('Y-m-d');
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-invoices'), 403);

        $invoice = Invoice::findOrFail($id);
        abort_unless($invoice->status === 'draft', 400, 'Hanya invoice berstatus draft yang dapat diubah.');

        $this->editingId = $invoice->id;
        $this->projectId = (string) $invoice->project_id;
        $this->projectDocumentId = (string) $invoice->project_document_id;
        $this->invoiceNumber = $invoice->invoice_number;
        $this->invoiceDate = $invoice->invoice_date->format('Y-m-d');
        $this->dueDate = optional($invoice->due_date)->format('Y-m-d') ?? '';
        $this->periodLabel = (string) $invoice->period_label;
        $this->subtotal = (string) $invoice->subtotal;
        $this->taxPercent = (string) $invoice->tax_percent;
        $this->notes = (string) $invoice->notes;
        $this->showModal = true;
    }

    public function save(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-invoices'), 403);

        $this->validate([
            'projectId' => ['required', Rule::exists('projects', 'id')],
            'projectDocumentId' => ['nullable', Rule::exists('project_documents', 'id')],
            'invoiceNumber' => ['required', 'string', 'max:255', Rule::unique('invoices', 'invoice_number')->ignore($this->editingId)],
            'invoiceDate' => ['required', 'date'],
            'dueDate' => ['nullable', 'date', 'after_or_equal:invoiceDate'],
            'periodLabel' => ['nullable', 'string', 'max:255'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'taxPercent' => ['required', 'numeric', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($this->editingId) {
            $invoice = Invoice::findOrFail($this->editingId);
            abort_unless($invoice->status === 'draft', 400, 'Hanya invoice berstatus draft yang dapat diubah.');
        }

        Invoice::updateOrCreate(['id' => $this->editingId], [
            'project_id' => $this->projectId,
            'project_document_id' => $this->projectDocumentId ?: null,
            'invoice_number' => $this->invoiceNumber,
            'invoice_date' => $this->invoiceDate,
            'due_date' => $this->dueDate ?: null,
            'period_label' => $this->periodLabel ?: null,
            'subtotal' => $this->subtotal,
            'tax_percent' => $this->taxPercent,
            'status' => 'draft',
            'notes' => $this->notes ?: null,
            'created_by' => $this->editingId ? Invoice::findOrFail($this->editingId)->created_by : auth()->id(),
        ]);

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Invoice tersimpan.');
    }

    public function sendInvoice(int $id): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-invoices'), 403);

        Invoice::findOrFail($id)->send(auth()->user());
        session()->flash('success', 'Invoice ditandai terkirim ke customer.');
    }

    public function openMarkPaid(int $id): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-invoices'), 403);

        $this->markingPaidId = $id;
        $this->cashBankAccountId = '';
        $this->paymentReference = '';
        $this->resetErrorBag();
        $this->showMarkPaidModal = true;
    }

    public function saveMarkPaid(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-invoices'), 403);

        $validated = $this->validate([
            'cashBankAccountId' => ['required', Rule::exists('cash_bank_accounts', 'id')],
            'paymentReference' => ['nullable', 'string', 'max:255'],
        ], [], [
            'cashBankAccountId' => 'Akun Kas/Bank',
        ]);

        Invoice::findOrFail($this->markingPaidId)->markPaid(
            auth()->user(),
            CashBankAccount::findOrFail($validated['cashBankAccountId']),
            $this->paymentReference ?: null
        );

        $this->showMarkPaidModal = false;
        session()->flash('success', 'Invoice ditandai lunas.');
    }

    public function cancelInvoice(int $id): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-invoices'), 403);

        Invoice::findOrFail($id)->cancel();
        session()->flash('success', 'Invoice dibatalkan.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'projectId', 'projectDocumentId', 'invoiceNumber', 'invoiceDate', 'dueDate', 'periodLabel', 'subtotal', 'notes']);
        $this->taxPercent = '11';
        $this->resetErrorBag();
    }
}
