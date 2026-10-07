<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Client Portal (SRS 4.3) -- akun login terpisah untuk customer, memakai
 * guard auth 'client' sendiri (bukan tabel `users` yang dipakai karyawan
 * internal). Satu customer boleh punya lebih dari satu akun client (misal
 * beberapa PIC customer yang berbeda).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('client_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('client_users');
    }
};
