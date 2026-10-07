<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Contract dengan Customer. Dua tipe: "spesifik" (nilai & pekerjaan sudah
     * pasti sejak kontrak diteken, langsung membentuk 1 Project) dan "payung"
     * (nilai adalah plafon maksimal, Project baru lahir belakangan lewat
     * Release Order -> Penawaran -> PO Customer, lihat release_orders dkk).
     */
    public function up(): void
    {
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            // Unit internal (region) penanggung jawab kontrak ini — dipakai untuk
            // mewarisi scoping visibility Project yang lahir dari kontrak ini.
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->string('contract_number')->unique();
            $table->enum('contract_type', ['spesifik', 'payung']);
            $table->date('contract_date');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('scope_description')->nullable();
            // Diisi utk tipe spesifik: nilai pekerjaan yang sudah pasti.
            $table->decimal('fixed_value', 15, 2)->nullable();
            // Diisi utk tipe payung: nilai plafon maksimal kontrak.
            $table->decimal('max_value', 15, 2)->nullable();
            $table->enum('status', ['draft', 'active', 'expired', 'terminated'])->default('draft');
            $table->string('document_path')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('contract_site', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['contract_id', 'site_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_site');
        Schema::dropIfExists('contracts');
    }
};
