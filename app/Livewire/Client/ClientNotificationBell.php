<?php

namespace App\Livewire\Client;

use Livewire\Component;

/**
 * Lonceng notifikasi di header Client Portal (SRS 4.21) -- versi ringkas
 * khusus guard 'client': dropdown saja (tanpa halaman "semua notifikasi"
 * terpisah, cukup untuk volume notifikasi client yang jauh lebih sedikit
 * dibanding user internal -- bisa ditambah kalau ke depan dibutuhkan).
 */
class ClientNotificationBell extends Component
{
    public bool $open = false;

    public function render()
    {
        $clientUser = auth('client')->user();

        return view('livewire.client.client-notification-bell', [
            'notifications' => $clientUser->notifications()->latest()->take(8)->get(),
            'unreadCount' => $clientUser->unreadNotifications()->count(),
        ]);
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function markAsRead(string $id): void
    {
        auth('client')->user()->notifications()->where('id', $id)->first()?->markAsRead();
    }

    public function markAllAsRead(): void
    {
        auth('client')->user()->unreadNotifications->markAsRead();
    }
}
