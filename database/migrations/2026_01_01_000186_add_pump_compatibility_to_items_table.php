<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Master Sparepart/Material per Tipe Pompa & Engine -- SRS v2.0 modul 4.4.1.
 * Kolom opsional (bukan wajib -- material umum seperti sealant/baut tidak
 * perlu diisi) supaya sparepart yang memang spesifik ke tipe pompa/engine
 * tertentu bisa difilter saat menyusun BOQ.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->string('applicable_pump_type')->nullable()->after('category');
            $table->string('applicable_engine_type')->nullable()->after('applicable_pump_type');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['applicable_pump_type', 'applicable_engine_type']);
        });
    }
};
