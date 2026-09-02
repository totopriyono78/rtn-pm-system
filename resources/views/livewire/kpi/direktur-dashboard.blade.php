<div class="space-y-6">
    <div>
        <h2 class="text-xl font-semibold text-slate-800">Dashboard KPI</h2>
        <p class="text-sm text-slate-500">Jam kerja karyawan berdasarkan laporan yang masuk.</p>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <h3 class="mb-4 text-sm font-semibold text-slate-700">Jam Kerja Hari Ini (per karyawan)</h3>
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
                <p class="text-sm text-slate-400">Belum ada laporan yang masuk hari ini.</p>
            @endforelse
        </div>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <h3 class="mb-4 text-sm font-semibold text-slate-700">Akumulasi Jam Kerja per Individu</h3>
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
                    <tr class="border-t border-slate-100">
                        <td class="py-2 font-medium">{{ $row['user']->name }}</td>
                        <td class="py-2">{{ $row['today'] }} jam</td>
                        <td class="py-2">{{ $row['week'] }} jam</td>
                        <td class="py-2">{{ $row['month'] }} jam</td>
                        <td class="py-2 text-right">
                            <a href="{{ route('kpi.drilldown', $row['user']) }}" class="text-indigo-600 hover:underline">Detail Breakdown</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-4 text-center text-slate-400">Belum ada data jam kerja bulan ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <h3 class="mb-4 text-sm font-semibold text-slate-700">Rencana vs Aktual per Proyek</h3>
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
                    <tr class="border-t border-slate-100">
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
                    <tr><td colspan="4" class="py-4 text-center text-slate-400">Belum ada proyek.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
