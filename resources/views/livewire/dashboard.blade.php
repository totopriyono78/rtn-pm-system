<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-2">
        <div>
            <h2 class="text-xl font-semibold text-slate-800">Selamat datang, {{ explode(' ', auth()->user()->name)[0] }} 👋</h2>
            <p class="text-sm text-slate-500">{{ auth()->user()->roleLabel() }} &middot; {{ now()->translatedFormat('l, d F Y') }}</p>
        </div>
    </div>

    {{-- ===== Stat tiles ===== --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="flex items-start gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                <x-icon name="briefcase" class="h-5 w-5" />
            </div>
            <div class="min-w-0">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-400">Total Proyek</div>
                <div class="mt-1 text-2xl font-semibold text-slate-800">{{ $totalProjects }}</div>
            </div>
        </div>

        @if ($totalProjectValue !== null)
            <div class="flex items-start gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600">
                    <x-icon name="wallet" class="h-5 w-5" />
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-400">Total Nilai Proyek</div>
                    <div class="mt-1 truncate text-2xl font-semibold text-slate-800" title="Rp {{ number_format($totalProjectValue, 0, ',', '.') }}">Rp {{ number_format($totalProjectValue, 0, ',', '.') }}</div>
                </div>
            </div>
        @endif

        <div class="flex items-start gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-orange-50 text-orange-500">
                <x-icon name="chart-bar" class="h-5 w-5" />
            </div>
            <div class="min-w-0">
                <div class="text-xs font-medium uppercase tracking-wide text-slate-400">Activity Berjalan</div>
                <div class="mt-1 text-2xl font-semibold text-slate-800">{{ $ongoingActivities }}</div>
            </div>
        </div>

        @if ($workHoursTrend !== null)
            <div class="flex items-start gap-4 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600">
                    <x-icon name="clipboard-list" class="h-5 w-5" />
                </div>
                <div class="min-w-0">
                    <div class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ $workHoursTrendLabel }} (14 hari)</div>
                    <div class="mt-1 text-2xl font-semibold text-slate-800">{{ number_format($workHoursTrend->sum('hours'), 1) }} jam</div>
                </div>
            </div>
        @endif

        @if ($pendingApprovals !== null)
            @php $hasPending = $pendingApprovals->count() > 0; @endphp
            <a
                href="{{ route('purchasing.rfq', ['statusFilter' => 'submitted']) }}"
                @class([
                    'relative flex items-start gap-4 rounded-2xl border p-5 shadow-sm transition-colors',
                    'border-amber-200 bg-amber-50 hover:bg-amber-100' => $hasPending,
                    'border-slate-100 bg-white hover:bg-slate-50' => ! $hasPending,
                ])
            >
                @if ($hasPending)
                    <span class="absolute right-3 top-3 flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                    </span>
                @endif
                <div @class([
                    'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl',
                    'bg-amber-100 text-amber-600' => $hasPending,
                    'bg-violet-50 text-violet-600' => ! $hasPending,
                ])>
                    <x-icon name="doc-text" class="h-5 w-5" />
                </div>
                <div class="min-w-0">
                    <div @class(['text-xs font-medium uppercase tracking-wide', 'text-amber-700' => $hasPending, 'text-slate-400' => ! $hasPending])>Menunggu Approval</div>
                    <div @class(['mt-1 text-2xl font-semibold', 'text-amber-800' => $hasPending, 'text-slate-800' => ! $hasPending])>{{ $pendingApprovals->count() }}</div>
                    @if ($hasPending)
                        <div class="mt-0.5 text-xs font-medium text-amber-700">Perlu tindakan Anda &rarr;</div>
                    @endif
                </div>
            </a>
        @endif

        @if ($pendingAssignmentApprovals !== null)
            @php $hasPendingAssignment = $pendingAssignmentApprovals->count() > 0; @endphp
            <a
                href="{{ route('projects.index') }}"
                @class([
                    'relative flex items-start gap-4 rounded-2xl border p-5 shadow-sm transition-colors',
                    'border-amber-200 bg-amber-50 hover:bg-amber-100' => $hasPendingAssignment,
                    'border-slate-100 bg-white hover:bg-slate-50' => ! $hasPendingAssignment,
                ])
            >
                @if ($hasPendingAssignment)
                    <span class="absolute right-3 top-3 flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                    </span>
                @endif
                <div @class([
                    'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl',
                    'bg-amber-100 text-amber-600' => $hasPendingAssignment,
                    'bg-violet-50 text-violet-600' => ! $hasPendingAssignment,
                ])>
                    <x-icon name="user-plus" class="h-5 w-5" />
                </div>
                <div class="min-w-0">
                    <div @class(['text-xs font-medium uppercase tracking-wide', 'text-amber-700' => $hasPendingAssignment, 'text-slate-400' => ! $hasPendingAssignment])>Usulan Jadwal Teknisi</div>
                    <div @class(['mt-1 text-2xl font-semibold', 'text-amber-800' => $hasPendingAssignment, 'text-slate-800' => ! $hasPendingAssignment])>{{ $pendingAssignmentApprovals->count() }}</div>
                    @if ($hasPendingAssignment)
                        <div class="mt-0.5 text-xs font-medium text-amber-700">Perlu tindakan Anda &rarr;</div>
                    @endif
                </div>
            </a>
        @endif

        @if ($pendingCashAdvances !== null)
            @php $hasPendingCashAdvance = $pendingCashAdvances->count() > 0; @endphp
            <a
                href="{{ route('admin.cash-advances') }}"
                @class([
                    'relative flex items-start gap-4 rounded-2xl border p-5 shadow-sm transition-colors',
                    'border-amber-200 bg-amber-50 hover:bg-amber-100' => $hasPendingCashAdvance,
                    'border-slate-100 bg-white hover:bg-slate-50' => ! $hasPendingCashAdvance,
                ])
            >
                @if ($hasPendingCashAdvance)
                    <span class="absolute right-3 top-3 flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                    </span>
                @endif
                <div @class([
                    'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl',
                    'bg-amber-100 text-amber-600' => $hasPendingCashAdvance,
                    'bg-violet-50 text-violet-600' => ! $hasPendingCashAdvance,
                ])>
                    <x-icon name="wallet" class="h-5 w-5" />
                </div>
                <div class="min-w-0">
                    <div @class(['text-xs font-medium uppercase tracking-wide', 'text-amber-700' => $hasPendingCashAdvance, 'text-slate-400' => ! $hasPendingCashAdvance])>Kasbon Menunggu Approval</div>
                    <div @class(['mt-1 text-2xl font-semibold', 'text-amber-800' => $hasPendingCashAdvance, 'text-slate-800' => ! $hasPendingCashAdvance])>{{ $pendingCashAdvances->count() }}</div>
                    @if ($hasPendingCashAdvance)
                        <div class="mt-0.5 text-xs font-medium text-amber-700">Perlu tindakan Anda &rarr;</div>
                    @endif
                </div>
            </a>
        @endif

        @if ($pendingMaterials !== null)
            @php
                $hasPendingMaterial = $pendingMaterials->count() > 0;
                $overdueMaterialCount = $pendingMaterials->where('is_overdue', true)->count();
            @endphp
            <button
                type="button"
                wire:click="openPendingMaterial"
                @class([
                    'relative flex items-start gap-4 rounded-2xl border p-5 text-left shadow-sm transition-colors',
                    'border-amber-200 bg-amber-50 hover:bg-amber-100' => $hasPendingMaterial,
                    'border-slate-100 bg-white hover:bg-slate-50' => ! $hasPendingMaterial,
                ])
            >
                <div @class([
                    'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl',
                    'bg-amber-100 text-amber-600' => $hasPendingMaterial,
                    'bg-violet-50 text-violet-600' => ! $hasPendingMaterial,
                ])>
                    <x-icon name="package" class="h-5 w-5" />
                </div>
                <div class="min-w-0">
                    <div @class(['text-xs font-medium uppercase tracking-wide', 'text-amber-700' => $hasPendingMaterial, 'text-slate-400' => ! $hasPendingMaterial])>Material Belum Diterima</div>
                    <div @class(['mt-1 text-2xl font-semibold', 'text-amber-800' => $hasPendingMaterial, 'text-slate-800' => ! $hasPendingMaterial])>{{ $pendingMaterials->count() }}</div>
                    @if ($overdueMaterialCount > 0)
                        <div class="mt-0.5 text-xs font-semibold text-red-700">{{ $overdueMaterialCount }} sudah lewat estimasi tiba (H-7) &rarr;</div>
                    @elseif ($hasPendingMaterial)
                        <div class="mt-0.5 text-xs font-medium text-amber-700">Klik untuk lihat daftar &rarr;</div>
                    @endif
                </div>
            </button>
        @endif

        @if ($simlokReminders !== null)
            @php $hasSimlokReminder = $simlokReminders->count() > 0; @endphp
            <a
                href="{{ route('projects.index') }}"
                @class([
                    'relative flex items-start gap-4 rounded-2xl border p-5 shadow-sm transition-colors',
                    'border-amber-200 bg-amber-50 hover:bg-amber-100' => $hasSimlokReminder,
                    'border-slate-100 bg-white hover:bg-slate-50' => ! $hasSimlokReminder,
                ])
            >
                @if ($hasSimlokReminder)
                    <span class="absolute right-3 top-3 flex h-2.5 w-2.5">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-amber-500"></span>
                    </span>
                @endif
                <div @class([
                    'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl',
                    'bg-amber-100 text-amber-600' => $hasSimlokReminder,
                    'bg-violet-50 text-violet-600' => ! $hasSimlokReminder,
                ])>
                    <x-icon name="shield" class="h-5 w-5" />
                </div>
                <div class="min-w-0">
                    <div @class(['text-xs font-medium uppercase tracking-wide', 'text-amber-700' => $hasSimlokReminder, 'text-slate-400' => ! $hasSimlokReminder])>Simlok &amp; SIKA Belum Ada (H-3)</div>
                    <div @class(['mt-1 text-2xl font-semibold', 'text-amber-800' => $hasSimlokReminder, 'text-slate-800' => ! $hasSimlokReminder])>{{ $simlokReminders->count() }}</div>
                    @if ($hasSimlokReminder)
                        <div class="mt-0.5 text-xs font-medium text-amber-700">Activity mau mulai, izin belum diupload &rarr;</div>
                    @endif
                </div>
            </a>
        @endif
    </div>

    {{-- ===== Panel aksi cepat ===== --}}
    @if (($todaysAssignments !== null && $todaysAssignments->isNotEmpty()) || ($pendingApprovals !== null && $pendingApprovals->isNotEmpty()) || ($pendingAssignmentApprovals !== null && $pendingAssignmentApprovals->isNotEmpty()) || ($pendingCashAdvances !== null && $pendingCashAdvances->isNotEmpty()) || ($simlokReminders !== null && $simlokReminders->isNotEmpty()))
        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
            @if ($todaysAssignments !== null && $todaysAssignments->isNotEmpty())
                <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                    <h3 class="mb-3 text-sm font-semibold text-slate-700">Jadwal Anda Hari Ini</h3>
                    @foreach ($todaysAssignments as $a)
                        <div class="flex items-center justify-between border-t border-slate-100 py-2.5 text-sm first:border-0">
                            <div class="min-w-0">
                                <div class="truncate font-medium text-slate-800">{{ $a->activity->name }}</div>
                                <div class="truncate text-slate-500">{{ $a->activity->project->name }}</div>
                            </div>
                            <a href="{{ route('teknisi.report.create') }}" class="shrink-0 text-indigo-600 hover:underline">Isi Laporan</a>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($pendingApprovals !== null && $pendingApprovals->isNotEmpty())
                <div class="rounded-2xl border border-amber-200 bg-amber-50/40 p-5 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            Menunggu Persetujuan Anda
                            <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-amber-500 px-1.5 text-[11px] font-semibold text-white">{{ $pendingApprovals->count() }}</span>
                        </h3>
                        <a href="{{ route('purchasing.rfq', ['statusFilter' => 'submitted']) }}" class="text-xs font-medium text-indigo-600 hover:underline">Lihat semua</a>
                    </div>
                    @foreach ($pendingApprovals as $q)
                        <div class="flex items-center justify-between border-t border-amber-100 py-2.5 text-sm first:border-0">
                            <div class="min-w-0">
                                <div class="truncate font-medium text-slate-800">{{ $q->code }}</div>
                                <div class="truncate text-slate-500">{{ $q->project->name }}</div>
                            </div>
                            <a href="{{ route('purchasing.rfq.show', $q) }}" class="shrink-0 rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-600">Tinjau &amp; Setujui</a>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($pendingAssignmentApprovals !== null && $pendingAssignmentApprovals->isNotEmpty())
                <div class="rounded-2xl border border-amber-200 bg-amber-50/40 p-5 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            Usulan Jadwal Lead Technician
                            <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-amber-500 px-1.5 text-[11px] font-semibold text-white">{{ $pendingAssignmentApprovals->count() }}</span>
                        </h3>
                        <a href="{{ route('projects.index') }}" class="text-xs font-medium text-indigo-600 hover:underline">Lihat semua proyek</a>
                    </div>
                    @foreach ($pendingAssignmentApprovals as $a)
                        <div class="flex items-center justify-between border-t border-amber-100 py-2.5 text-sm first:border-0">
                            <div class="min-w-0">
                                <div class="truncate font-medium text-slate-800">{{ $a->user->name }} &middot; {{ $a->scheduled_date->format('d M Y') }}</div>
                                <div class="truncate text-slate-500">{{ $a->activity->name }} &mdash; {{ $a->activity->project->name }}</div>
                                <div class="truncate text-[11px] text-slate-400">Diusulkan oleh {{ $a->creator->name ?? '-' }}</div>
                            </div>
                            <a href="{{ route('projects.show', $a->activity->project) }}" class="shrink-0 rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-600">Tinjau &amp; Setujui</a>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($pendingCashAdvances !== null && $pendingCashAdvances->isNotEmpty())
                <div class="rounded-2xl border border-amber-200 bg-amber-50/40 p-5 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            Kasbon Menunggu Approval
                            <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-amber-500 px-1.5 text-[11px] font-semibold text-white">{{ $pendingCashAdvances->count() }}</span>
                        </h3>
                        <a href="{{ route('admin.cash-advances') }}" class="text-xs font-medium text-indigo-600 hover:underline">Lihat semua</a>
                    </div>
                    @foreach ($pendingCashAdvances as $ca)
                        <div class="flex items-center justify-between border-t border-amber-100 py-2.5 text-sm first:border-0">
                            <div class="min-w-0">
                                <div class="truncate font-medium text-slate-800">{{ $ca->requester->name }} &middot; Rp {{ number_format((float) $ca->amount_requested, 0, ',', '.') }}</div>
                                <div class="truncate text-slate-500">{{ $ca->project->name ?? 'Tanpa proyek spesifik' }}</div>
                            </div>
                            <a href="{{ route('admin.cash-advances') }}" class="shrink-0 rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-600">Tinjau &amp; Setujui</a>
                        </div>
                    @endforeach
                </div>
            @endif

            @if ($simlokReminders !== null && $simlokReminders->isNotEmpty())
                <div class="rounded-2xl border border-amber-200 bg-amber-50/40 p-5 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <h3 class="flex items-center gap-2 text-sm font-semibold text-slate-700">
                            Simlok &amp; SIKA Belum Ada (H-3)
                            <span class="inline-flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-amber-500 px-1.5 text-[11px] font-semibold text-white">{{ $simlokReminders->count() }}</span>
                        </h3>
                        <a href="{{ route('projects.index') }}" class="text-xs font-medium text-indigo-600 hover:underline">Lihat semua proyek</a>
                    </div>
                    @foreach ($simlokReminders as $activity)
                        <div class="flex items-center justify-between border-t border-amber-100 py-2.5 text-sm first:border-0">
                            <div class="min-w-0">
                                <div class="truncate font-medium text-slate-800">{{ $activity->name }} &middot; mulai {{ $activity->start_date->format('d M Y') }}</div>
                                <div class="truncate text-slate-500">{{ $activity->project->name }}</div>
                            </div>
                            <a href="{{ route('projects.show', $activity->project) }}" class="shrink-0 rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-600">Upload Izin</a>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    {{-- ===== Grafik ===== --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        @if ($workHoursTrend !== null)
            @php
                $chartW = 700; $chartH = 220; $padL = 6; $padR = 6; $padT = 12; $padB = 24;
                $plotW = $chartW - $padL - $padR;
                $plotH = $chartH - $padT - $padB;
                $maxHours = max(1, ceil($workHoursTrend->max('hours')));
                $n = $workHoursTrend->count();
                $pts = $workHoursTrend->values()->map(function ($row, $i) use ($padL, $padT, $plotW, $plotH, $maxHours, $n) {
                    $x = $padL + ($n > 1 ? ($i / ($n - 1)) * $plotW : $plotW / 2);
                    $y = $padT + $plotH - ($maxHours > 0 ? ($row['hours'] / $maxHours) * $plotH : 0);
                    return ['x' => round($x, 1), 'y' => round($y, 1), 'row' => $row];
                });
                $polyline = $pts->map(fn ($p) => $p['x'].','.$p['y'])->join(' ');
                $baseline = $padT + $plotH;
                $areaPath = $pts->isNotEmpty()
                    ? 'M'.$pts->first()['x'].','.$baseline.' L'.$polyline.' L'.$pts->last()['x'].','.$baseline.' Z'
                    : '';
                $lastPoint = $pts->last();
            @endphp
            <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm lg:col-span-2">
                <div class="mb-1 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-700">Tren {{ $workHoursTrendLabel }} &middot; 14 Hari Terakhir</h3>
                    <span class="text-xs text-slate-400">dalam jam / hari</span>
                </div>
                @if ($pts->isNotEmpty() && $workHoursTrend->sum('hours') > 0)
                    <svg viewBox="0 0 {{ $chartW }} {{ $chartH }}" preserveAspectRatio="none" class="mt-2 h-48 w-full" role="img" aria-label="Grafik tren {{ $workHoursTrendLabel }} 14 hari terakhir">
                        @for ($g = 0; $g <= 3; $g++)
                            @php $gy = $padT + $plotH - ($g / 3) * $plotH; @endphp
                            <line x1="{{ $padL }}" y1="{{ $gy }}" x2="{{ $chartW - $padR }}" y2="{{ $gy }}" stroke="#e1e0d9" stroke-width="1" vector-effect="non-scaling-stroke" />
                        @endfor

                        <path d="{{ $areaPath }}" fill="{{ \App\Livewire\Dashboard::SEQUENTIAL_HUE }}" opacity="0.1" stroke="none" />
                        <polyline points="{{ $polyline }}" fill="none" stroke="{{ \App\Livewire\Dashboard::SEQUENTIAL_HUE }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" vector-effect="non-scaling-stroke" />

                        @foreach ($pts as $p)
                            <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="8" fill="transparent" stroke="none">
                                <title>{{ $p['row']['label'] }}: {{ $p['row']['hours'] }} jam</title>
                            </circle>
                            <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="2.5" fill="{{ \App\Livewire\Dashboard::SEQUENTIAL_HUE }}" stroke="#fff" stroke-width="1" />
                        @endforeach

                        @if ($lastPoint)
                            <text x="{{ $lastPoint['x'] - 8 }}" y="{{ max(12, $lastPoint['y'] - 10) }}" text-anchor="end" font-size="12" font-weight="600" fill="#0b0b0b">{{ $lastPoint['row']['hours'] }} jam</text>
                        @endif
                    </svg>
                    <div class="mt-1 flex justify-between text-[11px] text-slate-400">
                        @foreach ($pts as $i => $p)
                            @if ($i % 2 === 0 || $i === $pts->count() - 1)
                                <span>{{ $p['row']['label'] }}</span>
                            @endif
                        @endforeach
                    </div>
                @else
                    <div class="flex h-48 items-center justify-center text-sm text-slate-400">Belum ada data jam kerja pada periode ini.</div>
                @endif
            </div>
        @endif

        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm {{ $workHoursTrend !== null ? '' : 'lg:col-span-3' }}">
            <h3 class="mb-4 text-sm font-semibold text-slate-700">Distribusi Status Proyek</h3>
            <div class="space-y-3">
                @foreach ($statusBreakdown as $row)
                    <div>
                        <div class="mb-1 flex items-center justify-between text-xs">
                            <span class="font-medium text-slate-600">{{ $row['label'] }}</span>
                            <span class="text-slate-400">{{ $row['count'] }}</span>
                        </div>
                        <div class="h-2.5 w-full overflow-hidden rounded-full bg-slate-100">
                            <div class="h-2.5 rounded-full transition-all" style="width: {{ ($row['count'] / $maxStatusCount) * 100 }}%; background-color: {{ $row['color'] }};"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ===== Progress proyek teratas ===== --}}
    @if ($topProjectsProgress->isNotEmpty())
        <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-slate-700">Proyek dengan Progress Tertinggi</h3>
            <div class="space-y-3">
                @foreach ($topProjectsProgress as $row)
                    <div class="flex items-center gap-3">
                        <div class="w-40 shrink-0 truncate text-sm text-slate-600" title="{{ $row['name'] }}">{{ $row['name'] }}</div>
                        <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-slate-100">
                            <div class="h-2.5 rounded-full bg-indigo-500" style="width: {{ $row['percent'] }}%"></div>
                        </div>
                        <div class="w-10 shrink-0 text-right text-sm font-medium text-slate-700">{{ $row['percent'] }}%</div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ===== Proyek terbaru ===== --}}
    <div class="rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-700">Proyek Terbaru</h3>
            <a href="{{ route('projects.index') }}" class="text-sm text-indigo-600 hover:underline">Lihat semua</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">#</th>
                        <th class="pb-2">Proyek</th>
                        <th class="pb-2">Lokasi</th>
                        <th class="pb-2">Status</th>
                        <th class="pb-2">Progress</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($projects as $p)
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2.5 text-slate-400">{{ $loop->iteration }}</td>
                            <td class="py-2.5"><a href="{{ route('projects.show', $p) }}" class="font-medium text-indigo-600 hover:underline">{{ $p->name }}</a></td>
                            <td class="py-2.5 text-slate-500">{{ $p->unit->name }} &middot; {{ $p->unit->region->name }}</td>
                            <td class="py-2.5">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2 py-0.5 text-xs">
                                    <span class="h-1.5 w-1.5 rounded-full" style="background-color: {{ \App\Livewire\Dashboard::STATUS_COLORS[$p->status] ?? '#898781' }}"></span>
                                    {{ \App\Models\Project::STATUSES[$p->status] ?? $p->status }}
                                </span>
                            </td>
                            <td class="py-2.5">
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-28 overflow-hidden rounded-full bg-slate-100">
                                        <div class="h-2 rounded-full bg-indigo-500" style="width: {{ $p->progress_percent }}%"></div>
                                    </div>
                                    <span class="text-xs text-slate-500">{{ $p->progress_percent }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty-state icon="briefcase" title="Belum ada proyek." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($showPendingMaterialModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showPendingMaterialModal', false)">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl bg-white p-6 shadow-xl">
                <div class="mb-1 flex items-center justify-between">
                    <h3 class="flex items-center gap-2 text-lg font-semibold text-slate-800">
                        <x-icon name="package" class="h-5 w-5 text-amber-500" /> Material Belum Diterima
                    </h3>
                    <button type="button" wire:click="$set('showPendingMaterialModal', false)" class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600">
                        <x-icon name="x-mark" class="h-5 w-5" />
                    </button>
                </div>

                @if ($pendingMaterials->isEmpty())
                    <x-empty-state icon="package" title="Semua material sudah diterima. Tidak ada yang tertunda." />
                @else
                    <p class="mb-3 text-xs text-slate-400">Material yang sudah dipesan (status "Ordered"/"Shipping") namun belum berstatus diterima, diurutkan dari yang paling lama dipesan.</p>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="text-xs uppercase text-slate-400">
                                <tr>
                                    <th class="pb-2">Item</th>
                                    <th class="pb-2">Proyek</th>
                                    <th class="pb-2">No. PO</th>
                                    <th class="pb-2">Tanggal Pesan</th>
                                    <th class="pb-2">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pendingMaterials as $m)
                                    @php $po = $m->purchaseOrderItem?->purchaseOrder; @endphp
                                    <tr @class([
                                        'border-t border-slate-100 transition-colors hover:bg-slate-50/70',
                                        'bg-red-50/60' => $m->is_overdue,
                                    ])>
                                        <td class="py-2 font-medium">{{ $m->item->name }}</td>
                                        <td class="py-2">
                                            <a href="{{ route('projects.show', $m->project) }}" class="text-indigo-600 hover:underline">{{ $m->project->name }}</a>
                                        </td>
                                        <td class="py-2 text-slate-500">
                                            @if ($po)
                                                <a href="{{ route('purchasing.po.print', $po) }}" target="_blank" class="text-indigo-600 hover:underline">{{ $po->code }}</a>
                                            @else
                                                <span class="text-slate-300">-</span>
                                            @endif
                                        </td>
                                        <td class="py-2 text-slate-500">{{ $po?->created_at?->format('d M Y') ?? '-' }}</td>
                                        <td class="py-2">
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs text-amber-700">
                                                <x-icon name="clock" class="h-3 w-3" /> {{ \App\Models\MaterialTracking::STATUSES[$m->status] }}
                                            </span>
                                            @if ($m->is_overdue)
                                                <span class="ml-1 inline-flex items-center gap-1 rounded-full bg-red-100 px-1.5 py-0.5 text-[10px] font-semibold text-red-700">
                                                    <x-icon name="alert-circle" class="h-3 w-3" /> Terlambat
                                                </span>
                                            @elseif ($m->is_due_soon)
                                                <span class="ml-1 inline-flex items-center gap-1 rounded-full bg-amber-100 px-1.5 py-0.5 text-[10px] font-semibold text-amber-700">
                                                    H-7
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 flex justify-end border-t border-slate-200 pt-3">
                        <a href="{{ route('purchasing.tracking') }}" class="text-sm font-medium text-indigo-600 hover:underline">Buka Material Tracking &rarr;</a>
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
