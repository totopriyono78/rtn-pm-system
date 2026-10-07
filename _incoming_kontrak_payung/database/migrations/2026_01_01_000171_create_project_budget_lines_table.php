<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Breakdown rencana pengeluaran per proyek per kategori (material,
     * transportasi, akomodasi, administrasi, lain-lain) — melengkapi kolom
     * projects.budget yang tetap jadi angka total (tidak dihapus, supaya
     * accessor used_budget/remaining_budget yang sudah ada tidak berubah).
     */
    public function up(): void
    {
        Schema::create('project_budget_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->enum('category', ['material', 'transportasi', 'akomodasi', 'administrasi', 'lain_lain']);
            $table->decimal('planned_amount', 15, 2)->default(0);
            $table->string('notes')->nullable();
            $table->timestamps();
            $table->unique(['project_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_budget_lines');
    }
};
