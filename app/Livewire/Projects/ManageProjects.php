<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ManageProjects extends Component
{
    use WithFileUploads, WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public bool $showModal = false;

    public ?int $editingId = null;

    public string $unitId = '';

    public string $picUserId = '';

    public string $name = '';

    public string $description = '';

    public string $budget = '';

    public string $startDate = '';

    public string $endDate = '';

    public string $status = 'planning';

    /** @var array */
    public $newDocuments = [];

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
            'existingDocuments' => $this->editingId
                ? ProjectDocument::where('project_id', $this->editingId)->latest()->get()
                : collect(),
            'maxUploadMb' => (int) env('MAX_UPLOAD_SIZE_MB', 50),
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
        $this->budget = $project->budget !== null ? (string) $project->budget : '';
        $this->startDate = optional($project->start_date)->format('Y-m-d') ?? '';
        $this->endDate = optional($project->end_date)->format('Y-m-d') ?? '';
        $this->status = $project->status;
        $this->showModal = true;
    }

    public function save(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-projects'), 403);

        $maxKb = ((int) env('MAX_UPLOAD_SIZE_MB', 50)) * 1024;

        $this->validate([
            'unitId' => ['required', Rule::exists('units', 'id')],
            'picUserId' => ['nullable', Rule::exists('users', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'startDate' => ['nullable', 'date'],
            'endDate' => ['nullable', 'date', 'after_or_equal:startDate'],
            'status' => ['required', Rule::in(array_keys(Project::STATUSES))],
            'newDocuments.*' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,zip,jpg,jpeg,png', 'max:'.$maxKb],
        ]);

        $project = Project::updateOrCreate(['id' => $this->editingId], [
            'unit_id' => $this->unitId,
            'pic_user_id' => $this->picUserId ?: null,
            'name' => $this->name,
            'description' => $this->description,
            'budget' => $this->budget !== '' ? $this->budget : null,
            'start_date' => $this->startDate ?: null,
            'end_date' => $this->endDate ?: null,
            'status' => $this->status,
        ]);

        foreach ($this->newDocuments as $file) {
            if (! $file) {
                continue;
            }

            $path = $file->store("project-files/{$project->id}/Dokumen Proyek", 'local');

            $project->documents()->create([
                'uploaded_by' => auth()->id(),
                'disk_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
            ]);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('success', 'Proyek tersimpan.');
    }

    public function removeDocument(int $id): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-projects'), 403);

        $document = ProjectDocument::findOrFail($id);
        Storage::disk('local')->delete($document->disk_path);
        $document->delete();

        session()->flash('success', 'Dokumen dihapus.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'unitId', 'picUserId', 'name', 'description', 'budget', 'startDate', 'endDate', 'newDocuments']);
        $this->status = 'planning';
        $this->resetErrorBag();
    }
}
