@extends('layouts.website')

@section('title', 'Portofolio -- '.$settings->company_name)
@section('meta_description', 'Proyek-proyek yang telah ditangani '.$settings->company_name)

@section('content')

    <section class="bg-brand-blue-950">
        <div class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <span class="mx-auto mb-3 block h-1 w-14 rounded-full bg-brand-red-500"></span>
            <h1 class="text-3xl font-bold text-white sm:text-4xl">Portofolio Proyek</h1>
            <p class="mx-auto mt-3 max-w-2xl text-sm text-slate-300">Sebagian proyek yang pernah kami tangani di berbagai sektor industri.</p>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        @if ($portfolioItems->isEmpty())
            <x-empty-state icon="doc-text" title="Belum ada portofolio yang ditampilkan." />
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($portfolioItems as $item)
                    <div class="overflow-hidden rounded-2xl border border-slate-200">
                        <div class="flex h-44 items-center justify-center bg-slate-100">
                            @if ($item->image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($item->image_path) }}" alt="{{ $item->title }}" class="h-full w-full object-cover">
                            @else
                                <x-icon name="doc-text" class="h-12 w-12 text-slate-300" />
                            @endif
                        </div>
                        <div class="p-5">
                            <div class="flex items-center gap-2 text-xs font-medium text-brand-blue-600">
                                @if ($item->category)<span>{{ $item->category }}</span>@endif
                                @if ($item->year)<span class="text-slate-300">·</span><span class="text-slate-400">{{ $item->year }}</span>@endif
                            </div>
                            <h3 class="mt-2 text-base font-semibold text-slate-900">{{ $item->title }}</h3>
                            @if ($item->description)
                                <p class="mt-1.5 text-sm leading-relaxed text-slate-500">{{ $item->description }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

@endsection
