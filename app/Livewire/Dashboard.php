<?php

namespace App\Livewire;

use App\Models\Activity;
use App\Models\Assignment;
use App\Models\MaterialTracking;
use App\Models\Project;
use App\Models\RequestForQuotation;
use App\Models\WorkLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    /**
     * Warna kategorikal tervalidasi (aksesibel untuk buta warna), dipakai
     * konsisten per status agar warna yang sama selalu berarti hal yang sama.
     */
    public const STATUS_COLORS = [
        'planning' => '#2a78d6',
        'ongoing' => '#eb6834',
        'completed' => '#1baf7a',
        'on_hold' => '#eda100',
    ];

    public const SEQUENTIAL_HUE = '#2a78d6';

    /**
     * Status di MaterialTracking yang dianggap "belum diterima".
     */
    public const NOT_RECEIVED_STATUSES = ['ordered', 'shipping'];

    public bool $showPendingMaterialModal = false;

    public function openPendingMaterial(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('view-purchasing'), 403);
        $this->showPendingMaterialModal = true;
    }

    public function render()
    {
        $user = Auth::user();

        $visibleProjectIds = Project::query()->visibleTo($user)->pluck('id');

        $projects = Project::query()->visibleTo($user)->with('unit.region', 'activities')->latest()->take(6)->get();

        $todaysAssignments = null;
        if ($user->hasPermissionTo('submit-report')) {
            $todaysAssignments = Assignment::where('user_id', $user->id)
                ->whereDate('scheduled_date', today())
                ->with('activity.project')
                ->get();
        }

        $pendingApprovals = null;
        if ($user->hasPermissionTo('approve-purchasing')) {
            $pendingApprovals = RequestForQuotation::where('status', 'submitted')->with('project')->latest()->take(5)->get();
        }

        // ===== Material yang sudah dipesan tapi belum diterima (belum "arrived"/"installed") =====
        $pendingMaterials = null;
        if ($user->hasPermissionTo('view-purchasing')) {
            $pendingMaterials = MaterialTracking::whereIn('project_id', $visibleProjectIds)
                ->whereIn('status', self::NOT_RECEIVED_STATUSES)
                ->with(['item', 'project', 'purchaseOrderItem.purchaseOrder'])
                ->oldest()
                ->get();
        }

        // ===== Distribusi status proyek (bar chart kategorikal) =====
        $statusCounts = Project::query()->visibleTo($user)->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')->pluck('total', 'status');

        $statusBreakdown = collect(Project::STATUSES)->map(function ($label, $key) use ($statusCounts) {
            return [
                'key' => $key,
                'label' => $label,
                'count' => (int) ($statusCounts[$key] ?? 0),
                'color' => self::STATUS_COLORS[$key],
            ];
        })->values();

        $maxStatusCount = max(1, $statusBreakdown->max('count'));

        // ===== Tren jam kerja 14 hari terakhir (line/area chart) =====
        $workHoursTrend = null;
        $workHoursTrendLabel = null;

        if ($user->hasPermissionTo('view-kpi-team') || $user->hasPermissionTo('submit-report')) {
            $isTeamWide = $user->hasPermissionTo('view-kpi-team');
            $workHoursTrendLabel = $isTeamWide ? 'Jam Kerja Tim' : 'Jam Kerja Saya';

            $start = now()->subDays(13)->startOfDay();
            $rows = WorkLog::query()
                ->when(! $isTeamWide, fn ($q) => $q->where('user_id', $user->id))
                ->when($isTeamWide, fn ($q) => $q->whereIn('project_id', $visibleProjectIds))
                ->where('log_date', '>=', $start->toDateString())
                ->select('log_date', DB::raw('SUM(duration_minutes) as minutes'))
                ->groupBy('log_date')
                ->pluck('minutes', 'log_date');

            $workHoursTrend = collect(range(0, 13))->map(function ($i) use ($start, $rows) {
                $date = $start->copy()->addDays($i);
                $key = $date->toDateString();

                return [
                    'date' => $key,
                    'label' => $date->format('d/m'),
                    'hours' => round((float) ($rows[$key] ?? 0) / 60, 1),
                ];
            });
        }

        // ===== Progress proyek teratas (horizontal bar chart) =====
        $topProjectsProgress = $projects
            ->filter(fn ($p) => $p->activities->isNotEmpty())
            ->sortByDesc('progress_percent')
            ->take(6)
            ->map(fn ($p) => ['name' => $p->name, 'percent' => $p->progress_percent])
            ->values();

        return view('livewire.dashboard', [
            'projects' => $projects,
            'todaysAssignments' => $todaysAssignments,
            'pendingApprovals' => $pendingApprovals,
            'pendingMaterials' => $pendingMaterials,
            'totalProjects' => $visibleProjectIds->count(),
            'ongoingActivities' => Activity::whereIn('project_id', $visibleProjectIds)
                ->where('status', 'sedang_dikerjakan')->count(),
            'statusBreakdown' => $statusBreakdown,
            'maxStatusCount' => $maxStatusCount,
            'workHoursTrend' => $workHoursTrend,
            'workHoursTrendLabel' => $workHoursTrendLabel,
            'topProjectsProgress' => $topProjectsProgress,
        ]);
    }
}
