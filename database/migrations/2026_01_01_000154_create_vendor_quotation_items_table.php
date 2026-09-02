<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Harga yang ditawarkan satu vendor untuk satu baris RFQ item. unit_price
     * bisa diperbarui Purchasing untuk mencatat hasil negosiasi (harga
     * berjalan/terkini) — qty disalin dari rfq_items saat baris ini dibuat.
     */
    public function up(): void
    {
        Schema::create('vendor_quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_quotation_id')->constrained('vendor_quotations')->cascadeOnDelete();
            $table->foreignId('rfq_item_id')->constrained('rfq_items')->cascadeOnDelete();
            $table->decimal('qty', 12, 2);
            $table->decimal('unit_price', 14, 2);
            $table->decimal('subtotal', 16, 2);
            $table->timestamps();

            $table->unique(['vendor_quotation_id', 'rfq_item_id'], 'vqi_unique_line');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_quotation_items');
    }
};
