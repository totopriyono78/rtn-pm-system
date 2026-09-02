<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menyimpan pemenang (vendor + harga) yang dipilih Purchasing untuk satu
     * baris RFQ item. Ditambahkan lewat migration terpisah (bukan di
     * create_rfq_items_table) karena tabel vendor_quotation_items yang
     * menjadi tujuan FK ini baru ada setelah rfq_items dibuat.
     */
    public function up(): void
    {
        Schema::table('rfq_items', function (Blueprint $table) {
            $table->foreignId('awarded_vendor_quotation_item_id')
                ->nullable()
                ->after('qty')
                ->constrained('vendor_quotation_items')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rfq_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('awarded_vendor_quotation_item_id');
        });
    }
};
