<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Prospect ("lead"): calon customer yang masih dalam tahap CRM & Sales
     * Pipeline (SRS 4.5), SEBELUM resmi jadi Customer. Entity terpisah dari
     * Customer (bukan reuse langsung) karena data & statusnya jauh lebih
     * cair (banyak lead gagal/batal) dan tidak semua kolom Customer relevan
     * di tahap ini. Begitu status jadi "won", 1 Customer baru otomatis
     * dibuat (atau dihubungkan ke Customer yang sudah ada) — lihat
     * Prospect::win().
     */
    public function up(): void
    {
        Schema::create('prospects', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('company_name');
            $table->string('contact_name')->nullable();
            $table->string('contact_phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->text('address')->nullable();
            // Sumber lead -- referral, website, pameran, cold-call, lainnya (bebas isi).
            $table->string('source')->nullable();
            $table->enum('status', ['prospek', 'qualified', 'proposal', 'negosiasi', 'won', 'lost'])->default('prospek');
            $table->decimal('estimated_value', 15, 2)->nullable();
            $table->text('notes')->nullable();
            // Diisi kalau status = lost.
            $table->text('lost_reason')->nullable();
            $table->timestamp('won_at')->nullable();
            $table->timestamp('lost_at')->nullable();
            // Customer hasil konversi begitu prospect ini "won".
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prospects');
    }
};
