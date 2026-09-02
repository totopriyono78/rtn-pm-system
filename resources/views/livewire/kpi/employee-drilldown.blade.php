<div class="space-y-6">
    <div>
        <a href="{{ route('kpi.dashboard') }}" class="text-sm text-indigo-600 hover:underline">&larr; Kembali ke Dashboard KPI</a>
        <h2 class="mt-1 text-xl font-semibold text-slate-800">{{ $user->name }}</h2>
        <p class="text-sm text-slate-500">{{ $user->roleLabel() }} &middot; Total {{ $totalHours }} jam pada periode terpilih</p>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <form wire:submit.prevent class="flex flex-wrap items-end gap-3">
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
        <h3 class="mb-4 text-sm font-semibold text-slate-700">Breakdown per Activity</h3>
        <table class="w-full text-left text-sm">
            <thead class="text-xs uppercase text-slate-400">
                <tr><th class="pb-2">Activity</th><th class="pb-2">Proyek</th><th class="pb-2">Jam</th></tr>
            </thead>
            <tbody>
                @forelse ($byActivity as $row)
                    <tr class="border-t border-slate-100">
                        <td class="py-2 font-medium">{{ $row['activity'] }}</td>
                        <td class="py-2 text-slate-500">{{ $row['project'] }}</td>
                        <td class="py-2">{{ number_format($row['minutes'] / 60, 1) }} jam</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-4 text-center text-slate-400">Tidak ada data pada periode ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <h3 class="mb-4 text-sm font-semibold text-slate-700">Rincian Harian</h3>
        <table class="w-full text-left text-sm">
            <thead class="text-xs uppercase text-slate-400">
                <tr><th class="pb-2">Tanggal</th><th class="pb-2">Activity</th><th class="pb-2">Jam Kerja</th><th class="pb-2">Durasi</th></tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    <tr class="border-t border-slate-100">
                        <td class="py-2">{{ $log->log_date->format('d M Y') }}</td>
                        <td class="py-2">{{ $log->activity->name }} <span class="text-slate-400">({{ $log->activity->project->name }})</span></td>
                        <td class="py-2">{{ $log->start_time }} - {{ $log->end_time }}</td>
                        <td class="py-2">{{ number_format($log->duration_minutes / 60, 1) }} jam</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="py-4 text-center text-slate-400">Tidak ada data.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
