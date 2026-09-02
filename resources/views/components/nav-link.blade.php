@props(['href', 'active' => false, 'icon' => 'home', 'label' => '', 'badge' => null])
<a
    href="{{ $href }}"
    title="{{ $label }}{{ $badge ? ' (' . $badge . ' menunggu)' : '' }}"
    {{ $attributes->class([
        'group relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150',
        'bg-white/10 text-white shadow-sm' => $active,
        'text-slate-400 hover:bg-white/5 hover:text-slate-100' => ! $active,
    ]) }}
>
    <span
        @class([
            'pointer-events-none absolute left-0 top-1/2 h-5 w-0.5 -translate-y-1/2 rounded-r bg-indigo-400' => $active,
        ])
    ></span>
    <span class="relative shrink-0" @class(['text-indigo-400' => $active, 'text-slate-500 group-hover:text-slate-300' => ! $active])>
        <x-icon :name="$icon" />
        @if ($badge)
            {{-- Titik merah kecil di ikon: tetap terlihat walau sidebar diciutkan. --}}
            <span x-show="collapsed" class="absolute -right-1 -top-1 flex h-2.5 w-2.5">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-red-500 ring-2 ring-slate-900"></span>
            </span>
        @endif
    </span>
    <span x-show="!collapsed" x-transition.opacity.duration.150ms class="flex min-w-0 flex-1 items-center justify-between gap-2">
        <span class="truncate">{{ $label }}</span>
        @if ($badge)
            <span class="inline-flex h-5 min-w-[1.25rem] shrink-0 items-center justify-center rounded-full bg-red-500 px-1.5 text-[11px] font-semibold text-white">{{ $badge }}</span>
        @endif
    </span>
</a>
