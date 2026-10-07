<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_bank_transactions', function (Blueprint $table) {
            $table->foreignId('other_receivable_id')->nullable()->after('cash_advance_id')->constrained('other_receivables')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cash_bank_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('other_receivable_id');
        });
    }
};
