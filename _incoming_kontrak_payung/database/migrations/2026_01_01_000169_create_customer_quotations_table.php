<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CustomerQuotation ("Penawaran"): disusun Project Controller dari item
     * ReleaseOrder + harga vendor hasil RFQ + item tambahan (jasa dll).
     * Kebalikan arah dari VendorQuotation (vendor -> RTN): ini RTN -> Customer.
     * Butuh approval internal Direktur sebelum dianggap terkirim ke customer.
     * Bisa direvisi (revision_no bertambah) kalau customer minta perubahan.
     */
    public function up(): void
    {
        Schema::create('customer_quotations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('release_order_id')->constrained()->cascadeOnDelete();
            $table->string('code')->unique();
            $table->unsignedInteger('revision_no')->default(1);
            $table->enum('status', [
                'draft', 'submitted', 'approved', 'rejected', 'customer_accepted', 'customer_rejected',
            ])->default('draft');
            $table->decimal('total', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('customer_decision_at')->nullable();
            $table->text('customer_decision_note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('customer_quotation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_quotation_id')->constrained()->cascadeOnDelete();
            // Nullable: PC boleh menambah item di luar Release Order (jasa dll).
            $table->foreignId('release_order_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('description');
            $table->decimal('qty', 12, 2);
            $table->string('unit')->nullable();
            // Referensi bebas ke RFQ/penawaran vendor yang jadi dasar harga (kalkulasi HPP).
            $table->string('vendor_reference_note')->nullable();
            $table->decimal('unit_price', 15, 2);
            $table->decimal('subtotal', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_quotation_items');
        Schema::dropIfExists('customer_quotations');
    }
};
