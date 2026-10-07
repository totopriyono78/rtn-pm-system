<?php

namespace App\Support;

use App\Models\ClientUser;
use App\Models\User;
use App\Notifications\WorkflowNotification;

/**
 * Helper tipis untuk mengirim WorkflowNotification (SRS 4.21, in-app +
 * email) ke satu user, ke semua user yang punya permission tertentu
 * (approver berbasis role/permission, bukan approver tunggal -- lihat
 * alasan di masing-masing titik pemanggilan di model), atau ke ClientUser
 * (Client Portal, guard 'client').
 */
class Notifier
{
    public static function user(?User $user, string $title, string $message, ?string $url = null, string $level = 'info'): void
    {
        if (! $user || ! $user->is_active) {
            return;
        }

        $user->notify(new WorkflowNotification($title, $message, $url, $level));
    }

    public static function permission(string $permission, string $title, string $message, ?string $url = null, string $level = 'info', ?int $exceptUserId = null): void
    {
        User::permission($permission)
            ->where('is_active', true)
            ->when($exceptUserId, fn ($q) => $q->where('id', '!=', $exceptUserId))
            ->get()
            ->each(fn (User $user) => $user->notify(new WorkflowNotification($title, $message, $url, $level)));
    }

    public static function client(?ClientUser $clientUser, string $title, string $message, ?string $url = null, string $level = 'info'): void
    {
        if (! $clientUser || ! $clientUser->is_active) {
            return;
        }

        $clientUser->notify(new WorkflowNotification($title, $message, $url, $level));
    }

    public static function clients(iterable $clientUsers, string $title, string $message, ?string $url = null, string $level = 'info'): void
    {
        foreach ($clientUsers as $clientUser) {
            static::client($clientUser, $title, $message, $url, $level);
        }
    }
}
