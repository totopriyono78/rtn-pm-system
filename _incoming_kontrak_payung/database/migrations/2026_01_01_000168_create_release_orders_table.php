<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Release Order (RO): permintaan pekerjaan konkret dari Customer di bawah
     * sebuah Contract "payung". Setiap RO diproses Project Controller menjadi
     * satu atau beberapa CustomerQuotation (bisa revisi) sebelum akhirnya
     * customer menerbitkan CustomerPurchaseOrder.
     */
    public function up(): void
    {
        Schema::create('release_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->string('ro_number');
            $table->date('ro_date');
            $table->text('notes')->nullable();
            $table->string('document_path')->nullable();
            $table->enum('status', ['baru', 'dalam_penawaran', 'selesai', 'batal'])->default('baru');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('release_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('release_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('site_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description');
            $table->decimal('qty', 12, 2);
            $table->string('unit')->nullable();
            $table->text('spec_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('release_order_items');
        Schema::dropIfExists('release_orders');
    }
};
