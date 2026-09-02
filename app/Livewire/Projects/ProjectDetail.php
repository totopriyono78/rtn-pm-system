<?php

namespace App\Livewire\Projects;

use App\Models\Activity;
use App\Models\Assignment;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
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

    public string $activityStartDate = '';

    public string $activityEndDate = '';

    public bool $showAssignModal = false;

    public ?int $assigningActivityId = null;

    public string $assignUserId = '';

    public string $assignScheduledDate = '';

    public string $assignNotes = '';

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
            'documents.uploader',
        ]);

        $user = auth()->user();

        $reports = \App\Models\Report::whereIn('activity_id', $this->project->activities->pluck('id'))
            ->with('user', 'activity', 'files')
            ->latest('report_date')
            ->get();

        $teknisiOptions = User::role('Teknisi')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.projects.project-detail', [
            'canManage' => $user->hasPermissionTo('manage-projects'),
            'canViewReports' => $user->hasPermissionTo('view-reports'),
            'canViewHarga' => $user->hasPermissionTo('view-harga'),
            'reports' => $reports,
            'teknisiOptions' => $teknisiOptions,
            'gantt' => $this->buildGanttData(),
        ]);
    }

    /**
     * Siapkan data untuk tab Gantt Chart: rentang tanggal keseluruhan (dari
     * activity yang punya start/end date, atau tanggal proyek kalau belum
     * ada satupun activity yang diisi tanggalnya), posisi & lebar bar tiap
     * activity dalam persen, header per-bulan, dan posisi garis "hari ini".
     */
    protected function buildGanttData(): array
    {
        $withDates = $this->project->activities->filter(fn (Activity $a) => $a->start_date && $a->end_date);

        $rangeStart = $withDates->isNotEmpty() ? $withDates->min('start_date') : $this->project->start_date;
        $rangeEnd = $withDates->isNotEmpty() ? $withDates->max('end_date') : $this->project->end_date;

        if (! $rangeStart || ! $rangeEnd) {
            return ['hasRange' => false];
        }

        $rangeStart = Carbon::parse($rangeStart)->startOfDay();
        $rangeEnd = Carbon::parse($rangeEnd)->startOfDay();
        if ($rangeEnd->lt($rangeStart)) {
            $rangeEnd = $rangeStart->copy();
        }
        $totalDays = $rangeStart->diffInDays($rangeEnd) + 1;

        $months = [];
        $cursor = $rangeStart->copy()->startOfMonth();
        while ($cursor->lte($rangeEnd)) {
            $monthStart = $cursor->max($rangeStart);
            $monthEnd = $cursor->copy()->endOfMonth()->min($rangeEnd);
            $days = $monthStart->diffInDays($monthEnd) + 1;
            $months[] = [
                'label' => $cursor->format('M Y'),
                'percent' => round(($days / $totalDays) * 100, 3),
            ];
            $cursor->addMonthNoOverflow()->startOfMonth();
        }

        $bars = $this->project->activities->map(function (Activity $activity) use ($rangeStart, $totalDays) {
            if (! $activity->start_date || ! $activity->end_date) {
                return null;
            }

            $start = $activity->start_date->copy()->startOfDay();
            $end = $activity->end_date->copy()->startOfDay();
            if ($end->lt($start)) {
                $end = $start->copy();
            }

            $offsetDays = max(0, $rangeStart->diffInDays($start));
            $durationDays = $start->diffInDays($end) + 1;

            $left = round(($offsetDays / $totalDays) * 100, 3);
            $width = round(($durationDays / $totalDays) * 100, 3);

            return [
                'activity' => $activity,
                'left' => $left,
                'width' => min($width, 100 - $left),
                'barClass' => Activity::STATUS_BAR_CLASS[$activity->status] ?? 'bg-slate-400',
            ];
        })->filter()->values();

        $todayPercent = null;
        $today = Carbon::today();
        if ($today->between($rangeStart, $rangeEnd)) {
            $todayPercent = round(($rangeStart->diffInDays($today) / $totalDays) * 100, 3);
        }

        return [
            'hasRange' => true,
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
            'months' => $months,
            'bars' => $bars,
            'todayPercent' => $todayPercent,
            'undatedCount' => $this->project->activities->count() - $bars->count(),
        ];
    }

    public function openCreateActivity(): void
    {
        $this->reset(['editingActivityId', 'activityName', 'activityStartDate', 'activityEndDate']);
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
        $this->activityStartDate = optional($activity->start_date)->format('Y-m-d') ?? '';
        $this->activityEndDate = optional($activity->end_date)->format('Y-m-d') ?? '';
        $this->showActivityModal = true;
    }

    public function saveActivity(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-projects'), 403);

        $this->validate([
            'activityName' => ['required', 'string', 'max:255'],
            'activityStatus' => ['required', Rule::in(array_keys(Activity::STATUSES))],
            'activityPlannedHours' => ['required', 'numeric', 'min:0'],
            'activityStartDate' => ['nullable', 'date'],
            'activityEndDate' => ['nullable', 'date', 'after_or_equal:activityStartDate'],
        ]);

        Activity::updateOrCreate(['id' => $this->editingActivityId], [
            'project_id' => $this->project->id,
            'name' => $this->activityName,
            'status' => $this->activityStatus,
            'planned_hours' => $this->activityPlannedHours,
            'start_date' => $this->activityStartDate ?: null,
            'end_date' => $this->activityEndDate ?: null,
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

    public function openAssignTeknisi(int $activityId): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-projects'), 403);

        $this->reset(['assignUserId', 'assignNotes']);
        $this->assigningActivityId = $activityId;
        $this->assignScheduledDate = now()->format('Y-m-d');
        $this->showAssignModal = true;
    }

    public function saveAssignment(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-projects'), 403);

        $this->validate([
            'assignUserId' => ['required', Rule::exists('users', 'id')],
            'assignScheduledDate' => ['required', 'date'],
            'assignNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        Assignment::create([
            'activity_id' => $this->assigningActivityId,
            'user_id' => $this->assignUserId,
            'scheduled_date' => $this->assignScheduledDate,
            'notes' => $this->assignNotes,
        ]);

        $this->showAssignModal = false;
        session()->flash('success', 'Teknisi berhasil ditugaskan.');
    }

    public function removeAssignment(int $id): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-projects'), 403);
        Assignment::findOrFail($id)->delete();
        session()->flash('success', 'Penugasan dibatalkan.');
    }
}
