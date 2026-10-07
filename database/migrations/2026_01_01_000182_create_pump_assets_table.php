<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pump_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->string('serial_number')->nullable();
            $table->string('pump_type');
            $table->string('pump_model')->nullable();
            $table->string('pump_capacity')->nullable();
            $table->string('engine_type')->nullable();
            $table->string('engine_model')->nullable();
            $table->date('install_date')->nullable();
            $table->date('last_service_date')->nullable();
            $table->string('condition')->default('baik');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pump_assets');
    }
};
