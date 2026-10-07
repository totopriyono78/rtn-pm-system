<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Admin Purchase (SRS v2.0 modul 4.13): surat jalan & gatepass, dengan
 * tracking ekspedisi dasar (nama supir, plat kendaraan, tanggal).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_gatepasses', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('document_number')->nullable();
            $table->foreignId('purchase_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('vendor_name')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('vehicle_plate')->nullable();
            $table->date('gate_date');
            $table->text('notes')->nullable();
            $table->string('disk_path')->nullable();
            $table->string('original_name')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_gatepasses');
    }
};
