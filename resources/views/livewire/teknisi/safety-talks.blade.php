<div class="max-w-3xl space-y-6">
    <x-page-header icon="shield" color="emerald" title="Safety Talk / Toolbox Meeting" subtitle="Catat briefing K3 sebelum/selama bekerja — topik, peserta, dan foto dokumentasi (opsional)." />

    <form wire:submit="save" class="space-y-5 rounded-xl bg-white p-6 shadow-sm">
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Penugasan</label>
            <select wire:model="assignmentId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">-- pilih penugasan --</option>
                @foreach ($assignments as $a)
                    <option value="{{ $a->id }}">{{ $a->scheduled_date->format('d M Y') }} - {{ $a->activity->name }} ({{ $a->activity->project->name }})</option>
                @endforeach
            </select>
            @error('assignmentId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            @if ($assignments->isEmpty())
                <p class="mt-1 text-xs text-amber-600">Belum ada penugasan aktif (disetujui) untuk Anda saat ini.</p>
            @endif
        </div>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal</label>
                <input type="date" wire:model="meetingDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('meetingDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Topik</label>
                <input type="text" wire:model="topic" placeholder="mis. Penggunaan APD di area confined space" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('topic') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Peserta (opsional)</label>
            <textarea wire:model="attendees" rows="2" placeholder="mis. Andi, Budi, perwakilan klien Pak Joko" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
            @error('attendees') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Catatan (opsional)</label>
            <textarea wire:model="notes" rows="3" placeholder="Poin bahaya yang dibahas, tindakan pencegahan, dsb." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
            @error('notes') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="mb-1 flex items-center gap-1.5 text-sm font-medium text-slate-700">
                <x-icon name="doc-text" class="h-4 w-4 text-slate-400" /> Foto Dokumentasi (opsional, maks {{ $maxUploadMb }} MB)
            </label>
            <input type="file" wire:model="photo" accept="image/*"
                class="block w-full cursor-pointer rounded-lg border border-slate-300 p-2 text-sm text-slate-500 file:mr-4 file:cursor-pointer file:rounded-lg file:border-0 file:bg-emerald-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-emerald-500">
            @error('photo') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>

        <div class="flex justify-end">
            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                <x-icon name="shield" class="h-4 w-4" /> Simpan Catatan
            </button>
        </div>
    </form>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <h3 class="mb-3 text-sm font-semibold text-slate-700">Riwayat Safety Talk Saya</h3>
        @forelse ($logs as $log)
            <div class="flex items-start justify-between gap-3 border-t border-slate-100 py-3 text-sm first:border-0">
                <div class="min-w-0">
                    <div class="font-medium text-slate-800">{{ $log->topic }}</div>
                    <div class="text-slate-500">{{ $log->activity->name }} &middot; {{ $log->activity->project->name }}</div>
                    <div class="text-xs text-slate-400">{{ $log->meeting_date->format('d M Y') }}</div>
                    @if ($log->attendees)
                        <div class="mt-1 text-xs text-slate-500">Peserta: {{ $log->attendees }}</div>
                    @endif
                </div>
                @if ($log->photo_disk_path)
                    <a href="{{ route('safety-talks.photo', $log) }}" class="shrink-0 inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:underline">
                        <x-icon name="download" class="h-3.5 w-3.5" /> Foto
                    </a>
                @endif
            </div>
        @empty
            <x-empty-state icon="shield" title="Belum ada catatan Safety Talk." />
        @endforelse
        @if ($logs->hasPages())
            <div class="mt-4">{{ $logs->links() }}</div>
        @endif
    </div>
</div>
