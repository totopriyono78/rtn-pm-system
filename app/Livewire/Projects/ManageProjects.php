<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ManageProjects extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $unitId = '';

    public string $picUserId = '';

    public string $name = '';

    public string $description = '';

    public string $startDate = '';

    public string $endDate = '';

    public string $status = 'planning';

    public function mount(): void
    {
        abort_unless(
            auth()->user()->hasAnyPermission(['manage-projects', 'view-all-project', 'view-reports', 'view-purchasing']) || auth()->user()->hasPermissionTo('submit-report'),
            403
        );
    }

    public function render()
    {
        $projects = Project::query()
            ->visibleTo(auth()->user())
            ->with(['unit.region', 'pic', 'activities'])
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%"))
            ->when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(10);

        return view('livewire.projects.manage-projects', [
            'projects' => $projects,
            'units' => Unit::with('region')->orderBy('name')->get(),
            'pics' => User::role(['Project Manager', 'Administrator'])->orderBy('name')->get(),
            'canManage' => auth()->user()->hasPermissionTo('manage-projects'),
        ]);
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $project = Project::findOrFail($id);
        $this->editingId = $project->id;
        $this->unitId = (string) $project->unit_id;
        $this->picUserId = (string) $project->pic_user_id;
        $this->name = $project->name;
        $this->description = (string) $project->description;
        $this->startDate = optional($project->start_date)->format('Y-m-d') ?? '';
        $this->endDate = optional($project->end_date)->format('Y-m-d') ?? '';
        $this->status = $project->status;
        $this->showModal = true;
    }

    public function save(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-projects'), 403);

        $this->validate([
            'unitId' => ['required', Rule::exists('units', 'id')],
            'picUserId' => ['nullable', Rule::exists('users', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date', 'after_or_equal:startDate'],
            'status' => ['required', Rule::in(array_keys(Project::STATUSES))],
        ]);

        Project::updateOrCreate(['id' => $this->editingId], [
            'unit_id' => $this->unitId,
            'pic_user_id' => $this->picUserId ?: null,
            'name' => $this->name,
            'description' => $this->description,
            'start_date' => $this->startDate ?: null,
            'end_date' => $this->endDate ?: null,
            'status' => $this->status,
        ]);

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Proyek tersimpan.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'unitId', 'picUserId', 'name', 'description', 'startDate', 'endDate']);
        $this->status = 'planning';
        $this->resetErrorBag();
    }
}
