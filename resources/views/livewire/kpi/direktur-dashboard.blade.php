<div class="space-y-6">
    <div class="flex items-start justify-between gap-3">
        <x-page-header icon="chart-bar" color="orange" title="Dashboard KPI" subtitle="Jam kerja karyawan berdasarkan laporan yang masuk." />
        @can('manage-kpi-settings')
            <a href="{{ route('admin.kpi-settings') }}" class="inline-flex shrink-0 items-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3.5 py-2 text-xs font-medium text-slate-600 shadow-sm transition-colors hover:bg-slate-50">
                <x-icon name="sliders" class="h-3.5 w-3.5" /> Pengaturan KPI
            </a>
        @endcan
    </div>

    {{-- Ringkasan tim: memberi konteks langsung tanpa harus baca tabel dulu --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-slate-400">
                <x-icon name="clock" class="h-3.5 w-3.5" /> Total Jam Tim Hari Ini
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-800">{{ number_format($totalJamHariIni, 1) }} <span class="text-sm font-normal text-slate-400">jam</span></div>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-slate-400">
                <x-icon name="users" class="h-3.5 w-3.5" /> Sudah Lapor Hari Ini
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-800">{{ $sudahLaporHariIni }} <span class="text-sm font-normal text-slate-400">/ {{ $totalTeknisi }} teknisi</span></div>
            @if ($totalTeknisi > 0 && $sudahLaporHariIni < $totalTeknisi)
                <p class="mt-1 flex items-center gap-1 text-xs text-amber-600">
                    <x-icon name="alert-circle" class="h-3.5 w-3.5" /> {{ $totalTeknisi - $sudahLaporHariIni }} teknisi belum lapor
                </p>
            @endif
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="flex items-center gap-2 text-xs font-medium uppercase tracking-wide text-slate-400">
                <x-icon name="chart-bar" class="h-3.5 w-3.5" />
                {{ $kpiSetting->isTargetMode() ? 'Target Jam / Bulan' : 'Rata-rata Jam / Teknisi (Bulan Ini)' }}
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-800">
                {{ number_format($kpiSetting->isTargetMode() ? $thresholdMonth : $teamAvgMonth, 1) }}
                <span class="text-sm font-normal text-slate-400">jam</span>
            </div>
            <p class="mt-1 text-xs text-slate-400">
                Mode: {{ $kpiSetting->isTargetMode() ? 'Target Tetap' : 'Rata-rata Tim' }}
            </p>
        </div>
    </div>

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
        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <h3 class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                <x-icon name="users" class="h-4 w-4 text-slate-400" /> Akumulasi Jam Kerja per Individu
            </h3>
            <span class="text-xs text-slate-400">
                Diurutkan dari jam kerja bulan ini tertinggi
                @if ($kpiSetting->show_threshold_badges)
                    &middot; Ambang: {{ number_format($thresholdDay, 1) }} jam/hari, {{ number_format($thresholdWeek, 1) }} jam/minggu, {{ number_format($thresholdMonth, 1) }} jam/bulan
                @endif
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">#</th>
                        <th class="pb-2">Karyawan</th>
                        <th class="pb-2">Hari Ini</th>
                        <th class="pb-2">Minggu Ini</th>
                        <th class="pb-2">Bulan Ini</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($accumulation as $row)
                        @php
                            $belowDay = $kpiSetting->show_threshold_badges && $row['today'] < $thresholdDay;
                            $belowWeek = $kpiSetting->show_threshold_badges && $row['week'] < $thresholdWeek;
                            $belowMonth = $kpiSetting->show_threshold_badges && $row['month'] < $thresholdMonth;
                        @endphp
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70 {{ $belowMonth ? 'bg-amber-50/40' : '' }}">
                            <td class="py-2 text-slate-400">
                                @if ($loop->index === 0)
                                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-amber-100 text-xs font-bold text-amber-700">1</span>
                                @else
                                    {{ $loop->index + 1 }}
                                @endif
                            </td>
                            <td class="py-2 font-medium">{{ $row['user']->name }}</td>
                            <td class="py-2 {{ $belowDay ? 'text-amber-600' : '' }}">
                                {{ $row['today'] }} jam @if ($belowDay) <span title="Di bawah ambang harian ({{ number_format($thresholdDay, 1) }} jam)">▼</span> @endif
                            </td>
                            <td class="py-2 {{ $belowWeek ? 'text-amber-600' : '' }}">
                                {{ $row['week'] }} jam @if ($belowWeek) <span title="Di bawah ambang mingguan ({{ number_format($thresholdWeek, 1) }} jam)">▼</span> @endif
                            </td>
                            <td class="py-2">
                                {{ $row['month'] }} jam
                                @if ($belowMonth)
                                    <span class="ml-1 text-xs text-amber-600" title="Di bawah ambang bulanan ({{ number_format($thresholdMonth, 1) }} jam)">▼ kurang</span>
                                @endif
                            </td>
                            <td class="py-2 text-right">
                                <a href="{{ route('kpi.drilldown', $row['user']) }}" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 transition-colors hover:bg-indigo-50">
                                    <x-icon name="chart-bar" class="h-3.5 w-3.5" /> Detail Breakdown
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state icon="chart-bar" title="Belum ada data jam kerja bulan ini." /></td></tr>
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
