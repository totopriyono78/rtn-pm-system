<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tipe proyek sesuai SRS 4.7 (Assessment/Preventive/Corrective/Overhaul/
     * Commissioning). Nullable & tidak diisi otomatis untuk data lama --
     * murni metadata tambahan, tidak dipakai di logic lain manapun saat ini.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('type')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
