<div class="space-y-6">
    <x-page-header icon="map-pin" color="amber" title="Override Presensi" subtitle="Sahkan check-in teknisi yang tidak bisa presensi normal (GPS/sinyal buruk di lokasi) — tetap tercatat sebagai override, bukan check-in normal." />

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex items-center gap-3">
            <label class="text-xs font-medium text-slate-500">Tanggal</label>
            <input type="date" wire:model.live="date" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">Teknisi</th>
                        <th class="pb-2">Activity</th>
                        <th class="pb-2">Site</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assignments as $a)
                        <tr class="border-t border-slate-100 hover:bg-slate-50/70">
                            <td class="py-2 font-medium">{{ $a->user->name }}</td>
                            <td class="py-2">{{ $a->activity->name }} <span class="text-slate-400">({{ $a->activity->project->name }})</span></td>
                            <td class="py-2 text-slate-500">{{ $a->activity->site->name ?? '-' }}</td>
                            <td class="py-2 text-right">
                                <button wire:click="openOverride({{ $a->id }})" class="inline-flex items-center gap-1 rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-400">
                                    <x-icon name="check" class="h-3.5 w-3.5" /> Override
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-empty-state icon="check" title="Semua penugasan bersite tanggal ini sudah presensi." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $assignments->links() }}</div>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <h3 class="mb-3 text-sm font-semibold text-slate-700">Riwayat Override Terakhir</h3>
        <div class="space-y-2">
            @forelse ($recentOverrides as $ov)
                <div class="rounded-lg border border-amber-100 bg-amber-50/50 p-3 text-sm">
                    <div class="font-medium text-slate-800">{{ $ov->user->name }} &middot; {{ $ov->site->name ?? '-' }}</div>
                    <div class="text-xs text-slate-500">Oleh {{ $ov->overrideBy->name ?? '-' }} pada {{ $ov->checked_in_at->format('d M Y H:i') }}: {{ $ov->override_reason }}</div>
                </div>
            @empty
                <p class="text-sm text-slate-400">Belum ada override.</p>
            @endforelse
        </div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-slate-800">Override Presensi</h3>
                <form wire:submit="saveOverride" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Alasan Override</label>
                        <textarea wire:model="overrideReason" rows="3" placeholder="mis. Sinyal GPS tidak masuk di dalam tangki, dikonfirmasi hadir secara fisik." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('overrideReason') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-400">
                            <x-icon name="check" class="h-4 w-4" /> Sahkan Presensi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
