<div class="space-y-6">
    <div>
        <h1 class="text-xl font-semibold text-slate-800">Selamat datang, {{ $client->name }}</h1>
        <p class="text-sm text-slate-500">Daftar proyek {{ $client->customer->name ?? '-' }} bersama PT RTN.</p>
    </div>

    <div class="grid gap-4">
        @forelse ($projects as $project)
            <a href="{{ route('client.projects.show', $project) }}"
                class="block rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition-colors hover:border-indigo-200 hover:bg-indigo-50/30">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="truncate text-base font-semibold text-slate-800">{{ $project->name }}</div>
                        <div class="mt-1 flex items-center gap-2 text-xs text-slate-500">
                            @if ($project->unit)
                                <span class="inline-flex items-center gap-1"><x-icon name="map-pin" class="h-3.5 w-3.5" /> {{ $project->unit->name }}</span>
                            @endif
                            @if ($project->pic)
                                <span>&middot; PIC: {{ $project->pic->name }}</span>
                            @endif
                        </div>
                    </div>
                    <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium
                        @class([
                            'bg-sky-100 text-sky-700' => $project->status === 'planning',
                            'bg-amber-100 text-amber-700' => $project->status === 'ongoing',
                            'bg-emerald-100 text-emerald-700' => $project->status === 'completed',
                            'bg-slate-200 text-slate-600' => $project->status === 'on_hold',
                        ])">
                        {{ \App\Models\Project::STATUSES[$project->status] ?? $project->status }}
                    </span>
                </div>

                <div class="mt-4">
                    <div class="mb-1 flex items-center justify-between text-xs text-slate-500">
                        <span>Progres</span>
                        <span class="font-medium text-slate-700">{{ $project->progress_percent }}%</span>
                    </div>
                    <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                        <div class="h-2 rounded-full bg-indigo-500" style="width: {{ $project->progress_percent }}%"></div>
                    </div>
                </div>

                <div class="mt-3 flex items-center gap-1 text-xs text-slate-400">
                    <x-icon name="calendar" class="h-3.5 w-3.5" />
                    {{ $project->start_date?->translatedFormat('d M Y') ?? '-' }}
                    &ndash;
                    {{ $project->end_date?->translatedFormat('d M Y') ?? 'Belum ditentukan' }}
                </div>
            </a>
        @empty
            <div class="rounded-xl border border-slate-200 bg-white">
                <x-empty-state icon="briefcase" title="Belum ada proyek yang terdaftar untuk perusahaan Anda." />
            </div>
        @endforelse
    </div>
</div>
