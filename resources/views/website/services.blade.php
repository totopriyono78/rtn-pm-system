@extends('layouts.website')

@section('title', 'Layanan -- '.$settings->company_name)
@section('meta_description', 'Layanan service & maintenance serta sales & pengadaan pompa dari '.$settings->company_name)

@section('content')

    <section class="bg-brand-blue-950">
        <div class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <span class="mx-auto mb-3 block h-1 w-14 rounded-full bg-brand-red-500"></span>
            <h1 class="text-3xl font-bold text-white sm:text-4xl">Layanan Kami</h1>
            <p class="mx-auto mt-3 max-w-2xl text-sm text-slate-300">Dua lini layanan utama yang mendukung keandalan operasional pompa di fasilitas Anda.</p>
        </div>
    </section>

    <section class="mx-auto max-w-6xl space-y-16 px-4 py-16 sm:px-6 lg:px-8">
        @foreach (\App\Models\WebsiteService::DIVISIONS as $divisionKey => $divisionLabel)
            @php($items = $servicesByDivision->get($divisionKey, collect()))
            @if ($items->isNotEmpty())
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">{{ $divisionLabel }}</h2>
                    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($items as $service)
                            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-6">
                                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-blue-600 text-white">
                                    <x-icon name="{{ $service->icon ?: 'check' }}" class="h-5 w-5" />
                                </div>
                                <h3 class="mt-4 text-sm font-semibold text-slate-900">{{ $service->name }}</h3>
                                @if ($service->short_description)
                                    <p class="mt-2 text-xs leading-relaxed text-slate-500">{{ $service->short_description }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        @endforeach

        @if ($servicesByDivision->isEmpty())
            <x-empty-state icon="briefcase" title="Belum ada layanan yang ditampilkan." />
        @endif

        <div class="rounded-2xl bg-brand-blue-600 p-10 text-center">
            <h2 class="text-xl font-bold text-white">Diskusikan Kebutuhan Layanan Anda</h2>
            <p class="mx-auto mt-1 max-w-xl text-sm text-brand-blue-100">Jadwalkan assessment atau konsultasi kebutuhan pengadaan pompa bersama tim kami.</p>
            <a href="{{ route('website.contact') }}" class="mt-5 inline-block rounded-lg bg-white px-6 py-2.5 text-sm font-semibold text-brand-blue-600 hover:bg-brand-blue-50">Hubungi Kami</a>
        </div>
    </section>

@endsection
