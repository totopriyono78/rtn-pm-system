<?php

namespace App\Livewire\Teknisi;

use App\Models\Assignment;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class SubmitReport extends Component
{
    use WithFileUploads;

    /**
     * Diisi otomatis lewat query string (?assignmentId=...) saat masuk dari tombol
     * "Isi Laporan" di halaman Jadwal Saya, supaya teknisi tidak perlu memilih lagi.
     * Tetap bisa diganti manual kalau salah klik.
     */
    #[Url]
    public string $assignmentId = '';

    public bool $showCompletedActivities = false;

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

    /**
     * Notifikasi flash session di layout tidak ikut ter-render ulang saat
     * Livewire memproses aksi lewat AJAX (yang di-refresh cuma markup
     * komponen ini), jadi konfirmasi sukses dipakai lewat modal di dalam
     * komponen sendiri supaya pasti terlihat oleh teknisi.
     */
    public bool $showSuccessModal = false;

    public function mount(): void
    {
        $this->reportDate = now()->format('Y-m-d');
    }

    public function render()
    {
        $assignments = Assignment::where('user_id', Auth::id())
            ->with('activity.project')
            ->when(! $this->showCompletedActivities, function ($q) {
                // Activity yang sudah "selesai" disembunyikan dari pilihan supaya daftar
                // tidak terus bertambah panjang, tapi penugasan yang masih berjalan tetap
                // muncul walau sudah pernah dilaporkan (mis. laporan harian untuk pekerjaan
                // multi-hari) — satu penugasan wajar punya beberapa laporan sebelum selesai.
                $q->where(function ($q2) {
                    $q2->whereHas('activity', fn ($qa) => $qa->where('status', '!=', 'selesai'));

                    if ($this->assignmentId !== '') {
                        $q2->orWhere('id', $this->assignmentId);
                    }
                });
            })
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

        if ($overlapping = $this->findOverlappingReport()) {
            $existingStart = \Illuminate\Support\Carbon::parse($overlapping->start_time)->format('H:i');
            $existingEnd = \Illuminate\Support\Carbon::parse($overlapping->end_time)->format('H:i');

            $this->addError(
                'endTime',
                "Jam kerja ini bentrok dengan laporan lain Anda di tanggal yang sama: \"{$overlapping->activity->name}\" pukul {$existingStart}–{$existingEnd}. Satu orang tidak mungkin mengerjakan 2 tugas di jam yang sama — periksa kembali jam atau tanggalnya."
            );

            return;
        }

        $assignment = Assignment::where('user_id', Auth::id())->with('activity')->findOrFail($this->assignmentId);

        // Presensi cuma diwajibkan kalau Activity-nya sudah diberi Site oleh PM —
        // supaya activity lama yang belum diberi Site (site_id nullable, dirilis
        // belakangan) tidak mendadak memblokir seluruh alur Submit Laporan yang
        // sudah berjalan.
        if ($assignment->activity->site_id && ! $assignment->attendance()->exists()) {
            $this->addError('assignmentId', 'Anda belum presensi (check-in) untuk penugasan ini. Lakukan check-in di menu Presensi terlebih dahulu.');

            return;
        }

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
        $this->showSuccessModal = true;
    }

    /**
     * Cari laporan lain milik teknisi yang sama, di tanggal yang sama, dengan
     * jam kerja yang beririsan (overlap) dengan jam yang baru diisi.
     *
     * Satu orang tidak mungkin mengerjakan 2 tugas di jam yang sama — tanpa
     * pengecekan ini, teknisi bisa lapor beberapa penugasan dengan jam yang
     * tumpang tindih di hari yang sama, dan total "Jam Aktual" hariannya bisa
     * membengkak tidak masuk akal (mis. 30 jam dalam 1 hari).
     */
    private function findOverlappingReport(): ?Report
    {
        $newStart = strtotime($this->reportDate.' '.$this->startTime);
        $newEnd = strtotime($this->reportDate.' '.$this->endTime);

        if ($newStart === false || $newEnd === false) {
            return null;
        }

        return Report::where('user_id', Auth::id())
            ->whereDate('report_date', $this->reportDate)
            ->with('activity')
            ->get()
            ->first(function (Report $r) use ($newStart, $newEnd) {
                $existingStart = strtotime($r->report_date->format('Y-m-d').' '.$r->start_time);
                $existingEnd = strtotime($r->report_date->format('Y-m-d').' '.$r->end_time);

                // Overlap kalau kedua rentang saling beririsan; bersinggungan tepat di
                // batas (mis. selesai 12:00 lalu mulai lagi 12:00) tetap diperbolehkan.
                return $existingStart < $newEnd && $existingEnd > $newStart;
            });
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
