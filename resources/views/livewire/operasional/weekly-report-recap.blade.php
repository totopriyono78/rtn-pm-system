<div class="space-y-6">
    <x-page-header icon="clipboard-list" color="violet" title="Rekap Laporan Mingguan" subtitle="Admin Kantor -- pantau kelengkapan laporan teknisi, BAPP RO, dan BAL Bulanan per proyek." />

    <div class="flex items-center justify-between rounded-xl bg-white p-4 shadow-sm">
        <div class="flex items-center gap-2">
            <button wire:click="previousWeek" class="rounded-lg border border-slate-300 p-1.5 hover:bg-slate-50">
                <x-icon name="arrow-left" class="h-4 w-4" />
            </button>
            <div class="text-sm font-medium text-slate-700">
                {{ $weekStart->format('d M Y') }} &ndash; {{ $weekEnd->format('d M Y') }}
            </div>
            <button wire:click="nextWeek" class="rounded-lg border border-slate-300 p-1.5 hover:bg-slate-50">
                <x-icon name="arrow-right" class="h-4 w-4" />
            </button>
        </div>
        <button wire:click="thisWeek" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-50">
            Minggu Ini
        </button>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <p class="mb-3 text-xs text-slate-400">Status dokumen BAPP RO & BAL Bulanan ditampilkan untuk bulan {{ $monthLabel }}.</p>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">Proyek</th>
                        <th class="pb-2">Region / Unit</th>
                        <th class="pb-2 text-center">Laporan Minggu Ini</th>
                        <th class="pb-2 text-center">BAPP RO</th>
                        <th class="pb-2 text-center">BAL Bulanan</th>
                        <th class="pb-2 text-center">Simlok &amp; SIKA</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2 font-medium">{{ $row['project']->name }}</td>
                            <td class="py-2 text-slate-500">{{ $row['project']->unit?->region?->name }} / {{ $row['project']->unit?->name }}</td>
                            <td class="py-2 text-center">
                                @if ($row['reportCount'] > 0)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-700">{{ $row['reportCount'] }} laporan</span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-xs text-red-700">Belum ada</span>
                                @endif
                            </td>
                            <td class="py-2 text-center">
                                @if ($row['hasBapp'])
                                    <x-icon name="check" class="mx-auto h-4 w-4 text-green-600" />
                                @else
                                    <span class="text-xs text-slate-400">Belum</span>
                                @endif
                            </td>
                            <td class="py-2 text-center">
                                @if ($row['hasBal'])
                                    <x-icon name="check" class="mx-auto h-4 w-4 text-green-600" />
                                @else
                                    <span class="text-xs text-slate-400">Belum</span>
                                @endif
                            </td>
                            <td class="py-2 text-center">
                                @if ($row['hasSimlokSika'])
                                    <x-icon name="check" class="mx-auto h-4 w-4 text-green-600" />
                                @elseif ($row['needsSimlokSoon'])
                                    <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2 py-0.5 text-xs text-red-700">H-3, belum ada</span>
                                @else
                                    <span class="text-xs text-slate-400">Belum</span>
                                @endif
                            </td>
                            <td class="py-2 text-right">
                                <a href="{{ route('projects.show', $row['project']) }}" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 transition-colors hover:bg-indigo-50">
                                    <x-icon name="eye" class="h-3.5 w-3.5" /> Lihat
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-empty-state icon="clipboard-list" title="Tidak ada proyek berjalan." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
