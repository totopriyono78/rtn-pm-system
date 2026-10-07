<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Asset Management -- bagian terakhir Finance & Accounting (SRS 4.14).
 * Register aset tetap milik PERUSAHAAN (kendaraan operasional, peralatan
 * teknis, peralatan kantor, dll) untuk tujuan akuntansi/depresiasi --
 * BUKAN `PumpAsset` (Master Unit Pompa) yang itu aset milik KLIEN yang
 * di-maintain PT RTN, entity berbeda sama sekali.
 *
 * Depresiasi garis lurus (straight-line) sederhana: `accumulated_depreciation`
 * disimpan (bukan dihitung ulang tiap request) dan diupdate lewat aksi
 * batch "Jalankan Depresiasi" (lihat FixedAsset::runMonthlyDepreciation()),
 * `last_depreciated_period` ('YYYY-MM') mencegah depresiasi dobel kalau
 * aksi batch dijalankan lebih dari sekali di periode yang sama.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category');
            $table->date('acquisition_date');
            $table->decimal('acquisition_cost', 15, 2);
            $table->decimal('salvage_value', 15, 2)->default(0);
            $table->unsignedSmallInteger('useful_life_years');
            $table->decimal('accumulated_depreciation', 15, 2)->default(0);
            $table->string('last_depreciated_period')->nullable();
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->string('location')->nullable();
            $table->string('status')->default('aktif');
            $table->timestamp('disposed_at')->nullable();
            $table->foreignId('disposed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('disposal_notes')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fixed_assets');
    }
};
