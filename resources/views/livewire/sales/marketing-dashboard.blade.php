<div class="space-y-6">
    <x-page-header icon="chart-bar" color="violet" title="Dashboard Marketing &amp; Sales" subtitle="Ringkasan pipeline CRM dan Sales Order pengadaan pompa baru." />

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="flex items-start gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                <x-icon name="user-plus" class="h-5 w-5" />
            </div>
            <div class="min-w-0">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-400">Prospect Aktif</div>
                <div class="mt-1 text-2xl font-semibold text-slate-800">{{ $totalOpen }}</div>
            </div>
        </div>

        <div class="flex items-start gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-sky-50 text-sky-600">
                <x-icon name="wallet" class="h-5 w-5" />
            </div>
            <div class="min-w-0">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-400">Estimasi Nilai Pipeline</div>
                <div class="mt-1 truncate text-2xl font-semibold text-slate-800">Rp {{ number_format($pipelineValue, 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="flex items-start gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                <x-icon name="check" class="h-5 w-5" />
            </div>
            <div class="min-w-0">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-400">Win Rate</div>
                <div class="mt-1 text-2xl font-semibold text-slate-800">{{ $winRate !== null ? $winRate.'%' : '-' }}</div>
                <div class="text-xs text-slate-400">{{ $totalWon }} Won &middot; {{ $totalLost }} Lost</div>
            </div>
        </div>

        <div class="flex items-start gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-orange-500">
                <x-icon name="clipboard-list" class="h-5 w-5" />
            </div>
            <div class="min-w-0">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-400">Sales Order Terkonfirmasi</div>
                <div class="mt-1 text-2xl font-semibold text-slate-800">{{ $soByStatus['confirmed']->total ?? 0 }}</div>
                <div class="text-xs text-slate-400">Rp {{ number_format((float) ($soByStatus['confirmed']->value ?? 0), 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-slate-700">Pipeline per Tahap</h3>
            <div class="space-y-3">
                @foreach (\App\Models\Prospect::PIPELINE_STAGES as $stage)
                    @php
                        $count = $byStage[$stage]->total ?? 0;
                        $pct = $totalOpen > 0 ? round(($count / $totalOpen) * 100) : 0;
                    @endphp
                    <div>
                        <div class="mb-1 flex items-center justify-between text-xs">
                            <span class="font-medium text-slate-600">{{ \App\Models\Prospect::STATUSES[$stage] }}</span>
                            <span class="text-slate-400">{{ $count }} prospect</span>
                        </div>
                        <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-2 rounded-full bg-violet-500" style="width: {{ $pct }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
            <a href="{{ route('sales.prospects.index') }}" class="mt-4 inline-block text-xs font-semibold text-indigo-600 hover:underline">Lihat semua Prospect &rarr;</a>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-slate-700">Aktivitas Terbaru</h3>
            <div class="space-y-3">
                @forelse ($recentActivities as $activity)
                    <div class="flex items-start gap-2 text-sm">
                        <x-icon name="clock" class="mt-0.5 h-4 w-4 shrink-0 text-slate-300" />
                        <div class="min-w-0">
                            <p class="truncate">
                                <a href="{{ route('sales.prospects.show', $activity->prospect) }}" class="font-medium text-indigo-600 hover:underline">{{ $activity->prospect->company_name }}</a>
                                &middot; {{ $activity->typeLabel() }}
                            </p>
                            <p class="text-xs text-slate-400">{{ $activity->activity_date->format('d M Y H:i') }} &middot; {{ $activity->user->name ?? 'Sistem' }}</p>
                        </div>
                    </div>
                @empty
                    <x-empty-state icon="clock" title="Belum ada aktivitas tercatat." />
                @endforelse
            </div>
            <a href="{{ route('sales.orders.index') }}" class="mt-4 inline-block text-xs font-semibold text-indigo-600 hover:underline">Lihat semua Sales Order &rarr;</a>
        </div>
    </div>
</div>
