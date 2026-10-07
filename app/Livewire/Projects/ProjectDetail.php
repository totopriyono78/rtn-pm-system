<?php

namespace App\Livewire\Projects;

use App\Models\Activity;
use App\Models\Assignment;
use App\Models\Project;
use App\Models\Site;
use App\Models\User;
use App\Support\Audit;
use App\Support\Notifier;
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

    public string $activitySiteId = '';

    public bool $showBudgetModal = false;

    public bool $showAssignModal = false;

    public ?int $assigningActivityId = null;

    public string $assignUserId = '';

    public string $assignScheduledDate = '';

    public string $assignNotes = '';

    public bool $showRejectModal = false;

    public ?int $rejectingAssignmentId = null;

    public string $rejectReason = '';

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
            'activities.site',
            'purchaseOrders' => fn ($q) => $q->with('vendor', 'items.item'),
            'documents.uploader',
            'directContract.customer',
            'directContract.sites',
            'customerPurchaseOrder.customerQuotation.releaseOrder.contract.customer',
            'customerPurchaseOrder.customerQuotation.releaseOrder.contract.sites',
        ]);

        $user = auth()->user();

        $reports = \App\Models\Report::whereIn('activity_id', $this->project->activities->pluck('id'))
            ->with('user', 'activity', 'files')
            ->latest('report_date')
            ->get();

        // Dikelompokkan per tanggal agar mudah ditelusuri kapan laporan masuk,
        // dan supaya baris Final Report bisa disorot per kelompoknya di view.
        $reportsByDate = $reports->groupBy(fn ($r) => $r->report_date->format('Y-m-d'));

        $safetyTalks = \App\Models\SafetyTalk::whereIn('activity_id', $this->project->activities->pluck('id'))
            ->with('activity', 'conductor')
            ->latest('meeting_date')
            ->get();

        $teknisiOptions = User::role('Teknisi')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('livewire.projects.project-detail', [
            'canManage' => $user->hasPermissionTo('manage-projects'),
            'canCreateAssignments' => $this->userCanCreateAssignments(),
            'canApproveAssignments' => $this->userCanApproveAssignments(),
            'canViewReports' => $user->hasPermissionTo('view-reports'),
            'canViewHarga' => $user->hasPermissionTo('view-harga'),
            'reports' => $reports,
            'reportsByDate' => $reportsByDate,
            'safetyTalks' => $safetyTalks,
            'teknisiOptions' => $teknisiOptions,
            'sites' => Site::where('is_active', true)->orderBy('name')->get(),
            'gantt' => $this->buildGanttData(),
            'documentsByCategory' => $this->project->documents
                ->filter(fn ($doc) => $doc->isVisibleTo($user))
                ->whereNotIn('id', $this->project->documents->pluck('parent_document_id')->filter())
                ->groupBy('category'),
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
        $this->reset(['editingActivityId', 'activityName', 'activityStartDate', 'activityEndDate', 'activitySiteId']);
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
        $this->activitySiteId = (string) $activity->site_id;
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
            'activitySiteId' => ['nullable', Rule::exists('sites', 'id')],
        ]);

        Activity::updateOrCreate(['id' => $this->editingActivityId], [
            'project_id' => $this->project->id,
            'site_id' => $this->activitySiteId ?: null,
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

    public function openBudgetDetail(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('view-harga'), 403);
        $this->showBudgetModal = true;
    }

    protected function userCanCreateAssignments(): bool
    {
        $user = auth()->user();

        return $user->hasPermissionTo('manage-projects') || $user->hasPermissionTo('create-assignments');
    }

    protected function userCanApproveAssignments(): bool
    {
        $user = auth()->user();

        return $user->hasPermissionTo('manage-projects') || $user->hasPermissionTo('approve-assignments');
    }

    public function openAssignTeknisi(int $activityId): void
    {
        abort_unless($this->userCanCreateAssignments(), 403);

        $this->reset(['assignUserId', 'assignNotes']);
        $this->assigningActivityId = $activityId;
        $this->assignScheduledDate = now()->format('Y-m-d');
        $this->showAssignModal = true;
    }

    public function saveAssignment(): void
    {
        abort_unless($this->userCanCreateAssignments(), 403);

        $this->validate([
            'assignUserId' => ['required', Rule::exists('users', 'id')],
            'assignScheduledDate' => ['required', 'date'],
            'assignNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Pemegang manage-projects/approve-assignments (PM, Administrator) masih
        // bisa menugaskan langsung tanpa approval tambahan -- persis seperti
        // alur sebelumnya. Hanya yang HANYA punya create-assignments (Lead
        // Technician) yang usulannya masuk status 'diajukan' dulu.
        $canApprove = $this->userCanApproveAssignments();

        $assignment = Assignment::create([
            'activity_id' => $this->assigningActivityId,
            'user_id' => $this->assignUserId,
            'scheduled_date' => $this->assignScheduledDate,
            'notes' => $this->assignNotes,
            'status' => $canApprove ? 'disetujui' : 'diajukan',
            'created_by' => auth()->id(),
            'approved_by' => $canApprove ? auth()->id() : null,
            'approved_at' => $canApprove ? now() : null,
        ]);

        Audit::log($assignment, $canApprove ? 'approved' : 'created', $canApprove
            ? "Penugasan dibuat & otomatis disetujui oleh ".auth()->user()->name."."
            : "Penugasan diajukan oleh ".auth()->user()->name.", menunggu approval.");

        if (! $canApprove) {
            Notifier::permission('approve-assignments', 'Usulan Penugasan Menunggu Approval', "Usulan penugasan baru pada proyek {$this->project->name} menunggu persetujuan Anda.", route('projects.show', $this->project));
        }

        $this->showAssignModal = false;
        session()->flash('success', $canApprove
            ? 'Teknisi berhasil ditugaskan.'
            : 'Usulan jadwal terkirim, menunggu approval PM/Administrator.');
    }

    public function removeAssignment(int $id): void
    {
        $user = auth()->user();
        $assignment = Assignment::findOrFail($id);

        // Lead Technician hanya boleh membatalkan usulan MILIK SENDIRI yang
        // masih 'diajukan' -- begitu disetujui/ditolak, hanya pemegang
        // manage-projects yang bisa menghapusnya.
        $isOwnPendingProposal = $assignment->status === 'diajukan'
            && $assignment->created_by === $user->id
            && $user->hasPermissionTo('create-assignments');

        abort_unless($user->hasPermissionTo('manage-projects') || $isOwnPendingProposal, 403);

        $assignment->delete();
        session()->flash('success', 'Penugasan dibatalkan.');
    }

    public function approveAssignment(int $id): void
    {
        abort_unless($this->userCanApproveAssignments(), 403);

        $assignment = Assignment::findOrFail($id);
        abort_unless($assignment->status === 'diajukan', 400);

        $assignment->update([
            'status' => 'disetujui',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        Audit::log($assignment, 'approved', "Penugasan disetujui oleh ".auth()->user()->name.".");
        Notifier::user($assignment->user, 'Penugasan Disetujui', "Penugasan Anda pada proyek {$this->project->name} telah disetujui.", route('teknisi.schedule'));

        session()->flash('success', 'Usulan jadwal disetujui. Teknisi sekarang bisa melihatnya di Jadwal Saya.');
    }

    public function openRejectAssignment(int $id): void
    {
        abort_unless($this->userCanApproveAssignments(), 403);

        $this->rejectingAssignmentId = $id;
        $this->rejectReason = '';
        $this->showRejectModal = true;
    }

    public function saveRejectAssignment(): void
    {
        abort_unless($this->userCanApproveAssignments(), 403);

        $this->validate([
            'rejectReason' => ['required', 'string', 'max:500'],
        ]);

        $assignment = Assignment::findOrFail($this->rejectingAssignmentId);
        abort_unless($assignment->status === 'diajukan', 400);

        $assignment->update([
            'status' => 'ditolak',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'rejection_reason' => $this->rejectReason,
        ]);

        Audit::log($assignment, 'rejected', "Penugasan ditolak oleh ".auth()->user()->name.". Alasan: {$this->rejectReason}");
        Notifier::user($assignment->user, 'Penugasan Ditolak', "Penugasan Anda pada proyek {$this->project->name} ditolak. Alasan: {$this->rejectReason}", route('teknisi.schedule'));

        $this->showRejectModal = false;
        session()->flash('success', 'Usulan jadwal ditolak.');
    }
}
