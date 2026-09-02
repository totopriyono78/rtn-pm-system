<?php

namespace App\Livewire\Purchasing;

use App\Models\MaterialTracking as MaterialTrackingModel;
use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class MaterialTracking extends Component
{
    public string $projectFilter = '';

    public function render()
    {
        $trackings = MaterialTrackingModel::with('item', 'project', 'updatedBy')
            ->when($this->projectFilter, fn ($q) => $q->where('project_id', $this->projectFilter))
            ->latest()
            ->get();

        return view('livewire.purchasing.material-tracking', [
            'trackings' => $trackings,
            'projects' => Project::orderBy('name')->get(),
            'canManage' => auth()->user()->hasPermissionTo('manage-material-tracking'),
        ]);
    }

    public function updateStatus(int $id, string $status): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-material-tracking'), 403);

        $tracking = MaterialTrackingModel::findOrFail($id);
        $tracking->changeStatus($status, Auth::user());

        session()->flash('success', 'Status material tracking diperbarui.');
    }
}
