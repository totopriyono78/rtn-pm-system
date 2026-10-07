<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Log Safety Talk / Toolbox Meeting terstruktur -- SRS 4.9. Diisi
     * teknisi/Lead Technician sebelum atau selama bekerja di lapangan, bisa
     * dilihat PM lewat tab Laporan di Detail Proyek untuk cek kepatuhan K3.
     */
    public function up(): void
    {
        Schema::create('safety_talks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conducted_by')->constrained('users')->cascadeOnDelete();
            $table->date('meeting_date');
            $table->string('topic');
            $table->text('attendees')->nullable();
            $table->text('notes')->nullable();
            $table->string('photo_disk_path')->nullable();
            $table->string('photo_original_name')->nullable();
            $table->string('photo_mime_type')->nullable();
            $table->unsignedBigInteger('photo_size_bytes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('safety_talks');
    }
};
