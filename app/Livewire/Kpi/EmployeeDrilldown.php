<?php

namespace App\Livewire\Kpi;

use App\Models\User;
use App\Models\WorkLog;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class EmployeeDrilldown extends Component
{
    public User $user;

    public string $fromDate = '';

    public string $toDate = '';

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->fromDate = now()->startOfMonth()->format('Y-m-d');
        $this->toDate = now()->format('Y-m-d');
    }

    public function render()
    {
        $logs = WorkLog::where('user_id', $this->user->id)
            ->whereBetween('log_date', [$this->fromDate, $this->toDate])
            ->with('activity.project')
            ->orderByDesc('log_date')
            ->get();

        $byActivity = $logs->groupBy(fn ($log) => $log->activity->name.'||'.$log->activity->project->name)
            ->map(function ($group) {
                return [
                    'activity' => $group->first()->activity->name,
                    'project' => $group->first()->activity->project->name,
                    'minutes' => $group->sum('duration_minutes'),
                ];
            })->values();

        return view('livewire.kpi.employee-drilldown', [
            'logs' => $logs,
            'byActivity' => $byActivity,
            'totalHours' => round($logs->sum('duration_minutes') / 60, 1),
        ]);
    }
}
