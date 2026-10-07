<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Nilai kontrak/pekerjaan proyek (sisi pendapatan) — berbeda dari
            // "budget" yang merupakan batas maksimal pengeluaran Purchasing.
            // Nullable: proyek boleh belum diisi nilainya.
            $table->decimal('project_value', 15, 2)->nullable()->after('budget');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('project_value');
        });
    }
};
