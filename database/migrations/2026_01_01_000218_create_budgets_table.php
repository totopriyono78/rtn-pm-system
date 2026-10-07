<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Budgeting formal -- bagian terakhir Finance & Accounting (SRS 4.14).
 * Anggaran TAHUNAN per akun Chart of Account (BUKAN pengganti
 * `Project.budget`/`ProjectBudgetLine` yang sudah ada -- itu anggaran
 * per PROYEK individual dibandingkan ke Purchase Order; ini anggaran
 * level PERUSAHAAN per akun COA dibandingkan ke jurnal GL yang sudah
 * posted, memanfaatkan auto-posting yang baru selesai dibangun).
 *
 * Alur: draft (bebas diubah Administrator) -> disetujui (oleh Direktur,
 * lock permanen -- konsisten "tidak ada edit setelah final") atau
 * dibatalkan (hanya dari draft).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->unsignedSmallInteger('period_year');
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
