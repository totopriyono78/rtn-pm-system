<?php

namespace App\Livewire\Kpi;

use App\Models\Project;
use App\Models\User;
use App\Models\WorkLog;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class DirekturDashboard extends Component
{
    public function render()
    {
        $today = now()->toDateString();
        $startOfWeek = now()->startOfWeek()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();

        $todayHours = WorkLog::where('log_date', $today)
            ->select('user_id', DB::raw('SUM(duration_minutes) as minutes'))
            ->groupBy('user_id')
            ->with('user')
            ->get();

        $maxTodayMinutes = max(1, $todayHours->max('minutes') ?? 1);

        $userIds = User::pluck('id');

        $accumulation = $userIds->map(function ($userId) use ($today, $startOfWeek, $startOfMonth) {
            $user = User::find($userId);

            return [
                'user' => $user,
                'today' => round(WorkLog::where('user_id', $userId)->where('log_date', $today)->sum('duration_minutes') / 60, 1),
                'week' => round(WorkLog::where('user_id', $userId)->whereBetween('log_date', [$startOfWeek, $today])->sum('duration_minutes') / 60, 1),
                'month' => round(WorkLog::where('user_id', $userId)->whereBetween('log_date', [$startOfMonth, $today])->sum('duration_minutes') / 60, 1),
            ];
        })
            ->filter(fn ($row) => $row['month'] > 0)
            // Diurutkan dari jam kerja bulanan tertinggi supaya langsung terlihat
            // ranking-nya, bukan cuma daftar datar tanpa pembanding.
            ->sortByDesc('month')
            ->values();

        // Rata-rata jam kerja bulanan tim -- dipakai untuk menyoroti karyawan
        // yang jam kerjanya di bawah rata-rata (lihat view: butuh perhatian).
        $teamAvgMonth = $accumulation->isNotEmpty() ? round($accumulation->avg('month'), 1) : 0;

        $totalTeknisiAktif = $accumulation->count();
        $totalTeknisi = max($totalTeknisiAktif, User::role('Teknisi')->count());
        $sudahLaporHariIni = $todayHours->count();
        $totalJamHariIni = round($todayHours->sum('minutes') / 60, 1);

        $projects = Project::query()->with('activities', 'workLogs')->get()->map(function (Project $p) {
            return [
                'project' => $p,
                'planned' => $p->planned_hours,
                'actual' => $p->actual_hours,
            ];
        });

        return view('livewire.kpi.direktur-dashboard', [
            'todayHours' => $todayHours,
            'maxTodayMinutes' => $maxTodayMinutes,
            'accumulation' => $accumulation,
            'teamAvgMonth' => $teamAvgMonth,
            'totalTeknisi' => $totalTeknisi,
            'sudahLaporHariIni' => $sudahLaporHariIni,
            'totalJamHariIni' => $totalJamHariIni,
            'projects' => $projects,
        ]);
    }
}
