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
     * "Status" sekarang berarti status laporan HARI INI untuk penugasan tsb
     * (bukan lagi "pernah lapor sekali pun") -- karena satu penugasan wajar
     * menerima laporan harian berkali-kali selama activity-nya masih berjalan
     * (lihat SubmitReport::render()). Default: yang belum lapor hari ini.
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
        $today = now()->toDateString();
        $hasCustomDateFilter = $this->dateFrom !== '' || $this->dateTo !== '';

        $assignments = Assignment::where('user_id', auth()->id())
            ->with('activity.project.unit.region')
            ->withCount(['reports as reported_today_count' => fn ($q) => $q->whereDate('report_date', $today)])
            // Default (tanpa filter tanggal manual): hanya penugasan yang activity-nya
            // masih berada di dalam periode start_date - end_date HARI INI. Contoh:
            // activity 1-5 Sep, hari ini 3 Sep -> masih masuk periode -> ditampilkan.
            // Activity tanpa start/end_date (tidak diisi) dianggap tidak terbatas
            // periode, jadi tetap ditampilkan.
            ->when(! $hasCustomDateFilter, function ($q) use ($today) {
                $q->whereHas('activity', function ($qa) use ($today) {
                    $qa->where(fn ($qd) => $qd->whereNull('start_date')->orWhereDate('start_date', '<=', $today))
                        ->where(fn ($qd) => $qd->whereNull('end_date')->orWhereDate('end_date', '>=', $today));
                });
            })
            ->when($this->dateFrom !== '', fn ($q) => $q->whereDate('scheduled_date', '>=', $this->dateFrom))
            ->when($this->dateTo !== '', fn ($q) => $q->whereDate('scheduled_date', '<=', $this->dateTo))
            // Catatan: sengaja pakai whereHas/whereDoesntHave (bukan having() atas
            // alias withCount), karena PostgreSQL tidak mengizinkan HAVING mengacu
            // ke alias kolom SELECT -- whereHas/whereDoesntHave portable di semua driver.
            ->when($this->status === 'belum', fn ($q) => $q->whereDoesntHave('reports', fn ($qr) => $qr->whereDate('report_date', $today)))
            ->when($this->status === 'sudah', fn ($q) => $q->whereHas('reports', fn ($qr) => $qr->whereDate('report_date', $today)))
            ->orderByDesc('scheduled_date')
            ->paginate(15);

        return view('livewire.teknisi.my-schedule', [
            'assignments' => $assignments,
            'today' => $today,
        ]);
    }
}
