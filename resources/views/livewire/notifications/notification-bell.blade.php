<div class="relative" wire:poll.30s>
    <button wire:click="toggle" class="relative rounded-lg p-2 text-slate-500 transition-colors hover:bg-slate-100" title="Notifikasi">
        <x-icon name="bell" class="h-5 w-5" />
        @if ($unreadCount > 0)
            <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-[1rem] items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-semibold text-white">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
        @endif
    </button>

    @if ($open)
        <div class="fixed inset-0 z-30" wire:click="$set('open', false)"></div>
        <div class="absolute right-0 z-40 mt-2 w-80 rounded-xl border border-slate-200 bg-white shadow-lg">
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-2.5">
                <span class="text-sm font-semibold text-slate-700">Notifikasi</span>
                @if ($unreadCount > 0)
                    <button wire:click="markAllAsRead" class="text-xs font-medium text-indigo-600 hover:underline">Tandai semua dibaca</button>
                @endif
            </div>
            <div class="max-h-96 overflow-y-auto">
                @forelse ($notifications as $n)
                    @php $isUnread = $n->read_at === null; @endphp
                    <a
                        href="{{ $n->data['url'] ?? '#' }}"
                        wire:click="markAsRead('{{ $n->id }}')"
                        class="flex gap-2.5 border-b border-slate-50 px-4 py-3 text-sm transition-colors hover:bg-slate-50 {{ $isUnread ? 'bg-indigo-50/60' : '' }}"
                    >
                        <span class="mt-1 h-2 w-2 shrink-0 rounded-full {{ $isUnread ? 'bg-indigo-500' : 'bg-transparent' }}"></span>
                        <span class="min-w-0 flex-1">
                            <span class="block font-medium text-slate-700">{{ $n->data['title'] ?? 'Notifikasi' }}</span>
                            <span class="block text-xs text-slate-500">{{ $n->data['message'] ?? '' }}</span>
                            <span class="mt-0.5 block text-[11px] text-slate-400">{{ $n->created_at->diffForHumans() }}</span>
                        </span>
                    </a>
                @empty
                    <x-empty-state icon="bell" title="Belum ada notifikasi." />
                @endforelse
            </div>
            <div class="border-t border-slate-100 px-4 py-2 text-center">
                <a href="{{ route('notifications.index') }}" class="text-xs font-medium text-indigo-600 hover:underline">Lihat semua notifikasi</a>
            </div>
        </div>
    @endif
</div>
