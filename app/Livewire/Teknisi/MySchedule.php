<?php

namespace App\Livewire\Teknisi;

use App\Models\Assignment;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class MySchedule extends Component
{
    use WithPagination;

    /**
     * Default: hanya tampilkan penugasan yang belum dilaporkan/dikerjakan,
     * supaya daftar tidak penuh riwayat lama begitu penugasan sudah banyak.
     */
    #[Url]
    public string $status = 'belum';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatedDateTo(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['status', 'dateFrom', 'dateTo']);
        $this->resetPage();
    }

    public function render()
    {
        $assignments = Assignment::where('user_id', auth()->id())
            ->with('activity.project.unit.region')
            ->withCount('reports')
            ->when($this->status === 'belum', fn ($q) => $q->doesntHave('reports'))
            ->when($this->status === 'sudah', fn ($q) => $q->has('reports'))
            ->when($this->dateFrom !== '', fn ($q) => $q->whereDate('scheduled_date', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($q) => $q->whereDate('scheduled_date', '<=', $this->dateTo))
            ->orderByDesc('scheduled_date')
            ->paginate(15);

        return view('livewire.teknisi.my-schedule', [
            'assignments' => $assignments,
        ]);
    }
}
