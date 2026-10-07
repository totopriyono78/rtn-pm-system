<?php

namespace App\Livewire\Sales;

use App\Models\Prospect;
use App\Models\SalesOrder;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class MarketingDashboard extends Component
{
    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('view-sales-dashboard'), 403);
    }

    public function render()
    {
        $openStages = Prospect::PIPELINE_STAGES;

        $byStage = Prospect::query()
            ->selectRaw('status, count(*) as total, coalesce(sum(estimated_value), 0) as value')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $totalOpen = collect($openStages)->sum(fn ($s) => (int) ($byStage[$s]->total ?? 0));
        $totalWon = (int) ($byStage['won']->total ?? 0);
        $totalLost = (int) ($byStage['lost']->total ?? 0);
        $closedCount = $totalWon + $totalLost;
        $winRate = $closedCount > 0 ? round(($totalWon / $closedCount) * 100, 1) : null;
        $pipelineValue = collect($openStages)->sum(fn ($s) => (float) ($byStage[$s]->value ?? 0));

        $soByStatus = SalesOrder::query()
            ->selectRaw('status, count(*) as total, coalesce(sum(total_value), 0) as value')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $recentActivities = \App\Models\ProspectActivity::with(['prospect', 'user'])
            ->latest('activity_date')
            ->take(8)
            ->get();

        return view('livewire.sales.marketing-dashboard', [
            'byStage' => $byStage,
            'totalOpen' => $totalOpen,
            'totalWon' => $totalWon,
            'totalLost' => $totalLost,
            'winRate' => $winRate,
            'pipelineValue' => $pipelineValue,
            'soByStatus' => $soByStatus,
            'recentActivities' => $recentActivities,
        ]);
    }
}
