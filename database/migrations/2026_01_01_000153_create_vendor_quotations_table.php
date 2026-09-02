<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Satu penawaran yang masuk dari satu vendor untuk satu RFQ. Purchasing
     * boleh menginput lebih dari satu vendor_quotation per RFQ untuk
     * dibandingkan (lihat vendor_quotation_items untuk rincian harga per item).
     */
    public function up(): void
    {
        Schema::create('vendor_quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_for_quotation_id')->constrained('request_for_quotations')->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('reference_number')->nullable();
            $table->date('quoted_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_quotations');
    }
};
