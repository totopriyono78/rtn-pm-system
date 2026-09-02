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

    /**
     * Dikelompokkan per proyek agar bagian Purchasing bisa langsung melihat
     * seluruh barang yang dipesan untuk masing-masing proyek: item, qty,
     * nomor PO terkait, tanggal pesan, dan status penerimaannya.
     */
    public function render()
    {
        $trackings = MaterialTrackingModel::with(['item', 'project', 'updatedBy', 'purchaseOrderItem.purchaseOrder'])
            ->when($this->projectFilter, fn ($q) => $q->where('project_id', $this->projectFilter))
            ->latest()
            ->get();

        $trackingsByProject = $trackings
            ->groupBy('project_id')
            ->map(fn ($items) => [
                'project' => $items->first()->project,
                'items' => $items,
                'receivedCount' => $items->whereIn('status', ['arrived', 'installed'])->count(),
            ])
            ->sortBy(fn ($group) => $group['project']->name)
            ->values();

        return view('livewire.purchasing.material-tracking', [
            'trackingsByProject' => $trackingsByProject,
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
