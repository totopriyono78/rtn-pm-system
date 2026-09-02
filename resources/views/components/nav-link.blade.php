@props(['href', 'active' => false, 'icon' => 'home', 'label' => ''])
<a
    href="{{ $href }}"
    title="{{ $label }}"
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
    <span @class(['shrink-0', 'text-indigo-400' => $active, 'text-slate-500 group-hover:text-slate-300' => ! $active])>
        <x-icon :name="$icon" />
    </span>
    <span x-show="!collapsed" x-transition.opacity.duration.150ms class="truncate">{{ $label }}</span>
</a>
