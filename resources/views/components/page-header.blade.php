{{--
    Header standar untuk setiap halaman: kotak ikon berwarna + judul + sub judul.
    Dipakai supaya semua halaman punya identitas visual yang sama (tidak flat)
    dan konsisten dengan gaya kartu statistik di Dashboard.

    $color memetakan ke string class Tailwind LENGKAP (bukan hasil interpolasi
    nama warna) supaya tetap ter-scan oleh Tailwind JIT/v4 content scanner.
--}}
@props(['icon' => 'briefcase', 'title', 'subtitle' => null, 'color' => 'indigo'])
@php
    $colorMap = [
        'indigo' => 'bg-indigo-50 text-indigo-600',
        'emerald' => 'bg-emerald-50 text-emerald-600',
        'amber' => 'bg-amber-50 text-amber-600',
        'orange' => 'bg-orange-50 text-orange-500',
        'violet' => 'bg-violet-50 text-violet-600',
        'sky' => 'bg-sky-50 text-sky-600',
        'rose' => 'bg-rose-50 text-rose-600',
        'slate' => 'bg-slate-100 text-slate-600',
    ];
    $colorClass = $colorMap[$color] ?? $colorMap['indigo'];
@endphp
<div {{ $attributes->class(['flex items-center gap-3']) }}>
    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl {{ $colorClass }}">
        <x-icon :name="$icon" class="h-5 w-5" />
    </div>
    <div class="min-w-0">
        <h2 class="truncate text-xl font-semibold text-slate-800">{{ $title }}</h2>
        @if ($subtitle)
            <p class="text-sm text-slate-500">{{ $subtitle }}</p>
        @endif
    </div>
</div>
