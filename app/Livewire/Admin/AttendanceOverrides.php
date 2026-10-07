<?php

namespace App\Livewire\Admin;

use App\Models\Assignment;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Override presensi: dipakai PM/Admin untuk mengesahkan check-in teknisi
 * yang GPS-nya di luar radius site (mis. sinyal buruk di dalam tangki/gedung
 * bertingkat) supaya pekerjaan riil tidak terblokir gara-gara keterbatasan
 * GPS, sambil tetap auditable (override_by + alasan tersimpan).
 */
#[Layout('layouts.app')]
class AttendanceOverrides extends Component
{
    use WithPagination;

    #[Url]
    public string $date = '';

    public bool $showModal = false;

    public ?int $overridingAssignmentId = null;

    public string $overrideReason = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-attendance-overrides'), 403);
        $this->date = now()->format('Y-m-d');
    }

    public function render()
    {
        $assignments = Assignment::query()
            ->whereDate('scheduled_date', $this->date ?: now()->toDateString())
            ->whereDoesntHave('attendance')
            ->whereHas('activity', fn ($q) => $q->whereNotNull('site_id'))
            ->with(['user', 'activity.project', 'activity.site'])
            ->orderBy('id')
            ->paginate(15);

        return view('livewire.admin.attendance-overrides', [
            'assignments' => $assignments,
            'recentOverrides' => Attendance::where('status', 'overridden')
                ->with(['user', 'site', 'overrideBy'])
                ->latest('checked_in_at')
                ->limit(10)
                ->get(),
        ]);
    }

    public function openOverride(int $assignmentId): void
    {
        $this->overridingAssignmentId = $assignmentId;
        $this->overrideReason = '';
        $this->showModal = true;
    }

    public function saveOverride(): void
    {
        $this->validate([
            'overrideReason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $assignment = Assignment::with('activity.site')->findOrFail($this->overridingAssignmentId);

        abort_if($assignment->attendance()->exists(), 400, 'Penugasan ini sudah punya presensi.');

        Attendance::create([
            'assignment_id' => $assignment->id,
            'user_id' => $assignment->user_id,
            'site_id' => $assignment->activity->site_id,
            'checked_in_at' => now(),
            'latitude' => $assignment->activity->site->latitude,
            'longitude' => $assignment->activity->site->longitude,
            'accuracy_meters' => null,
            'distance_meters' => null,
            'is_within_radius' => false,
            'status' => 'overridden',
            'override_by' => Auth::id(),
            'override_reason' => $this->overrideReason,
        ]);

        $this->showModal = false;
        session()->flash('success', 'Presensi berhasil di-override.');
    }
}
