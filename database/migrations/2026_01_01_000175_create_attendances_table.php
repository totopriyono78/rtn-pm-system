<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Presensi teknisi: check-in TERPISAH sebelum Submit Laporan (bukan
     * digabung ke form laporan). Satu Assignment hanya boleh punya satu
     * check-in. Valid hanya kalau distance_meters <= radius site terkait
     * (dihitung pakai formula Haversine saat check-in) — kalau di luar
     * radius, ditolak keras di sisi server kecuali di-override PM/Admin.
     */
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('checked_in_at');
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('accuracy_meters', 8, 2)->nullable();
            $table->decimal('distance_meters', 10, 2)->nullable();
            $table->boolean('is_within_radius')->default(false);
            $table->enum('status', ['valid', 'overridden'])->default('valid');
            $table->foreignId('override_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('override_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
