<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Invoice & Billing -- SRS 4.15, modul pertama Fase 2 (Finance & SDM).
     * Lanjutan alami dari siklus Divisi 2 yang sudah ada: Pelaksanaan ->
     * BAPP RO -> BAL Bulanan -> Penagihan. Satu Invoice menagih SATU Project
     * (jumlah lump-sum per periode, sesuai praktik penagihan jasa service
     * yang direferensikan ke dokumen BAPP RO/BAL Bulanan terkait -- bukan
     * line-item per material seperti PurchaseOrder).
     */
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_document_id')->nullable()->constrained('project_documents')->nullOnDelete();
            $table->string('invoice_number')->unique();
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->string('period_label')->nullable();
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_percent', 5, 2)->default(11.00);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
