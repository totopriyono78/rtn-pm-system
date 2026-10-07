<div class="space-y-6">
    <div class="flex items-center justify-between">
        <x-page-header icon="bell" color="indigo" title="Notifikasi" subtitle="Semua notifikasi aktivitas & approval yang ditujukan untuk Anda." />
        @if ($unreadCount > 0)
            <button wire:click="markAllAsRead" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">
                <x-icon name="check" class="h-4 w-4" /> Tandai semua dibaca
            </button>
        @endif
    </div>

    <div class="flex items-center gap-2">
        <button wire:click="setFilter('all')" @class(['rounded-lg px-3 py-1.5 text-sm font-medium', 'bg-indigo-600 text-white' => $filter === 'all', 'border border-slate-300 text-slate-600 hover:bg-slate-50' => $filter !== 'all'])>Semua</button>
        <button wire:click="setFilter('unread')" @class(['rounded-lg px-3 py-1.5 text-sm font-medium', 'bg-indigo-600 text-white' => $filter === 'unread', 'border border-slate-300 text-slate-600 hover:bg-slate-50' => $filter !== 'unread'])>Belum Dibaca ({{ $unreadCount }})</button>
    </div>

    <div class="rounded-xl bg-white p-2 shadow-sm sm:p-4">
        @forelse ($notifications as $n)
            @php $isUnread = $n->read_at === null; @endphp
            <div wire:key="notif-{{ $n->id }}" class="flex gap-3 border-b border-slate-100 px-2 py-3.5 last:border-0 sm:px-3 {{ $isUnread ? 'bg-indigo-50/40' : '' }}">
                <span class="mt-1.5 h-2 w-2 shrink-0 rounded-full {{ $isUnread ? 'bg-indigo-500' : 'bg-slate-200' }}"></span>
                <div class="min-w-0 flex-1">
                    <p class="font-medium text-slate-700">{{ $n->data['title'] ?? 'Notifikasi' }}</p>
                    <p class="text-sm text-slate-500">{{ $n->data['message'] ?? '' }}</p>
                    <p class="mt-1 text-xs text-slate-400">{{ $n->created_at->translatedFormat('d M Y, H:i') }} ({{ $n->created_at->diffForHumans() }})</p>
                </div>
                <div class="flex shrink-0 items-center gap-2">
                    @if (! empty($n->data['url']))
                        <a href="{{ $n->data['url'] }}" wire:click="markAsRead('{{ $n->id }}')" class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50">Lihat</a>
                    @endif
                    @if ($isUnread)
                        <button wire:click="markAsRead('{{ $n->id }}')" class="rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 hover:underline">Tandai dibaca</button>
                    @endif
                </div>
            </div>
        @empty
            <x-empty-state icon="bell" title="Belum ada notifikasi." />
        @endforelse
    </div>
    <div>{{ $notifications->links() }}</div>
</div>
