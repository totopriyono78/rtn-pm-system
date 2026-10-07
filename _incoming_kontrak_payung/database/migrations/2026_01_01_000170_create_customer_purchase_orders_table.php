<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CustomerPurchaseOrder: PO yang diterbitkan CUSTOMER ke PT RTN (arah
     * kebalikan dari PurchaseOrder yang sudah ada, yaitu PO PT RTN ke Vendor
     * — sengaja diberi nama entity berbeda supaya tidak tertukar).
     * Mencatat PO ini men-trigger pembuatan Project baru (lihat
     * CustomerPurchaseOrder::convertToProject()).
     */
    public function up(): void
    {
        Schema::create('customer_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_quotation_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('po_number');
            $table->date('po_date');
            $table->decimal('value', 15, 2);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('document_path')->nullable();
            $table->enum('status', ['received', 'converted', 'cancelled'])->default('received');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_purchase_orders');
    }
};
