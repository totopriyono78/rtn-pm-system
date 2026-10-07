<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Dokumen teknis Sales Order (SRS 4.6) -- datasheet pompa, gambar
     * teknis/drawing, surat penawaran harga, dsb. Pola sama dengan
     * ProjectDocument (disk_path/original_name/mime_type/size_bytes) supaya
     * konsisten dengan document-management yang sudah ada.
     */
    public function up(): void
    {
        Schema::create('sales_order_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('category', ['datasheet', 'drawing', 'quotation', 'lainnya'])->default('lainnya');
            $table->string('disk_path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_order_documents');
    }
};
