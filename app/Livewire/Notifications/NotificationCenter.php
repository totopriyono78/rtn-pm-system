<?php

namespace App\Livewire\Notifications;

use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Halaman "Semua Notifikasi" (SRS 4.21) -- daftar lengkap notifikasi user
 * internal yang login, dengan filter semua/belum dibaca. Pasangan dari
 * dropdown singkat di NotificationBell.
 */
#[Layout('layouts.app')]
class NotificationCenter extends Component
{
    use WithPagination;

    public string $filter = 'all';

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();

        $query = $user->notifications();
        if ($this->filter === 'unread') {
            $query->whereNull('read_at');
        }

        return view('livewire.notifications.notification-center', [
            'notifications' => $query->latest()->paginate(15),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    public function setFilter(string $filter): void
    {
        $this->filter = in_array($filter, ['all', 'unread'], true) ? $filter : 'all';
        $this->resetPage();
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
