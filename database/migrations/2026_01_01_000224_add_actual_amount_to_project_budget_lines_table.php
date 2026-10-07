<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Keputusan client 2026-10-07: role Cost Control (baru, lihat
 * RolePermissionSeeder) MENGELOLA, bukan cuma melihat, budget & actual cost
 * per proyek. Kolom `planned_amount` yang sudah ada dipakai untuk budget;
 * kolom baru ini untuk actual cost yang diinput manual oleh Cost Control --
 * terpisah dari Project::used_budget (yang dihitung otomatis dari total
 * Purchase Order issued) karena tidak semua pengeluaran riil tercatat lewat
 * PO (lihat App\Livewire\CostControl\ManageProjectCost).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_budget_lines', function (Blueprint $table) {
            $table->decimal('actual_amount', 15, 2)->default(0)->after('planned_amount');
        });
    }

    public function down(): void
    {
        Schema::table('project_budget_lines', function (Blueprint $table) {
            $table->dropColumn('actual_amount');
        });
    }
};
