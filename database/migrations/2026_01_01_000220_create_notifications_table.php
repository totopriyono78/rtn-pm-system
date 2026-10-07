<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Tabel notifikasi standar Laravel (polymorphic) -- menampung notifikasi
// User (guard 'web') maupun ClientUser (guard 'client') sekaligus lewat
// notifiable_type/notifiable_id, dipakai Notification & Audit Trail
// (SRS 4.21, keputusan scope eksplisit user 2026-10-06: channel In-app +
// Email). Lihat App\Notifications\WorkflowNotification & App\Support\Notifier.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
