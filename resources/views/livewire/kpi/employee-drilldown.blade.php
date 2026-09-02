<div class="space-y-6">
    <div>
        <a href="{{ route('kpi.dashboard') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:underline">
            <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke Dashboard KPI
        </a>
        <div class="mt-2">
            <x-page-header icon="users" color="orange" :title="$user->name" :subtitle="$user->roleLabel() . ' · Total ' . $totalHours . ' jam pada periode terpilih'" />
        </div>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <form wire:submit.prevent class="flex flex-wrap items-end gap-3">
            <div class="flex items-center gap-2 pb-2 text-slate-400">
                <x-icon name="filter" class="h-4 w-4" />
                <span class="text-xs font-medium uppercase tracking-wide">Filter Periode</span>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Dari</label>
                <input type="date" wire:model.live="fromDate" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-600">Sampai</label>
                <input type="date" wire:model.live="toDate" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
        </form>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-slate-700">
            <x-icon name="clipboard-list" class="h-4 w-4 text-slate-400" /> Breakdown per Activity
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr><th class="pb-2">Activity</th><th class="pb-2">Proyek</th><th class="pb-2">Jam</th></tr>
                </thead>
                <tbody>
                    @forelse ($byActivity as $row)
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2 font-medium">{{ $row['activity'] }}</td>
                            <td class="py-2 text-slate-500">{{ $row['project'] }}</td>
                            <td class="py-2">{{ number_format($row['minutes'] / 60, 1) }} jam</td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><x-empty-state icon="clipboard-list" title="Tidak ada data pada periode ini." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-slate-700">
            <x-icon name="calendar" class="h-4 w-4 text-slate-400" /> Rincian Harian
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr><th class="pb-2">Tanggal</th><th class="pb-2">Activity</th><th class="pb-2">Jam Kerja</th><th class="pb-2">Durasi</th></tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2">{{ $log->log_date->format('d M Y') }}</td>
                            <td class="py-2">{{ $log->activity->name }} <span class="text-slate-400">({{ $log->activity->project->name }})</span></td>
                            <td class="py-2">{{ $log->start_time }} - {{ $log->end_time }}</td>
                            <td class="py-2">{{ number_format($log->duration_minutes / 60, 1) }} jam</td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-empty-state icon="calendar" title="Tidak ada data." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
