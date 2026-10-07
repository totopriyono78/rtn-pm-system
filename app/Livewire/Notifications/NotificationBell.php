<?php

namespace App\Livewire\Notifications;

use Livewire\Component;

/**
 * Lonceng notifikasi di header internal (SRS 4.21) -- dropdown 8
 * notifikasi terbaru + badge jumlah belum dibaca. Di-poll setiap 30 detik
 * supaya badge ikut update tanpa refresh manual, tanpa perlu
 * websocket/Reverb (konsisten -- tidak ada infrastruktur realtime lain
 * di app ini).
 */
class NotificationBell extends Component
{
    public bool $open = false;

    public function render()
    {
        $user = auth()->user();

        return view('livewire.notifications.notification-bell', [
            'notifications' => $user->notifications()->latest()->take(8)->get(),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function markAsRead(string $id): void
    {
        auth()->user()->notifications()->where('id', $id)->first()?->markAsRead();
    }

    public function markAllAsRead(): void
    {
        auth()->user()->unreadNotifications->markAsRead();
    }
}
