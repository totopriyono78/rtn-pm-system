<?php

namespace App\Livewire\Teknisi;

use App\Models\Assignment;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class SubmitReport extends Component
{
    use WithFileUploads;

    public string $assignmentId = '';

    public string $type = 'daily';

    public string $reportDate = '';

    public string $startTime = '';

    public string $endTime = '';

    public string $notes = '';

    /** @var array */
    public $documents = [];

    /** @var array */
    public $photos = [];

    /** @var array */
    public $drawings = [];

    public function mount(): void
    {
        $this->reportDate = now()->format('Y-m-d');
    }

    public function render()
    {
        $assignments = Assignment::where('user_id', Auth::id())
            ->with('activity.project')
            ->orderByDesc('scheduled_date')
            ->get();

        return view('livewire.teknisi.submit-report', [
            'assignments' => $assignments,
            'maxUploadMb' => (int) env('MAX_UPLOAD_SIZE_MB', 50),
        ]);
    }

    public function save(): void
    {
        $maxKb = ((int) env('MAX_UPLOAD_SIZE_MB', 50)) * 1024;

        $this->validate([
            'assignmentId' => ['required', 'exists:assignments,id'],
            'type' => ['required', 'in:daily,final'],
            'reportDate' => ['required', 'date'],
            'startTime' => ['required'],
            'endTime' => ['required', 'after:startTime'],
            'notes' => ['nullable', 'string'],
            'documents.*' => ['nullable', 'file', 'mimes:pdf,doc,docx,mp4,mov', 'max:'.$maxKb],
            'photos.*' => ['nullable', 'file', 'image', 'max:'.$maxKb],
            'drawings.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:'.$maxKb],
        ]);

        $assignment = Assignment::where('user_id', Auth::id())->findOrFail($this->assignmentId);

        DB::transaction(function () use ($assignment) {
            $report = Report::create([
                'activity_id' => $assignment->activity_id,
                'user_id' => Auth::id(),
                'assignment_id' => $assignment->id,
                'type' => $this->type,
                'report_date' => $this->reportDate,
                'start_time' => $this->startTime,
                'end_time' => $this->endTime,
                'notes' => $this->notes,
            ]);

            $project = $assignment->activity->project;
            $docCategory = $this->type === 'final' ? 'final_report' : 'daily_report';

            $this->storeFiles($report, $this->documents, $docCategory, $project->id);
            $this->storeFiles($report, $this->photos, 'foto', $project->id);
            $this->storeFiles($report, $this->drawings, 'drawing', $project->id);

            $report->workLog()->create([
                'user_id' => Auth::id(),
                'activity_id' => $assignment->activity_id,
                'project_id' => $project->id,
                'log_date' => $report->report_date,
                'start_time' => $report->start_time,
                'end_time' => $report->end_time,
                'duration_minutes' => $report->duration_minutes,
            ]);
        });

        session()->flash('success', 'Laporan berhasil dikirim.');
        $this->reset(['assignmentId', 'notes', 'documents', 'photos', 'drawings']);
        $this->type = 'daily';
        $this->reportDate = now()->format('Y-m-d');
        $this->startTime = '';
        $this->endTime = '';
    }

    private function storeFiles(Report $report, array $files, string $category, int $projectId): void
    {
        $folderMap = [
            'daily_report' => 'Daily Report',
            'final_report' => 'Final Report',
            'foto' => 'Foto',
            'drawing' => 'Drawing',
        ];

        foreach ($files as $file) {
            if (! $file) {
                continue;
            }

            $folder = "project-files/{$projectId}/{$folderMap[$category]}";
            $path = $file->store($folder, 'local');

            $report->files()->create([
                'category' => $category,
                'disk_path' => $path,
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'size_bytes' => $file->getSize(),
            ]);
        }
    }
}
