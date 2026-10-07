<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Entry Voucher otomatis/auto-posting (SRS 4.14, lanjutan GL/Chart of
 * Account) -- menambah jejak sumber transaksi ke jurnal yang dibuat
 * otomatis oleh sistem (bukan manual lewat form Jurnal Umum), meniru
 * pola invoice_id/cash_advance_id/purchase_order_id yang sudah ada di
 * cash_bank_transactions. `source` membedakan jurnal manual (default,
 * tidak berubah untuk data lama) dari otomatis -- dipakai UI untuk
 * menampilkan badge dan menyembunyikan aksi Ubah/Posting/Batalkan
 * (jurnal otomatis dibuat langsung berstatus posted).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->string('source')->default('manual')->after('status');
            $table->foreignId('invoice_id')->nullable()->after('source')->constrained('invoices')->nullOnDelete();
            $table->foreignId('purchase_order_id')->nullable()->after('invoice_id')->constrained('purchase_orders')->nullOnDelete();
            $table->foreignId('cash_advance_id')->nullable()->after('purchase_order_id')->constrained('cash_advances')->nullOnDelete();
            $table->foreignId('payroll_run_id')->nullable()->after('cash_advance_id')->constrained('payroll_runs')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('invoice_id');
            $table->dropConstrainedForeignId('purchase_order_id');
            $table->dropConstrainedForeignId('cash_advance_id');
            $table->dropConstrainedForeignId('payroll_run_id');
            $table->dropColumn('source');
        });
    }
};
