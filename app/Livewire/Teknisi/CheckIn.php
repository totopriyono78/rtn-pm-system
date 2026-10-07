<?php

namespace App\Livewire\Teknisi;

use App\Models\Assignment;
use App\Models\Attendance;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Presensi teknisi: langkah TERPISAH sebelum Submit Laporan (bukan digabung
 * jadi satu klik saat submit laporan). Hanya valid kalau koordinat GPS saat
 * check-in berada dalam radius Site milik Activity terkait — di luar radius
 * ditolak keras di sini, teknisi diarahkan menghubungi PM/Admin untuk
 * override manual (lihat Admin\AttendanceOverrides).
 */
#[Layout('layouts.app')]
class CheckIn extends Component
{
    public ?string $lastErrorAssignmentId = null;

    public ?float $lastErrorDistance = null;

    public function render()
    {
        $today = now()->toDateString();

        $assignments = Assignment::where('user_id', Auth::id())
            ->approved()
            ->whereDate('scheduled_date', $today)
            ->with(['activity.project', 'activity.site', 'attendance'])
            ->orderBy('id')
            ->get();

        return view('livewire.teknisi.check-in', [
            'assignments' => $assignments,
            'today' => $today,
        ]);
    }

    public function doCheckIn(int $assignmentId, float $lat, float $lng, ?float $accuracy = null): void
    {
        $this->reset(['lastErrorAssignmentId', 'lastErrorDistance']);

        $assignment = Assignment::where('user_id', Auth::id())->approved()->with('activity.site')->findOrFail($assignmentId);

        if ($assignment->attendance()->exists()) {
            session()->flash('error', 'Anda sudah check-in untuk penugasan ini.');

            return;
        }

        $site = $assignment->activity->site;

        if (! $site) {
            session()->flash('error', 'Activity ini belum diberi Site oleh Project Manager, sehingga presensi tidak bisa divalidasi. Hubungi PM untuk melengkapi data Site.');

            return;
        }

        $distance = $site->distanceInMetersFrom($lat, $lng);
        // isWithinRadius() otomatis true kalau radius_check_enabled dimatikan
        // untuk site ini (keputusan client 2026-10-07) -- jarak tetap dihitung
        // & disimpan untuk catatan, tapi tidak lagi memblokir presensi.
        $withinRadius = $site->isWithinRadius($lat, $lng);

        if (! $withinRadius) {
            $this->lastErrorAssignmentId = (string) $assignmentId;
            $this->lastErrorDistance = $distance;

            session()->flash('error', sprintf(
                'Anda berada %s meter dari site "%s" (radius diizinkan %d meter). Presensi ditolak — mendekatlah ke lokasi, atau hubungi PM/Admin untuk override manual bila GPS di lokasi ini memang tidak akurat.',
                number_format($distance, 0),
                $site->name,
                $site->radius_meters
            ));

            return;
        }

        Attendance::create([
            'assignment_id' => $assignment->id,
            'user_id' => Auth::id(),
            'site_id' => $site->id,
            'checked_in_at' => now(),
            'latitude' => $lat,
            'longitude' => $lng,
            'accuracy_meters' => $accuracy,
            'distance_meters' => $distance,
            'is_within_radius' => true,
            'status' => 'valid',
        ]);

        session()->flash('success', 'Presensi berhasil dicatat. Anda bisa lanjut mengisi laporan untuk penugasan ini.');
    }

    public function geolocationError(string $message): void
    {
        session()->flash('error', 'Gagal mengambil lokasi GPS: '.$message.'. Pastikan izin lokasi browser diaktifkan.');
    }
}
