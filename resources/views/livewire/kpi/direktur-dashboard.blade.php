<div class="space-y-6">
    <x-page-header icon="chart-bar" color="orange" title="Dashboard KPI" subtitle="Jam kerja karyawan berdasarkan laporan yang masuk." />

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-slate-700">
            <x-icon name="clock" class="h-4 w-4 text-slate-400" /> Jam Kerja Hari Ini (per karyawan)
        </h3>
        <div class="space-y-2">
            @forelse ($todayHours as $row)
                <div class="flex items-center gap-3">
                    <div class="w-40 shrink-0 truncate text-sm text-slate-600">{{ $row->user->name }}</div>
                    <div class="h-4 flex-1 overflow-hidden rounded-full bg-slate-100">
                        <div class="h-4 rounded-full bg-indigo-500" style="width: {{ ($row->minutes / $maxTodayMinutes) * 100 }}%"></div>
                    </div>
                    <div class="w-16 shrink-0 text-right text-sm font-medium">{{ number_format($row->minutes / 60, 1) }} jam</div>
                </div>
            @empty
                <x-empty-state icon="clock" title="Belum ada laporan yang masuk hari ini." />
            @endforelse
        </div>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-slate-700">
            <x-icon name="users" class="h-4 w-4 text-slate-400" /> Akumulasi Jam Kerja per Individu
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">Karyawan</th>
                        <th class="pb-2">Hari Ini</th>
                        <th class="pb-2">Minggu Ini</th>
                        <th class="pb-2">Bulan Ini</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($accumulation as $row)
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2 font-medium">{{ $row['user']->name }}</td>
                            <td class="py-2">{{ $row['today'] }} jam</td>
                            <td class="py-2">{{ $row['week'] }} jam</td>
                            <td class="py-2">{{ $row['month'] }} jam</td>
                            <td class="py-2 text-right">
                                <a href="{{ route('kpi.drilldown', $row['user']) }}" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 transition-colors hover:bg-indigo-50">
                                    <x-icon name="chart-bar" class="h-3.5 w-3.5" /> Detail Breakdown
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty-state icon="chart-bar" title="Belum ada data jam kerja bulan ini." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-slate-700">
            <x-icon name="briefcase" class="h-4 w-4 text-slate-400" /> Rencana vs Aktual per Proyek
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">Proyek</th>
                        <th class="pb-2">Jam Rencana</th>
                        <th class="pb-2">Jam Aktual</th>
                        <th class="pb-2">Selisih</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($projects as $row)
                        @php $selisih = $row['actual'] - $row['planned']; @endphp
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2 font-medium">
                                <a href="{{ route('projects.show', $row['project']) }}" class="text-indigo-600 hover:underline">{{ $row['project']->name }}</a>
                            </td>
                            <td class="py-2">{{ number_format($row['planned'], 1) }} jam</td>
                            <td class="py-2">{{ number_format($row['actual'], 1) }} jam</td>
                            <td class="py-2 {{ $selisih > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                {{ $selisih > 0 ? '+' : '' }}{{ number_format($selisih, 1) }} jam
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-empty-state icon="briefcase" title="Belum ada proyek." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
