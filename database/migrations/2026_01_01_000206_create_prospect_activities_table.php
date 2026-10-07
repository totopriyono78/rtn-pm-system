<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Log aktivitas per Prospect (SRS 4.5) -- DIPISAH PER TAHAP, artinya
     * setiap kali Prospect pindah stage (prospek->qualified->proposal->
     * negosiasi->won/lost) tercatat sebagai 1 baris log tersendiri (type =
     * stage_change, from_status/to_status terisi), terpisah dari log
     * aktivitas manual (call/meeting/email/note) yang dicatat Marketing.
     * Jadi riwayat pipeline dan riwayat komunikasi tetap satu timeline yang
     * sama tapi bisa dibedakan jenisnya.
     */
    public function up(): void
    {
        Schema::create('prospect_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prospect_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['stage_change', 'call', 'meeting', 'email', 'note'])->default('note');
            $table->string('from_status')->nullable();
            $table->string('to_status')->nullable();
            $table->text('notes')->nullable();
            $table->dateTime('activity_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospect_activities');
    }
};
