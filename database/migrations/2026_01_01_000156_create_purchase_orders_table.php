<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Purchase Order: diterbitkan otomatis (satu PO per vendor) saat Direktur
     * meng-approve hasil pemilihan pemenang pada sebuah RFQ.
     */
    public function up(): void
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('request_for_quotation_id')->constrained('request_for_quotations')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['issued', 'cancelled'])->default('issued');
            $table->decimal('total', 16, 2)->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
