<?php

namespace App\Livewire\Teknisi;

use App\Models\Assignment;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class MySchedule extends Component
{
    use WithPagination;

    public function render()
    {
        $assignments = Assignment::where('user_id', auth()->id())
            ->with('activity.project.unit.region')
            ->orderByDesc('scheduled_date')
            ->paginate(15);

        return view('livewire.teknisi.my-schedule', [
            'assignments' => $assignments,
        ]);
    }
}
