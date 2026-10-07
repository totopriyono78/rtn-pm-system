<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Project sekarang bisa lahir dari 2 jalur: langsung dari Contract
     * "spesifik" (contract_id diisi langsung), atau dari CustomerPurchaseOrder
     * hasil approval CustomerQuotation di jalur Contract "payung"
     * (customer_purchase_order_id diisi, contract-nya didapat tidak langsung
     * lewat rantai customerPurchaseOrder->customerQuotation->releaseOrder->contract).
     * Lihat Project::getContractAttribute().
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->foreignId('contract_id')->nullable()->after('unit_id')->constrained()->nullOnDelete();
            $table->foreignId('customer_purchase_order_id')->nullable()->after('contract_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_purchase_order_id');
            $table->dropConstrainedForeignId('contract_id');
        });
    }
};
