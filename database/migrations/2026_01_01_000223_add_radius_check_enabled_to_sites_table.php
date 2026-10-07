<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Keputusan client 2026-10-07: radius presensi "bisa diset sangat luas bila
 * ingin presensi dimana saja, atau bisa dimatikan pakai radius atau tidak."
 * Dua hal terpisah: (1) radius_meters tetap ada, batas atas validasinya
 * dinaikkan jauh (lihat ManageLocations::saveSite, max 100000 meter / 100km)
 * supaya bisa diset "sangat luas"; (2) kolom baru ini untuk kasus "dimatikan
 * sama sekali" -- kalau false, CheckIn::doCheckIn() melewati pengecekan
 * jarak sepenuhnya (presensi diterima dari mana saja), tanpa perlu set
 * radius ke angka yang sangat besar sebagai workaround.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->boolean('radius_check_enabled')->default(true)->after('radius_meters');
        });
    }

    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn('radius_check_enabled');
        });
    }
};
