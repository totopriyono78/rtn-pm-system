<div class="space-y-6">
    <x-page-header icon="mail" color="indigo" title="Pesan Masuk (Company Website)" subtitle="Lead dari form Kontak di halaman publik. Belum terhubung ke CRM -- tindak lanjuti manual sampai modul CRM (Divisi 1) dibangun." />

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center gap-2">
            <button wire:click="$set('statusFilter', '')" class="rounded-full px-3 py-1 text-xs font-medium {{ $statusFilter === '' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600' }}">Semua</button>
            @foreach (\App\Models\WebsiteContactMessage::STATUSES as $key => $label)
                <button wire:click="$set('statusFilter', '{{ $key }}')" class="rounded-full px-3 py-1 text-xs font-medium {{ $statusFilter === $key ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600' }}">{{ $label }}</button>
            @endforeach
        </div>

        <div class="space-y-3">
            @forelse ($messages as $message)
                <div class="rounded-lg border border-slate-100 p-4">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div>
                            <div class="font-medium text-slate-800">{{ $message->name }} @if ($message->company)<span class="font-normal text-slate-400">· {{ $message->company }}</span>@endif</div>
                            <div class="text-xs text-slate-400">{{ $message->email }} @if ($message->phone) · {{ $message->phone }} @endif · {{ $message->created_at->format('d M Y H:i') }}</div>
                        </div>
                        <select wire:change="updateStatus({{ $message->id }}, $event.target.value)" class="rounded-lg border border-slate-300 px-2 py-1 text-xs">
                            @foreach (\App\Models\WebsiteContactMessage::STATUSES as $key => $label)
                                <option value="{{ $key }}" @selected($message->status === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if ($message->subject)
                        <div class="mt-2 text-sm font-medium text-slate-700">{{ $message->subject }}</div>
                    @endif
                    <p class="mt-1 whitespace-pre-line text-sm text-slate-600">{{ $message->message }}</p>
                    <div class="mt-2 text-right">
                        <button wire:click="delete({{ $message->id }})" wire:confirm="Hapus pesan ini?" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-red-600 transition-colors hover:bg-red-50">
                            <x-icon name="trash" class="h-3.5 w-3.5" /> Hapus
                        </button>
                    </div>
                </div>
            @empty
                <x-empty-state icon="mail" title="Belum ada pesan masuk." />
            @endforelse
        </div>
        <div class="mt-4">{{ $messages->links() }}</div>
    </div>
</div>
