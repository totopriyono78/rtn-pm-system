<?php

namespace App\Livewire\Website;

use App\Models\WebsiteContactMessage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class WebsiteContactMessages extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public function mount(): void
    {
        abort_unless(auth()->user()->hasPermissionTo('manage-website'), 403);
    }

    public function render()
    {
        $messages = WebsiteContactMessage::when($this->statusFilter, fn ($q) => $q->where('status', $this->statusFilter))
            ->latest()
            ->paginate(10);

        return view('livewire.website.website-contact-messages', [
            'messages' => $messages,
        ]);
    }

    public function updateStatus(int $id, string $status): void
    {
        abort_unless(array_key_exists($status, WebsiteContactMessage::STATUSES), 422, 'Status tidak valid.');

        WebsiteContactMessage::findOrFail($id)->update(['status' => $status]);
        session()->flash('success', 'Status pesan diperbarui.');
    }

    public function delete(int $id): void
    {
        WebsiteContactMessage::findOrFail($id)->delete();
        session()->flash('success', 'Pesan dihapus.');
    }
}
