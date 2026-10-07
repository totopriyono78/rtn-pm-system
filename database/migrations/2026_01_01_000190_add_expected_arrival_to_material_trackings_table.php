<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dasar reminder H-7 (SRS 4.12): tanggal estimasi barang tiba, diisi
     * manual oleh Purchasing/Admin Purchase saat/stelah PO terbit. Nullable --
     * tanpa tanggal ini, baris tracking tidak masuk hitungan reminder apa pun
     * (opt-in, tidak ada rework data lama).
     */
    public function up(): void
    {
        Schema::table('material_trackings', function (Blueprint $table) {
            $table->date('expected_arrival_date')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('material_trackings', function (Blueprint $table) {
            $table->dropColumn('expected_arrival_date');
        });
    }
};
