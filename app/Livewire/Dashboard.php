<?php

namespace App\Livewire;

use App\Models\Activity;
use App\Models\Assignment;
use App\Models\CashAdvance;
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
                ->approved()
                ->whereDate('scheduled_date', today())
                ->with('activity.project')
                ->get();
        }

        // Usulan jadwal dari Lead Technician yang menunggu approval PM/Admin --
        // dikumpulkan lintas proyek (sesuai region yang boleh dilihat user) supaya
        // PM tidak perlu buka satu-satu Detail Proyek untuk menemukannya.
        $pendingAssignmentApprovals = null;
        if ($user->hasPermissionTo('manage-projects') || $user->hasPermissionTo('approve-assignments')) {
            $pendingAssignmentApprovals = Assignment::where('status', 'diajukan')
                ->whereHas('activity.project', fn ($q) => $q->visibleTo($user))
                ->with('activity.project', 'user', 'creator')
                ->oldest()
                ->take(5)
                ->get();
        }

        $pendingApprovals = null;
        if ($user->hasPermissionTo('approve-purchasing')) {
            $pendingApprovals = RequestForQuotation::where('status', 'submitted')->with('project')->latest()->take(5)->get();
        }

        // Kasbon yang menunggu approval PM/Administrator -- dikumpulkan
        // lintas proyek, meniru pola widget usulan jadwal Lead Technician di
        // atas supaya PM punya satu tempat untuk melihat semua approval yang
        // menanti (jadwal + kasbon).
        $pendingCashAdvances = null;
        if ($user->hasPermissionTo('manage-cash-advances')) {
            $pendingCashAdvances = CashAdvance::where('status', 'diajukan')
                ->where(fn ($q) => $q->whereNull('project_id')->orWhereHas('project', fn ($p) => $p->visibleTo($user)))
                ->with('project', 'requester')
                ->oldest()
                ->take(5)
                ->get();
        }

        // ===== Total nilai proyek/pekerjaan (sisi pendapatan) — informasi finansial, sama seperti budget/harga =====
        $totalProjectValue = null;
        if ($user->hasPermissionTo('view-harga')) {
            $totalProjectValue = (float) Project::query()->visibleTo($user)->sum('project_value');
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

        // Activity yang mau mulai dalam 3 hari (H-3) tapi proyeknya belum
        // pernah upload dokumen Simlok & SIKA sama sekali -- reminder
        // kepatuhan sebelum teknisi masuk lokasi (SRS 4.9). Reuse scope
        // visibleTo yang sama seperti widget reminder lain di atas.
        $simlokReminders = null;
        if ($user->hasPermissionTo('manage-projects')) {
            $simlokReminders = Activity::where('status', 'belum_dimulai')
                ->whereNotNull('start_date')
                ->whereBetween('start_date', [now()->startOfDay(), now()->addDays(3)->endOfDay()])
                ->whereHas('project', fn ($q) => $q->visibleTo($user))
                ->whereDoesntHave('project.documents', fn ($q) => $q->where('category', 'simlok_sika'))
                ->with('project')
                ->orderBy('start_date')
                ->take(5)
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
            'pendingAssignmentApprovals' => $pendingAssignmentApprovals,
            'pendingCashAdvances' => $pendingCashAdvances,
            'pendingMaterials' => $pendingMaterials,
            'simlokReminders' => $simlokReminders,
            'totalProjectValue' => $totalProjectValue,
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
