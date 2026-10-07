<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Piutang Lain-lain -- bagian "AR murni" dari Finance & Accounting (SRS
 * 4.14, lanjutan GL/Chart of Account). Piutang DI LUAR yang sudah
 * tercermin lewat Invoice (yang itu adalah piutang dagang/customer,
 * sudah tercatat via status Invoice sejak awal) -- ini untuk uang yang
 * diberikan ke pihak lain (karyawan di luar kasbon proyek, vendor,
 * pihak lain) yang harus dikembalikan, mis. pinjaman karyawan,
 * titipan/DP ke vendor di luar PO resmi, dsb.
 *
 * Alur: diajukan -> disetujui/ditolak/dibatalkan -> diberikan (uang
 * keluar, tercatat di Buku Kas/Bank + auto-posting jurnal GL) ->
 * (pembayaran kembali sebagian/penuh lewat other_receivable_payments)
 * -> lunas otomatis begitu total pembayaran >= jumlah diberikan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('other_receivables', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('debtor_type');
            $table->string('debtor_name');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->text('description');
            $table->decimal('amount', 15, 2);
            $table->date('due_date')->nullable();
            $table->string('status')->default('diajukan');
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('given_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('given_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('other_receivables');
    }
};
