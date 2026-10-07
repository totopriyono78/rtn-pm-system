<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Baris pembayaran kembali (bisa bertahap/dicicil) atas satu Piutang
 * Lain-lain. Setiap baris tercatat sebagai transaksi masuk di Buku
 * Kas/Bank + auto-posting jurnal GL (Debit Kas/Bank, Kredit Piutang
 * Lain-lain). Tidak ada edit/delete -- konsisten pola CashBankTransaction.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('other_receivable_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('other_receivable_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cash_bank_account_id')->constrained()->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('other_receivable_payments');
    }
};
