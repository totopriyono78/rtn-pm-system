<?php

namespace App\Livewire\Projects;

use App\Models\Activity;
use App\Models\Project;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ProjectDetail extends Component
{
    public Project $project;

    public string $activeTab = 'overview';

    public bool $showActivityModal = false;

    public ?int $editingActivityId = null;

    public string $activityName = '';

    public string $activityStatus = 'belum_dimulai';

    public string $activityPlannedHours = '0';

    public function mount(Project $project): void
    {
        abort_unless(
            Project::query()->visibleTo(auth()->user())->whereKey($project->id)->exists(),
            403,
            'Anda tidak memiliki akses ke proyek ini.'
        );

        $this->project = $project;
    }

    public function render()
    {
        $this->project->load([
            'unit.region',
            'pic',
            'activities.assignments.user',
            'activities.workLogs',
            'purchaseOrders' => fn ($q) => $q->with('vendor', 'items.item'),
        ]);

        $user = auth()->user();

        $reports = \App\Models\Report::whereIn('activity_id', $this->project->activities->pluck('id'))
            ->with('user', 'activity', 'files')
            ->latest('report_date')
            ->get();

        return view('livewire.projects.project-detail', [
            'canManage' => $user->hasPermissionTo('manage-projects'),
            'canViewReports' => $user->hasPermissionTo('view-reports'),
            'canViewHarga' => $user->hasPermissionTo('view-harga'),
            'reports' => $reports,
        ]);
    }

    public function openCreateActivity(): void
    {
        $this->reset(['editingActivityId', 'activityName']);
        $this->activityStatus = 'belum_dimulai';
        $this->activityPlannedHours = '0';
        $this->showActivityModal = true;
    }

    public function openEditActivity(int $id): void
    {
        $activity = Activity::findOrFail($id);
        $this->editingActivityId = $activity->id;
        $this->activityName = $activity->name;
        $this->activityStatus = $activity->status;
        $this->activityPlannedHours = (string) $activity->planned_hours;
        $this->showActivityModal = true;
    }

    public function saveActivity(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-projects'), 403);

        $this->validate([
            'activityName' => ['required', 'string', 'max:255'],
            'activityStatus' => ['required', Rule::in(array_keys(Activity::STATUSES))],
            'activityPlannedHours' => ['required', 'numeric', 'min:0'],
        ]);

        Activity::updateOrCreate(['id' => $this->editingActivityId], [
            'project_id' => $this->project->id,
            'name' => $this->activityName,
            'status' => $this->activityStatus,
            'planned_hours' => $this->activityPlannedHours,
            'order_no' => $this->editingActivityId ? Activity::find($this->editingActivityId)->order_no : ($this->project->activities()->max('order_no') + 1),
        ]);

        $this->showActivityModal = false;
        session()->flash('success', 'Activity tersimpan.');
    }

    public function quickUpdateStatus(int $activityId, string $status): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-projects'), 403);
        Activity::whereKey($activityId)->update(['status' => $status]);
    }

    public function deleteActivity(int $id): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-projects'), 403);
        Activity::findOrFail($id)->delete();
        session()->flash('success', 'Activity dihapus.');
    }
}
