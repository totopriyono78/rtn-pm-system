<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Baris material/jasa yang dibutuhkan pada satu RFQ (belum ada harga —
     * harga baru muncul lewat penawaran tiap vendor di vendor_quotation_items).
     * Kolom pemenang (awarded_vendor_quotation_item_id) ditambahkan lewat
     * migration terpisah setelah tabel vendor_quotation_items ada, karena
     * relasinya saling silang (rfq_items <-> vendor_quotation_items).
     */
    public function up(): void
    {
        Schema::create('rfq_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_for_quotation_id')->constrained('request_for_quotations')->cascadeOnDelete();
            $table->foreignId('item_id')->constrained();
            $table->decimal('qty', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfq_items');
    }
};
