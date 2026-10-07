<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * RFQ ke vendor sekarang bisa dipakai di 2 konteks: (1) seperti sebelumnya,
     * procurement riil untuk Project yang sudah berjalan (project_id diisi,
     * approve()-nya tetap menerbitkan Purchase Order ke vendor seperti biasa);
     * atau (2) RFQ "costing" untuk menyusun CustomerQuotation di jalur kontrak
     * payung, SEBELUM Project ada (release_order_id diisi, project_id kosong
     * — harga hasil RFQ ini dipakai sbg dasar harga penawaran ke customer,
     * TIDAK pernah menerbitkan Purchase Order riil, lihat
     * RequestForQuotation::submitForApproval()).
     *
     * project_id di-drop NOT NULL-nya lewat raw SQL karena doctrine/dbal
     * (dipakai Blueprint::change()) tidak terpasang di project ini.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE request_for_quotations ALTER COLUMN project_id DROP NOT NULL');

        Schema::table('request_for_quotations', function (Blueprint $table) {
            $table->foreignId('release_order_id')->nullable()->after('project_id')->constrained()->nullOnDelete();
        });

        DB::statement(
            'ALTER TABLE request_for_quotations ADD CONSTRAINT rfq_source_check '.
            'CHECK (project_id IS NOT NULL OR release_order_id IS NOT NULL)'
        );
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE request_for_quotations DROP CONSTRAINT IF EXISTS rfq_source_check');

        Schema::table('request_for_quotations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('release_order_id');
        });

        DB::statement('ALTER TABLE request_for_quotations ALTER COLUMN project_id SET NOT NULL');
    }
};
