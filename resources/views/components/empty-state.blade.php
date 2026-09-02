{{--
    State kosong bergaya (ikon lingkaran + teks) untuk dipakai di dalam sel
    tabel (<td colspan="...">) atau area kosong lain, menggantikan teks abu-abu
    polos supaya halaman tidak terasa flat.
--}}
@props(['icon' => 'inbox', 'title'])
<div class="flex flex-col items-center justify-center gap-2 py-8 text-center">
    <div class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-slate-400">
        <x-icon :name="$icon" class="h-5 w-5" />
    </div>
    <p class="text-sm text-slate-400">{{ $title }}</p>
</div>
