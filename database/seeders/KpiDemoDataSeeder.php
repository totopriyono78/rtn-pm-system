<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Assignment;
use App\Models\Project;
use App\Models\Region;
use App\Models\Report;
use App\Models\User;
use App\Models\WorkLog;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Seeder tambahan untuk memperkaya sample data KPI DAN sekaligus MEMBERSIHKAN
 * data dummy lama yang jamnya overlap (peninggalan hasil klik-klik manual
 * testing di UI, dari sebelum validasi overlap ditambahkan ke SubmitReport).
 *
 * DemoDataSeeder hanya membuat 1 akun teknisi ("Joko Teknisi") dengan 1
 * laporan, sehingga Dashboard KPI Direktur tidak punya pembanding antar
 * karyawan. Seeder ini menambah 3 teknisi lagi dan mengisi jam kerja untuk
 * ~3-4 minggu terakhir (Senin-Jumat, dihitung RELATIF terhadap kapan seeder
 * ini dijalankan -- bukan tanggal tetap, supaya datanya tidak basi) dengan
 * pola yang sengaja dibuat bervariasi: ada yang produktif, ada yang sering
 * bolong -- supaya perbandingan KPI terlihat nyata.
 *
 * AMAN dijalankan berulang kali di database yang sudah live: setiap kali
 * dijalankan, seeder ini RESET dulu -- hapus bersih seluruh Report / WorkLog /
 * Assignment milik ke-4 akun teknisi demo di bawah (termasuk data lama yang
 * overlap/berantakan dari testing manual) -- baru mengisi ulang dengan data
 * yang bersih & tidak overlap. Tidak menyentuh user atau data lain di luar
 * ke-4 akun ini.
 *
 * Jalankan manual di server yang sudah live (tanpa perlu migrate:fresh):
 *   php artisan db:seed --class=KpiDemoDataSeeder --force
 */
class KpiDemoDataSeeder extends Seeder
{
    /** Email akun teknisi demo yang datanya boleh di-reset & diisi ulang seeder ini. */
    private const DEMO_TEKNISI_EMAILS = [
        'teknisi.jbb@rtn.co.id',
        'rudi.jbb@rtn.co.id',
        'dedi.jbb@rtn.co.id',
        'ahmad.jbt@rtn.co.id',
    ];

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

        // ===== Teknisi: pakai yang sudah ada (Joko), buat kalau belum ada (Rudi/Dedi/Ahmad) =====
        $joko = User::where('email', 'teknisi.jbb@rtn.co.id')->first();

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

        // ===== Bersihkan dulu SELURUH data kerja lama ke-4 akun demo ini =====
        // (termasuk data yang jamnya overlap/berantakan dari testing manual sebelumnya)
        $this->resetDemoWorkData();

        // ===== Isi ulang dengan data bersih & bervariasi -- Senin s.d. Jumat,
        // ~3-4 minggu terakhir dihitung dari hari seeder ini dijalankan =====
        $today = now()->startOfDay();
        $cursor = $today->copy()->subWeeks(3)->startOfWeek();

        while ($cursor->lte($today)) {
            if ($cursor->isWeekday()) {
                $dateStr = $cursor->toDateString();
                $isoWeekday = $cursor->dayOfWeekIso; // 1=Senin .. 5=Jumat

                // Joko: baseline solid, off setiap hari Rabu (contoh jadwal rutin di luar site).
                if ($joko && $isoWeekday !== 3) {
                    $this->logWork($joko, $actPelaksanaan, $dateStr, '08:00:00', '16:00:00', 'Pelaksanaan pekerjaan tangki.');
                }

                // Rudi: performa tinggi -- masuk penuh setiap hari kerja, jam lebih panjang.
                $this->logWork($rudi, $actPelaksanaan, $dateStr, '08:00:00', '17:00:00', 'Membantu pelaksanaan pekerjaan tangki.');

                // Dedi: performa rendah -- cuma sempat lapor Senin & Rabu, jam lebih pendek.
                // Contoh kasus nyata yang perlu dipantau tim Management/Direktur.
                if (in_array($isoWeekday, [1, 3], true)) {
                    $this->logWork($dedi, $actPelaksanaan, $dateStr, '09:00:00', '13:00:00', 'Support pelaksanaan pekerjaan tangki.');
                }

                // Ahmad: proyek & region berbeda (Commissioning Unit Baru, Region JBT), off tiap Jumat.
                if ($isoWeekday !== 5) {
                    $this->logWork($ahmad, $actFat, $dateStr, '08:00:00', '12:30:00', 'FAT commissioning unit baru.');
                }
            }

            $cursor->addDay();
        }

        $this->command?->info('KPI demo data (Joko, Rudi, Dedi, Ahmad) sudah di-reset & diisi ulang tanpa jam yang overlap.');
    }

    /**
     * Hapus bersih seluruh Report (+file fisiknya), WorkLog, dan Assignment
     * milik ke-4 akun teknisi demo -- termasuk data lama yang mungkin overlap
     * hasil klik-klik manual testing di UI. Tidak menyentuh user/data lain.
     *
     * WorkLog TIDAK ikut otomatis terhapus saat Report dihapus (relasinya
     * nullOnDelete di migration, bukan cascade) -- makanya dihapus manual di
     * sini dulu, supaya tidak ada WorkLog "yatim" yang masih ikut terhitung
     * di rekap jam kerja / KPI walau laporannya sendiri sudah tidak ada.
     */
    private function resetDemoWorkData(): void
    {
        $userIds = User::whereIn('email', self::DEMO_TEKNISI_EMAILS)->pluck('id');

        if ($userIds->isEmpty()) {
            return;
        }

        $reports = Report::whereIn('user_id', $userIds)->with('files')->get();

        foreach ($reports as $report) {
            foreach ($report->files as $file) {
                Storage::disk('local')->delete($file->disk_path);
            }
        }

        WorkLog::whereIn('user_id', $userIds)->delete();
        Report::whereIn('user_id', $userIds)->delete(); // cascade menghapus baris report_files
        Assignment::whereIn('user_id', $userIds)->delete();
    }

    /**
     * Buat Assignment + Report + WorkLog untuk satu hari kerja. Dipanggil
     * hanya dari data yang sudah dipastikan bersih (lihat resetDemoWorkData),
     * dan setiap teknisi cuma diberi SATU shift per tanggal di sini -- jadi
     * secara desain tidak mungkin menghasilkan jam yang overlap.
     */
    private function logWork(User $user, Activity $activity, string $date, string $start, string $end, string $notes): void
    {
        $assignment = Assignment::create([
            'activity_id' => $activity->id,
            'user_id' => $user->id,
            'scheduled_date' => $date,
            'notes' => $notes,
        ]);

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
    }
}
