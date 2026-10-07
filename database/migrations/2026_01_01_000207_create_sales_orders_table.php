<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sales Order & Dokumen Teknis (SRS 4.6): dibuat Sales Support untuk
     * Customer yang sudah deal (baik dari Prospect yang "won" maupun
     * Customer lama yang order ulang pompa baru). Begitu dikonfirmasi
     * (status draft -> confirmed), OTOMATIS membentuk 1 Contract (tipe
     * spesifik) lalu 1 Project baru -- menyambung ke infrastruktur
     * Contract/Project Divisi 2 yang sudah ada, bukan membangun ulang
     * (lihat SalesOrder::confirm()). Sama seperti Contract/CustomerPurchaseOrder,
     * TIDAK ADA hard-delete setelah confirmed -- hanya status draft yang
     * boleh diubah/dibatalkan.
     */
    public function up(): void
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->string('so_number')->unique();
            // Opsional: Sales Order ini lahir dari Prospect mana (kalau ada).
            $table->foreignId('prospect_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            // Unit internal (Divisi 1) penanggung jawab -- kalau dikosongkan saat
            // dikonfirmasi, otomatis diisi Unit "Sales & Pengadaan Pompa Baru"
            // (lihat SalesOrder::resolveDefaultUnit()).
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->date('so_date');
            $table->text('description')->nullable();
            $table->decimal('total_value', 15, 2)->default(0);
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            // Contract + Project (lewat Contract->directProject) hasil konfirmasi.
            $table->foreignId('contract_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_orders');
    }
};
