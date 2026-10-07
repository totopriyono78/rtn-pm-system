@extends('layouts.website')

@section('title', 'Tentang Kami -- '.$settings->company_name)
@section('meta_description', 'Profil, visi, dan misi '.$settings->company_name)

@section('content')

    <section class="bg-brand-blue-950">
        <div class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <span class="mx-auto mb-3 block h-1 w-14 rounded-full bg-brand-red-500"></span>
            <h1 class="text-3xl font-bold text-white sm:text-4xl">Tentang {{ $settings->company_name }}</h1>
            <p class="mx-auto mt-3 max-w-2xl text-sm text-slate-300">{{ $settings->tagline }}</p>
        </div>
    </section>

    <section class="mx-auto max-w-4xl px-4 py-16 sm:px-6 lg:px-8">
        @if ($settings->about)
            <p class="text-base leading-relaxed text-slate-600">{{ $settings->about }}</p>
        @endif

        <div class="mt-12 grid gap-6 sm:grid-cols-2">
            @if ($settings->vision)
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-7">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-blue-600 text-white"><x-icon name="eye" class="h-5 w-5" /></div>
                    <h2 class="mt-4 text-lg font-semibold text-slate-900">Visi</h2>
                    <p class="mt-2 text-sm leading-relaxed text-slate-600">{{ $settings->vision }}</p>
                </div>
            @endif
            @if ($settings->mission)
                <div class="rounded-2xl border border-slate-100 bg-slate-50 p-7">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-blue-600 text-white"><x-icon name="check" class="h-5 w-5" /></div>
                    <h2 class="mt-4 text-lg font-semibold text-slate-900">Misi</h2>
                    <ul class="mt-2 space-y-2 text-sm leading-relaxed text-slate-600">
                        @foreach (preg_split('/\r\n|\r|\n/', trim($settings->mission)) as $line)
                            @if (trim($line) !== '')
                                <li class="flex items-start gap-2"><x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-brand-blue-500" /> {{ $line }}</li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="mt-12 grid gap-6 sm:grid-cols-2">
            <a href="{{ route('website.services') }}" class="group rounded-2xl border border-slate-100 p-7 transition-colors hover:border-brand-blue-200 hover:bg-brand-blue-50/50">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-500 group-hover:bg-brand-blue-600 group-hover:text-white"><x-icon name="refresh" class="h-5 w-5" /></div>
                <h3 class="mt-4 text-base font-semibold text-slate-900">Service & Maintenance Pompa</h3>
                <p class="mt-1 text-sm text-slate-500">Lihat cakupan layanan perawatan kami →</p>
            </a>
            <a href="{{ route('website.services') }}" class="group rounded-2xl border border-slate-100 p-7 transition-colors hover:border-brand-blue-200 hover:bg-brand-blue-50/50">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-500 group-hover:bg-brand-blue-600 group-hover:text-white"><x-icon name="truck" class="h-5 w-5" /></div>
                <h3 class="mt-4 text-base font-semibold text-slate-900">Sales & Pengadaan Pompa Baru</h3>
                <p class="mt-1 text-sm text-slate-500">Lihat cakupan layanan pengadaan kami →</p>
            </a>
        </div>
    </section>

@endsection
