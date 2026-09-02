<div class="space-y-6">
    <div>
        <h2 class="text-xl font-semibold text-slate-800">Jadwal Saya</h2>
        <p class="text-sm text-slate-500">Daftar penugasan yang di-assign kepada Anda. Secara default hanya yang belum dilaporkan yang ditampilkan.</p>
    </div>

    <div class="flex flex-wrap items-end gap-3 rounded-xl bg-white p-4 shadow-sm">
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">Status</label>
            <select wire:model.live="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="belum">Belum Dilaporkan</option>
                <option value="sudah">Sudah Dilaporkan</option>
                <option value="semua">Semua</option>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">Dari Tanggal</label>
            <input type="date" wire:model.live="dateFrom" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">Sampai Tanggal</label>
            <input type="date" wire:model.live="dateTo" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <button wire:click="resetFilters" type="button" class="rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50">Reset Filter</button>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="text-xs uppercase text-slate-400">
                <tr>
                    <th class="pb-2">Tanggal</th>
                    <th class="pb-2">Activity</th>
                    <th class="pb-2">Proyek</th>
                    <th class="pb-2">Lokasi</th>
                    <th class="pb-2">Catatan</th>
                    <th class="pb-2">Status</th>
                    <th class="pb-2 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($assignments as $a)
                    <tr class="border-t border-slate-100">
                        <td class="py-2">{{ $a->scheduled_date->format('d M Y') }}</td>
                        <td class="py-2 font-medium">{{ $a->activity->name }}</td>
                        <td class="py-2">{{ $a->activity->project->name }}</td>
                        <td class="py-2 text-slate-500">{{ $a->activity->project->unit->region->code }} - {{ $a->activity->project->unit->name }}</td>
                        <td class="py-2 text-slate-500">{{ $a->notes ?: '-' }}</td>
                        <td class="py-2">
                            @if ($a->reports_count > 0)
                                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs text-emerald-700">Sudah Dilaporkan</span>
                            @else
                                <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs text-amber-700">Belum Dilaporkan</span>
                            @endif
                        </td>
                        <td class="py-2 text-right">
                            <a href="{{ route('teknisi.report.create') }}?assignmentId={{ $a->id }}" class="text-indigo-600 hover:underline">Isi Laporan</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-4 text-center text-slate-400">Tidak ada penugasan yang cocok dengan filter ini.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $assignments->links() }}</div>
    </div>
</div>
