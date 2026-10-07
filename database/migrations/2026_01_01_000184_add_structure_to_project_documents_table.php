<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Document Management Terstruktur -- SRS v2.0 modul 4.10. Menambahkan folder
 * taxonomy (category), versioning, dan flag visibilitas untuk Client Portal
 * nanti (belum dipakai di mana pun -- disiapkan supaya migrasi skema tidak
 * perlu diulang begitu Client Portal mulai dibangun).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_documents', function (Blueprint $table) {
            $table->string('category')->default('lainnya')->after('project_id');
            $table->unsignedInteger('version')->default(1)->after('category');
            $table->foreignId('parent_document_id')->nullable()->after('version')->constrained('project_documents')->nullOnDelete();
            $table->boolean('is_client_visible')->default(false)->after('parent_document_id');
        });
    }

    public function down(): void
    {
        Schema::table('project_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_document_id');
            $table->dropColumn(['category', 'version', 'is_client_visible']);
        });
    }
};
