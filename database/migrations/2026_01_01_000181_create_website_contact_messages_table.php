<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pesan masuk dari form Kontak di Company Website (lead mentah). Belum
     * terhubung ke modul CRM (Divisi 1, belum dibangun) -- saat CRM ada,
     * baris di sini bisa dipindahkan/ditautkan jadi Lead.
     */
    public function up(): void
    {
        Schema::create('website_contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('subject')->nullable();
            $table->text('message');
            $table->enum('status', ['baru', 'dihubungi', 'selesai'])->default('baru');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_contact_messages');
    }
};
