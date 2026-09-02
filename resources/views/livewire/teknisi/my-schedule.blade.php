<div class="space-y-6">
    <x-page-header icon="calendar" color="emerald" title="Jadwal Saya" subtitle="Penugasan yang activity-nya masih berjalan hari ini. Status menunjukkan apakah Anda sudah lapor hari ini, dievaluasi ulang setiap hari selama periode activity berlangsung." />

    <div class="flex flex-wrap items-end gap-3 rounded-xl bg-white p-4 shadow-sm">
        <div class="flex items-center gap-2 pb-2 text-slate-400">
            <x-icon name="filter" class="h-4 w-4" />
            <span class="text-xs font-medium uppercase tracking-wide">Filter</span>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">Status Hari Ini</label>
            <select wire:model.live="status" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="belum">Belum Lapor Hari Ini</option>
                <option value="sudah">Sudah Lapor Hari Ini</option>
                <option value="semua">Semua</option>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">Dari Tanggal Ditugaskan</label>
            <input type="date" wire:model.live="dateFrom" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">Sampai Tanggal Ditugaskan</label>
            <input type="date" wire:model.live="dateTo" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
        </div>
        <button wire:click="resetFilters" type="button" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50">
            <x-icon name="refresh" class="h-4 w-4" /> Reset Filter
        </button>
        <p class="w-full text-xs text-slate-400">
            Tanpa filter tanggal manual, daftar otomatis hanya menampilkan penugasan yang periode activity-nya (start - end date) masih mencakup hari ini ({{ \Illuminate\Support\Carbon::parse($today)->translatedFormat('d M Y') }}). Isi "Dari/Sampai Tanggal" untuk mencari penugasan lain di luar hari ini.
        </p>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">Periode Activity</th>
                        <th class="pb-2">Activity</th>
                        <th class="pb-2">Proyek</th>
                        <th class="pb-2">Lokasi</th>
                        <th class="pb-2">Catatan</th>
                        <th class="pb-2">Status Hari Ini</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assignments as $a)
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2 text-slate-600">
                                @if ($a->activity->start_date || $a->activity->end_date)
                                    {{ optional($a->activity->start_date)->format('d M') ?? '?' }} &ndash; {{ optional($a->activity->end_date)->format('d M Y') ?? '?' }}
                                @else
                                    <span class="text-slate-400">Tidak ditentukan</span>
                                @endif
                            </td>
                            <td class="py-2 font-medium">{{ $a->activity->name }}</td>
                            <td class="py-2">{{ $a->activity->project->name }}</td>
                            <td class="py-2 text-slate-500">{{ $a->activity->project->unit->region->code }} - {{ $a->activity->project->unit->name }}</td>
                            <td class="py-2 text-slate-500">{{ $a->notes ?: '-' }}</td>
                            <td class="py-2">
                                @if ($a->reported_today_count > 0)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs text-emerald-700">
                                        <x-icon name="check" class="h-3 w-3" /> Sudah Lapor Hari Ini
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs text-amber-700">
                                        <x-icon name="clock" class="h-3 w-3" /> Belum Lapor Hari Ini
                                    </span>
                                @endif
                            </td>
                            <td class="py-2 text-right">
                                <a href="{{ route('teknisi.report.create') }}?assignmentId={{ $a->id }}" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 transition-colors hover:bg-indigo-50">
                                    <x-icon name="doc-plus" class="h-3.5 w-3.5" /> Isi Laporan
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-empty-state icon="calendar" title="Tidak ada penugasan yang cocok dengan filter ini." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $assignments->links() }}</div>
    </div>
</div>
