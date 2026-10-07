<?php

namespace App\Livewire\Operasional;

use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\Report;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Admin Kantor (SRS v2.0 modul 4.13): rekap laporan mingguan per proyek --
 * jumlah laporan teknisi yang masuk minggu ini, kelengkapan dokumen BAPP RO
 * & BAL Bulanan (ditarik dari Document Management, modul 4.10), dan status
 * Simlok & SIKA (izin masuk lokasi/kerja aman -- reminder H-3 kalau ada
 * activity yang mau mulai tapi dokumennya belum diupload sama sekali, SRS
 * 4.9).
 */
#[Layout('layouts.app')]
class WeeklyReportRecap extends Component
{
    public string $weekAnchor = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('view-weekly-recap'), 403);
        $this->weekAnchor = now()->format('Y-m-d');
    }

    public function previousWeek(): void
    {
        $this->weekAnchor = Carbon::parse($this->weekAnchor)->subWeek()->format('Y-m-d');
    }

    public function nextWeek(): void
    {
        $this->weekAnchor = Carbon::parse($this->weekAnchor)->addWeek()->format('Y-m-d');
    }

    public function thisWeek(): void
    {
        $this->weekAnchor = now()->format('Y-m-d');
    }

    public function render()
    {
        $anchor = Carbon::parse($this->weekAnchor);
        $weekStart = $anchor->copy()->startOfWeek();
        $weekEnd = $anchor->copy()->endOfWeek();
        $monthStart = $anchor->copy()->startOfMonth();
        $monthEnd = $anchor->copy()->endOfMonth();

        $projects = Project::query()
            ->visibleTo(auth()->user())
            ->where('status', 'ongoing')
            ->with(['unit.region', 'activities', 'documents'])
            ->orderBy('name')
            ->get();

        $rows = $projects->map(function (Project $project) use ($weekStart, $weekEnd, $monthStart, $monthEnd) {
            $activityIds = $project->activities->pluck('id');

            $reportCount = Report::whereIn('activity_id', $activityIds)
                ->whereBetween('report_date', [$weekStart->format('Y-m-d'), $weekEnd->format('Y-m-d')])
                ->count();

            $hasBapp = $project->documents
                ->where('category', 'bapp_ro')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->isNotEmpty();

            $hasBal = $project->documents
                ->where('category', 'bal_bulanan')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->isNotEmpty();

            // Simlok & SIKA tidak bersifat bulanan seperti BAPP RO/BAL --
            // jadi dicek "pernah diupload sama sekali untuk proyek ini",
            // bukan dibatasi periode seperti dua kategori di atas.
            $hasSimlokSika = $project->documents->where('category', 'simlok_sika')->isNotEmpty();

            $needsSimlokSoon = ! $hasSimlokSika && $project->activities->contains(
                fn ($activity) => $activity->status === 'belum_dimulai'
                    && $activity->start_date !== null
                    && $activity->start_date->between(now()->startOfDay(), now()->addDays(3)->endOfDay())
            );

            return [
                'project' => $project,
                'reportCount' => $reportCount,
                'hasBapp' => $hasBapp,
                'hasBal' => $hasBal,
                'hasSimlokSika' => $hasSimlokSika,
                'needsSimlokSoon' => $needsSimlokSoon,
            ];
        });

        return view('livewire.operasional.weekly-report-recap', [
            'rows' => $rows,
            'weekStart' => $weekStart,
            'weekEnd' => $weekEnd,
            'monthLabel' => $anchor->translatedFormat('F Y'),
        ]);
    }
}
