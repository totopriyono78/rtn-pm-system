<?php

namespace App\Livewire\Teknisi;

use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class MyReports extends Component
{
    use WithPagination;

    public function render()
    {
        $reports = Report::where('user_id', Auth::id())
            ->with('activity.project', 'files')
            ->orderByDesc('report_date')
            ->paginate(10);

        return view('livewire.teknisi.my-reports', [
            'reports' => $reports,
        ]);
    }
}
