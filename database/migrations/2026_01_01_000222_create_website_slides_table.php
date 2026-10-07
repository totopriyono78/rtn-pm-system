<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Slide/carousel beranda Company Website (pilar publik, SRS v2.0 bab 4.2) --
 * konten yang bergeser otomatis di hero beranda, bisa diisi teks dan/atau gambar.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('website_slides', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('image_path')->nullable();
            $table->string('link_label')->nullable();
            $table->string('link_url')->nullable();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('website_slides');
    }
};
