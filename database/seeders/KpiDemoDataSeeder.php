<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Assignment;
use App\Models\Project;
use App\Models\Region;
use App\Models\Report;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeder tambahan untuk memperkaya sample data KPI.
 *
 * DemoDataSeeder hanya membuat 1 akun teknisi ("Joko Teknisi") dengan 1
 * laporan, sehingga Dashboard KPI Direktur tidak punya pembanding antar
 * karyawan (chart "Jam Kerja Hari Ini" cuma 1 batang, tabel akumulasi cuma
 * 1 baris). Seeder ini menambah beberapa teknisi lagi dengan jam kerja yang
 * sengaja dibuat bervariasi (ada yang produktif, ada yang jarang lapor)
 * supaya perbandingan KPI terlihat nyata.
 *
 * Beda dengan DemoDataSeeder/RolePermissionSeeder (didesain untuk instalasi
 * baru), seeder ini AMAN dijalankan berulang / di database yang sudah live
 * — semua pakai firstOrCreate / pengecekan "sudah ada belum" sebelum insert.
 *
 * Jalankan manual di server yang sudah live (tanpa perlu migrate:fresh):
 *   php artisan db:seed --class=KpiDemoDataSeeder --force
 */
class KpiDemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $jbb = Region::where('code', 'JBB')->first();
        $jbt = Region::where('code', 'JBT')->first();

        if (! $jbb || ! $jbt) {
            $this->command?->warn('Region JBB/JBT belum ada — jalankan DemoDataSeeder dahulu.');

            return;
        }

        $project1 = Project::where('name', 'Performance Test Tangki 31T-101')->first();
        $project3 = Project::where('name', 'Commissioning Unit Baru')->first();

        $actPelaksanaan = $project1?->activities()->where('name', 'Pelaksanaan Pekerjaan')->first();
        $actFat = $project3?->activities()->where('name', 'FAT')->first();

        if (! $actPelaksanaan || ! $actFat) {
            $this->command?->warn('Activity contoh belum ditemukan — jalankan DemoDataSeeder dahulu.');

            return;
        }

        // ===== Teknisi tambahan =====
        $rudi = User::firstOrCreate(
            ['email' => 'rudi.jbb@rtn.co.id'],
            ['name' => 'Rudi Hartono', 'password' => 'password', 'is_active' => true]
        );
        if (! $rudi->hasRole('Teknisi')) {
            $rudi->assignRole('Teknisi');
        }
        $rudi->regions()->syncWithoutDetaching([$jbb->id]);

        $dedi = User::firstOrCreate(
            ['email' => 'dedi.jbb@rtn.co.id'],
            ['name' => 'Dedi Setiawan', 'password' => 'password', 'is_active' => true]
        );
        if (! $dedi->hasRole('Teknisi')) {
            $dedi->assignRole('Teknisi');
        }
        $dedi->regions()->syncWithoutDetaching([$jbb->id]);

        $ahmad = User::firstOrCreate(
            ['email' => 'ahmad.jbt@rtn.co.id'],
            ['name' => 'Ahmad Fauzi', 'password' => 'password', 'is_active' => true]
        );
        if (! $ahmad->hasRole('Teknisi')) {
            $ahmad->assignRole('Teknisi');
        }
        $ahmad->regions()->syncWithoutDetaching([$jbt->id]);

        $joko = User::where('email', 'teknisi.jbb@rtn.co.id')->first();

        // Helper: buat Assignment + Report + WorkLog untuk satu hari kerja.
        // Melewati kalau user tsb sudah punya report untuk activity+tanggal
        // yang sama, supaya seeder ini aman dijalankan berulang kali.
        $logWork = function (User $user, Activity $activity, string $date, string $start, string $end, string $notes): void {
            $alreadyLogged = Report::where('user_id', $user->id)
                ->where('activity_id', $activity->id)
                ->whereDate('report_date', $date)
                ->exists();

            if ($alreadyLogged) {
                return;
            }

            $assignment = Assignment::firstOrCreate(
                ['activity_id' => $activity->id, 'user_id' => $user->id, 'scheduled_date' => $date],
                ['notes' => $notes]
            );

            $report = Report::create([
                'activity_id' => $activity->id,
                'user_id' => $user->id,
                'assignment_id' => $assignment->id,
                'type' => 'daily',
                'report_date' => $date,
                'start_time' => $start,
                'end_time' => $end,
                'notes' => $notes,
            ]);

            $report->workLog()->create([
                'user_id' => $user->id,
                'activity_id' => $activity->id,
                'project_id' => $activity->project_id,
                'log_date' => $report->report_date,
                'start_time' => $report->start_time,
                'end_time' => $report->end_time,
                'duration_minutes' => $report->duration_minutes,
            ]);
        };

        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();

        // Joko: sudah ada laporan hari ini dari DemoDataSeeder -> tambah kemarin.
        if ($joko) {
            $logWork($joko, $actPelaksanaan, $yesterday, '08:00:00', '15:00:00', 'Lanjutan pelaksanaan pekerjaan.');
        }

        // Rudi: performa tinggi, kerja penuh 2 hari berturut-turut.
        $logWork($rudi, $actPelaksanaan, $today, '08:00:00', '14:30:00', 'Membantu pelaksanaan pekerjaan tangki.');
        $logWork($rudi, $actPelaksanaan, $yesterday, '08:00:00', '16:00:00', 'Membantu pelaksanaan pekerjaan tangki.');

        // Dedi: hanya sempat lapor kemarin, hari ini belum ada laporan masuk —
        // contoh kasus nyata yang perlu dipantau tim Management/Direktur.
        $logWork($dedi, $actPelaksanaan, $yesterday, '09:00:00', '14:00:00', 'Support pelaksanaan pekerjaan tangki.');

        // Ahmad: teknisi di proyek & region berbeda (Commissioning Unit Baru, Region JBT).
        $logWork($ahmad, $actFat, $today, '08:00:00', '12:00:00', 'FAT commissioning unit baru.');
        $logWork($ahmad, $actFat, $yesterday, '08:00:00', '12:30:00', 'FAT commissioning unit baru.');
    }
}
