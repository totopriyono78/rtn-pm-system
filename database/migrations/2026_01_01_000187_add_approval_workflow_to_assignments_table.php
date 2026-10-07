<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Skema approval dua tahap untuk penugasan teknisi (SRS: Lead Technician
     * membuat usulan jadwal -> PM/Administrator menyetujui/menolak).
     *
     * Default status 'disetujui' supaya SELURUH baris lama (dan penugasan
     * baru yang dibuat langsung oleh pemegang permission manage-projects/
     * approve-assignments) tetap langsung aktif tanpa approval, persis
     * seperti alur sebelum fitur ini ada -- tidak ada rework ke data lama.
     */
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->string('status')->default('disetujui')->after('notes');
            $table->foreignId('created_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropConstrainedForeignId('approved_by');
            $table->dropColumn(['status', 'approved_at', 'rejection_reason']);
        });
    }
};
