<?php

namespace App\Livewire\Contracts;

use App\Models\Contract;
use App\Models\Project;
use App\Models\ReleaseOrder;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ContractDetail extends Component
{
    public Contract $contract;

    // --- buat Release Order (jalur payung) ---
    public bool $showRoModal = false;

    public string $roNumber = '';

    public string $roDate = '';

    public string $roNotes = '';

    // --- buat Project langsung (jalur spesifik) ---
    public bool $showProjectModal = false;

    public string $projectName = '';

    public string $projectPicUserId = '';

    public string $projectStartDate = '';

    public string $projectEndDate = '';

    public function mount(Contract $contract): void
    {
        abort_unless(auth()->user()->hasAnyPermission(['manage-contracts', 'view-contract-value']), 403);
        $this->contract = $contract;
    }

    public function render()
    {
        $this->contract->load(['customer', 'unit', 'sites', 'releaseOrders.quotations', 'directProject']);

        return view('livewire.contracts.contract-detail', [
            'canManage' => auth()->user()->hasPermissionTo('manage-contracts'),
            'pics' => User::role(['Project Manager', 'Administrator'])->orderBy('name')->get(),
        ]);
    }

    // ===== Jalur payung: Release Order =====

    public function openCreateRo(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-contracts'), 403);
        abort_unless($this->contract->isPayung(), 400, 'Release Order hanya berlaku untuk kontrak payung.');

        $this->reset(['roNumber', 'roNotes']);
        $this->roDate = now()->format('Y-m-d');
        $this->showRoModal = true;
    }

    public function saveRo(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-contracts'), 403);

        $this->validate([
            'roNumber' => ['required', 'string', 'max:255'],
            'roDate' => ['required', 'date'],
            'roNotes' => ['nullable', 'string'],
        ]);

        $ro = ReleaseOrder::create([
            'contract_id' => $this->contract->id,
            'ro_number' => $this->roNumber,
            'ro_date' => $this->roDate,
            'notes' => $this->roNotes ?: null,
            'status' => 'baru',
            'created_by' => auth()->id(),
        ]);

        $this->showRoModal = false;
        session()->flash('success', 'Release Order tersimpan. Lanjutkan dengan menambah item kebutuhan di halaman detail RO.');
        $this->redirect(route('contracts.release-orders.show', $ro), navigate: false);
    }

    // ===== Jalur spesifik: langsung buat Project =====

    public function openCreateProject(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-contracts') || auth()->user()->hasPermissionTo('manage-projects'), 403);
        abort_unless($this->contract->contract_type === 'spesifik', 400, 'Hanya kontrak spesifik yang langsung membentuk 1 Project.');
        abort_if($this->contract->directProject()->exists(), 400, 'Kontrak ini sudah punya Project.');

        $this->reset(['projectPicUserId']);
        $this->projectName = $this->contract->customer->name.' - '.$this->contract->contract_number;
        $this->projectStartDate = optional($this->contract->start_date)->format('Y-m-d') ?? '';
        $this->projectEndDate = optional($this->contract->end_date)->format('Y-m-d') ?? '';
        $this->showProjectModal = true;
    }

    public function saveProject(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-contracts') || auth()->user()->hasPermissionTo('manage-projects'), 403);
        abort_if($this->contract->directProject()->exists(), 400, 'Kontrak ini sudah punya Project.');

        $this->validate([
            'projectName' => ['required', 'string', 'max:255'],
            'projectPicUserId' => ['nullable', Rule::exists('users', 'id')],
            'projectStartDate' => ['nullable', 'date'],
            'projectEndDate' => ['nullable', 'date', 'after_or_equal:projectStartDate'],
        ]);

        $project = Project::create([
            'unit_id' => $this->contract->unit_id,
            'contract_id' => $this->contract->id,
            'pic_user_id' => $this->projectPicUserId ?: null,
            'name' => $this->projectName,
            'description' => $this->contract->scope_description,
            'budget' => null,
            'project_value' => $this->contract->fixed_value,
            'start_date' => $this->projectStartDate ?: null,
            'end_date' => $this->projectEndDate ?: null,
            'status' => 'planning',
        ]);

        $this->showProjectModal = false;
        session()->flash('success', 'Project berhasil dibuat dari kontrak ini.');
        $this->redirect(route('projects.show', $project), navigate: false);
    }
}
