<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel singleton (selalu 1 baris, id=1) untuk pengaturan perhitungan KPI.
     * Dibuat sebagai tabel (bukan config/.env) supaya bisa diubah Administrator
     * langsung dari halaman Admin tanpa perlu redeploy aplikasi.
     */
    public function up(): void
    {
        Schema::create('kpi_settings', function (Blueprint $table) {
            $table->id();

            // 'average' = bandingkan tiap karyawan terhadap rata-rata tim (relatif).
            // 'target'  = bandingkan terhadap angka target tetap yang ditentukan perusahaan.
            $table->string('mode')->default('average');

            // Target jam kerja minimal (dipakai penuh saat mode = target; saat
            // mode = average dipakai sebagai nilai default/awal saja).
            $table->decimal('min_hours_day', 5, 2)->default(8);
            $table->decimal('min_hours_week', 5, 2)->default(40);
            $table->decimal('min_hours_month', 6, 2)->default(160);

            // Hanya dipakai saat mode = average: berapa persen dari rata-rata tim
            // yang jadi ambang batas "di bawah rata-rata". 100 = persis di bawah
            // rata-rata langsung ditandai; 80 = baru ditandai kalau di bawah 80%
            // dari rata-rata (beri toleransi).
            $table->unsignedTinyInteger('average_margin_percent')->default(100);

            // Karyawan yang belum lapor sama sekali pada periode berjalan (0 jam)
            // ikut dihitung ke rata-rata & ditampilkan di daftar, atau tidak.
            $table->boolean('include_zero_hour_employees')->default(false);

            // Tampilkan/matikan seluruh penandaan "di bawah target/rata-rata" di
            // Dashboard KPI.
            $table->boolean('show_threshold_badges')->default(true);

            $table->foreignId('updated_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_settings');
    }
};
