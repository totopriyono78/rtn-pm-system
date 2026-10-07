<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Digital signature in-house (tanpa pihak ketiga seperti PrivyID): user
     * menyimpan tanda tangan (hasil gambar canvas atau file upload) di
     * UserSignature, lalu dipasang ke dokumen apa pun lewat DocumentSignature
     * (polymorphic — generik, tidak perlu kolom TTD terpisah di tiap tabel
     * dokumen). Catatan: ini paraf digital internal untuk kebutuhan
     * operasional, BUKAN tanda tangan elektronik bersertifikat setara
     * UU ITE/PP 71/2019.
     */
    public function up(): void
    {
        Schema::create('user_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['drawn', 'uploaded']);
            $table->string('file_path');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('document_signatures', function (Blueprint $table) {
            $table->id();
            $table->string('signable_type');
            $table->unsignedBigInteger('signable_id');
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('user_signature_id')->nullable()->constrained('user_signatures')->nullOnDelete();
            $table->string('role_label')->nullable();
            $table->timestamp('signed_at');
            $table->string('ip_address')->nullable();
            $table->timestamps();
            $table->index(['signable_type', 'signable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_signatures');
        Schema::dropIfExists('user_signatures');
    }
};
