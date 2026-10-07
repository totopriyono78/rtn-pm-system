<?php

namespace App\Livewire\Teknisi;

use App\Models\Assignment;
use App\Models\SafetyTalk;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

/**
 * Safety Talk / Toolbox Meeting -- SRS 4.9. Dipakai teknisi (termasuk Lead
 * Technician) untuk mencatat briefing K3 sebelum/selama bekerja. Memakai
 * daftar penugasan yang sama seperti Submit Laporan (approved() saja),
 * supaya konsisten dengan apa yang sudah bisa dikerjakan teknisi hari ini.
 */
#[Layout('layouts.app')]
class SafetyTalks extends Component
{
    use WithFileUploads, WithPagination;

    public string $assignmentId = '';

    public string $meetingDate = '';

    public string $topic = '';

    public string $attendees = '';

    public string $notes = '';

    /** @var mixed */
    public $photo = null;

    public function mount(): void
    {
        $this->meetingDate = now()->format('Y-m-d');
    }

    public function render()
    {
        $assignments = Assignment::where('user_id', Auth::id())
            ->approved()
            ->with('activity.project')
            ->orderByDesc('scheduled_date')
            ->get();

        $logs = SafetyTalk::where('conducted_by', Auth::id())
            ->with('activity.project')
            ->latest('meeting_date')
            ->paginate(10);

        return view('livewire.teknisi.safety-talks', [
            'assignments' => $assignments,
            'logs' => $logs,
            'maxUploadMb' => (int) env('MAX_UPLOAD_SIZE_MB', 50),
        ]);
    }

    public function save(): void
    {
        $maxKb = ((int) env('MAX_UPLOAD_SIZE_MB', 50)) * 1024;

        $this->validate([
            'assignmentId' => ['required', Rule::exists('assignments', 'id')],
            'meetingDate' => ['required', 'date'],
            'topic' => ['required', 'string', 'max:255'],
            'attendees' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'max:'.$maxKb],
        ]);

        // Dibatasi ke penugasan MILIK SENDIRI yang sudah disetujui -- sama
        // seperti Submit Laporan, supaya safety talk tidak bisa dicatat atas
        // nama activity yang bukan tanggung jawab teknisi ybs.
        $assignment = Assignment::where('user_id', Auth::id())->approved()->findOrFail($this->assignmentId);

        $photoData = [];
        if ($this->photo) {
            $project = $assignment->activity->project;
            $path = $this->photo->store("project-files/{$project->id}/Safety Talk", 'local');
            $photoData = [
                'photo_disk_path' => $path,
                'photo_original_name' => $this->photo->getClientOriginalName(),
                'photo_mime_type' => $this->photo->getMimeType(),
                'photo_size_bytes' => $this->photo->getSize(),
            ];
        }

        SafetyTalk::create(array_merge([
            'activity_id' => $assignment->activity_id,
            'conducted_by' => Auth::id(),
            'meeting_date' => $this->meetingDate,
            'topic' => $this->topic,
            'attendees' => $this->attendees ?: null,
            'notes' => $this->notes ?: null,
        ], $photoData));

        session()->flash('success', 'Safety Talk / Toolbox Meeting tercatat.');
        $this->reset(['assignmentId', 'topic', 'attendees', 'notes', 'photo']);
        $this->meetingDate = now()->format('Y-m-d');
    }
}
