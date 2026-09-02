<?php

namespace App\Livewire\Kpi;

use App\Models\KpiSetting;
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
        $setting = KpiSetting::current();

        $today = now()->toDateString();
        $startOfWeek = now()->startOfWeek()->toDateString();
        $startOfMonth = now()->startOfMonth()->toDateString();

        $todayHours = WorkLog::where('log_date', $today)
            ->select('user_id', DB::raw('SUM(duration_minutes) as minutes'))
            ->groupBy('user_id')
            ->with('user')
            ->get();

        $maxTodayMinutes = max(1, $todayHours->max('minutes') ?? 1);

        // Hanya role Teknisi yang mengisi jam kerja -- dibatasi eksplisit di sini
        // (sebelumnya mengambil SEMUA user lalu "tersaring" jadi teknisi secara
        // kebetulan lewat filter jam>0; begitu opsi "sertakan 0 jam" dinyalakan,
        // itu akan salah ikut menampilkan Admin/Direktur/PM yang memang tidak
        // pernah mengisi WorkLog sama sekali).
        $userIds = User::role('Teknisi')->pluck('id');

        $accumulation = $userIds->map(function ($userId) use ($today, $startOfWeek, $startOfMonth) {
            $user = User::find($userId);

            return [
                'user' => $user,
                'today' => round(WorkLog::where('user_id', $userId)->where('log_date', $today)->sum('duration_minutes') / 60, 1),
                'week' => round(WorkLog::where('user_id', $userId)->whereBetween('log_date', [$startOfWeek, $today])->sum('duration_minutes') / 60, 1),
                'month' => round(WorkLog::where('user_id', $userId)->whereBetween('log_date', [$startOfMonth, $today])->sum('duration_minutes') / 60, 1),
            ];
        })
            // Diatur di halaman Pengaturan KPI: karyawan yang belum lapor sama
            // sekali (0 jam) bisa disembunyikan (default) atau disertakan.
            ->when(
                ! $setting->include_zero_hour_employees,
                fn ($rows) => $rows->filter(fn ($row) => $row['month'] > 0)
            )
            // Diurutkan dari jam kerja bulanan tertinggi supaya langsung terlihat
            // ranking-nya, bukan cuma daftar datar tanpa pembanding.
            ->sortByDesc('month')
            ->values();

        // Rata-rata jam kerja bulanan tim -- ditampilkan apa adanya di kartu
        // ringkasan (bukan dikali ambang toleransi), supaya gampang dibaca.
        $teamAvgMonth = $accumulation->isNotEmpty() ? round($accumulation->avg('month'), 1) : 0;

        // ===== Ambang batas per periode, sesuai mode di halaman Pengaturan KPI =====
        if ($setting->isTargetMode()) {
            $thresholdDay = $setting->min_hours_day;
            $thresholdWeek = $setting->min_hours_week;
            $thresholdMonth = $setting->min_hours_month;
        } else {
            $marginFactor = $setting->average_margin_percent / 100;
            $thresholdDay = round(($accumulation->avg('today') ?: 0) * $marginFactor, 2);
            $thresholdWeek = round(($accumulation->avg('week') ?: 0) * $marginFactor, 2);
            $thresholdMonth = round($teamAvgMonth * $marginFactor, 2);
        }

        $totalTeknisi = User::role('Teknisi')->count();
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
            'kpiSetting' => $setting,
            'thresholdDay' => $thresholdDay,
            'thresholdWeek' => $thresholdWeek,
            'thresholdMonth' => $thresholdMonth,
        ]);
    }
}
