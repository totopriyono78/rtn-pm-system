<div class="max-w-2xl space-y-6">
    <x-page-header icon="map-pin" color="emerald" title="Presensi" subtitle="Check-in wajib dilakukan di lokasi site sebelum Anda bisa mengisi laporan untuk penugasan hari ini." />

    <div class="space-y-3">
        @forelse ($assignments as $a)
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="font-medium text-slate-800">{{ $a->activity->name }}</div>
                        <div class="text-xs text-slate-500">{{ $a->activity->project->name }}</div>
                        @if ($a->activity->site)
                            <div class="mt-1 flex items-center gap-1 text-xs text-sky-700">
                                <x-icon name="map-pin" class="h-3.5 w-3.5" /> {{ $a->activity->site->name }}
                                <span class="text-slate-400">(radius {{ $a->activity->site->radius_meters }} m)</span>
                            </div>
                        @else
                            <div class="mt-1 flex items-center gap-1 text-xs text-amber-600">
                                <x-icon name="alert-circle" class="h-3.5 w-3.5" /> Belum ada Site — hubungi PM.
                            </div>
                        @endif
                    </div>
                    <div class="shrink-0">
                        @if ($a->attendance)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-medium text-emerald-700">
                                <x-icon name="check" class="h-3.5 w-3.5" />
                                Check-in {{ $a->attendance->checked_in_at->format('H:i') }}
                                @if ($a->attendance->status === 'overridden')
                                    (override)
                                @endif
                            </span>
                        @elseif ($a->activity->site)
                            <button type="button"
                                x-on:click="
                                    if (! navigator.geolocation) { alert('Browser ini tidak mendukung GPS.'); return; }
                                    navigator.geolocation.getCurrentPosition(
                                        (pos) => $wire.doCheckIn({{ $a->id }}, pos.coords.latitude, pos.coords.longitude, pos.coords.accuracy),
                                        (err) => $wire.geolocationError(err.message),
                                        { enableHighAccuracy: true, timeout: 15000 }
                                    )
                                "
                                class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-500">
                                <x-icon name="map-pin" class="h-4 w-4" /> Check-in Sekarang
                            </button>
                        @endif
                    </div>
                </div>
                @if ($a->attendance)
                    <p class="mt-2 text-[11px] text-slate-400">Jarak dari site saat check-in: {{ number_format($a->attendance->distance_meters, 0) }} m.</p>
                @endif
            </div>
        @empty
            <x-empty-state icon="calendar" title="Tidak ada penugasan untuk hari ini." />
        @endforelse
    </div>

    <p class="text-xs text-slate-400">Presensi memakai lokasi GPS perangkat Anda saat ini — pastikan izin lokasi browser sudah diaktifkan dan Anda benar-benar berada di lokasi site.</p>
</div>
