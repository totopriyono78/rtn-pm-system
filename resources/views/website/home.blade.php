@extends('layouts.website')

@section('title', $settings->company_name.' -- '.($settings->tagline ?: 'Solusi Service, Maintenance & Pengadaan Pompa Industri'))
@section('meta_description', $settings->tagline)

@section('content')

    {{-- ===== Hero / Slide Beranda ===== --}}
    @if ($slides->isNotEmpty())
        {{-- Carousel: bergeser otomatis, bisa juga digeser manual (drag mouse / swipe layar sentuh).
             Dirender di server (tanpa Alpine/eval) -- lihat resources/js/hero-carousel.js untuk
             perilaku interaktifnya. Ini sengaja TIDAK pakai Alpine.js karena direktif Alpine (x-data,
             x-show, dst) butuh eval JavaScript di browser, yang diblokir oleh sebagian antivirus/
             Content-Security-Policy (mis. Kaspersky Web Anti-Virus) -- carousel versi Alpine sempat
             blank total di kondisi itu. Versi ini aman untuk semua pengunjung. --}}
        <section id="hero-carousel" data-autoplay="6000" class="relative select-none overflow-hidden bg-brand-blue-950">
            <div id="hero-carousel-track" class="relative h-[460px] touch-pan-y sm:h-[520px] lg:h-[600px]">
                @foreach ($slides as $index => $slide)
                    <div
                        class="hero-slide absolute inset-0 transition-all duration-700 ease-out {{ $index === 0 ? 'z-10 opacity-100 translate-x-0' : 'opacity-0 translate-x-12 pointer-events-none' }}"
                        data-slide-index="{{ $index }}"
                    >
                        @if ($slide->image_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($slide->image_path) }}" alt="{{ $slide->title ?? '' }}" class="absolute inset-0 h-full w-full object-cover" draggable="false">
                        @endif
                        <div class="absolute inset-0 bg-gradient-to-t from-brand-blue-950/95 via-brand-blue-950/55 to-brand-blue-950/10"></div>
                        <div class="relative mx-auto flex h-full max-w-6xl flex-col justify-end px-4 pb-16 sm:px-6 lg:px-8">
                            @if ($slide->title)
                                <h1 class="max-w-2xl text-3xl font-bold leading-tight text-white sm:text-4xl lg:text-5xl">{{ $slide->title }}</h1>
                            @endif
                            @if ($slide->subtitle)
                                <p class="mt-4 max-w-xl text-base leading-relaxed text-slate-200">{{ $slide->subtitle }}</p>
                            @endif
                            @if ($slide->link_url && $slide->link_label)
                                <div class="mt-6">
                                    <a href="{{ $slide->link_url }}" class="inline-block rounded-lg bg-brand-blue-600 px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-brand-blue-500">{{ $slide->link_label }}</a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach

                @if ($slides->count() > 1)
                    <button type="button" id="hero-carousel-prev" aria-label="Slide sebelumnya" class="absolute left-3 top-1/2 z-20 -translate-y-1/2 rounded-full bg-white/15 p-2 text-white backdrop-blur transition-colors hover:bg-white/25 sm:left-5">
                        <x-icon name="arrow-left" class="h-5 w-5" />
                    </button>
                    <button type="button" id="hero-carousel-next" aria-label="Slide berikutnya" class="absolute right-3 top-1/2 z-20 -translate-y-1/2 rounded-full bg-white/15 p-2 text-white backdrop-blur transition-colors hover:bg-white/25 sm:right-5">
                        <x-icon name="arrow-right" class="h-5 w-5" />
                    </button>
                    <div id="hero-carousel-dots" class="absolute bottom-5 left-1/2 z-20 flex -translate-x-1/2 gap-2">
                        @foreach ($slides as $index => $slide)
                            <button type="button" class="hero-carousel-dot h-2 rounded-full transition-all {{ $index === 0 ? 'w-6 bg-white' : 'w-2 bg-white/40 hover:bg-white/60' }}" data-dot-index="{{ $index }}" aria-label="Ke slide {{ $index + 1 }}"></button>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @else
        {{-- Hero standar -- tampil otomatis kalau belum ada slide yang ditambahkan lewat Kelola Website > Slide Beranda --}}
        <section class="relative overflow-hidden bg-brand-blue-950">
            <div class="absolute inset-0 bg-gradient-to-br from-brand-blue-950 via-brand-blue-900 to-slate-950"></div>
            <div class="relative mx-auto grid max-w-6xl items-center gap-10 px-4 py-20 sm:px-6 lg:grid-cols-2 lg:px-8 lg:py-28">
                <div>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1 text-xs font-medium text-brand-blue-200">
                        <x-icon name="shield" class="h-3.5 w-3.5" /> Service & Maintenance · Sales & Pengadaan Pompa
                    </span>
                    <h1 class="mt-5 text-3xl font-bold leading-tight text-white sm:text-4xl lg:text-5xl">
                        {{ $settings->hero_headline ?: 'Partner Terpercaya untuk Keandalan Pompa Industri Anda' }}
                    </h1>
                    <p class="mt-5 max-w-lg text-base leading-relaxed text-slate-300">
                        {{ $settings->hero_subheadline }}
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="{{ route('website.contact') }}" class="rounded-lg bg-brand-blue-600 px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-brand-blue-500">
                            Konsultasi Kebutuhan Anda
                        </a>
                        <a href="{{ route('website.services') }}" class="rounded-lg border border-white/20 px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-white/10">
                            Lihat Layanan Kami
                        </a>
                    </div>
                </div>
                <div class="relative hidden lg:block">
                    @if ($settings->hero_image_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($settings->hero_image_path) }}" alt="{{ $settings->company_name }}" class="aspect-[4/3] w-full rounded-2xl object-cover shadow-2xl">
                    @else
                        <div class="aspect-[4/3] w-full rounded-2xl border border-white/10 bg-white/5 p-8">
                            <div class="grid h-full grid-cols-2 gap-4">
                                <div class="flex flex-col items-center justify-center gap-2 rounded-xl bg-white/5"><x-icon name="refresh" class="h-8 w-8 text-brand-blue-300" /><span class="text-xs text-slate-300">Preventive Maintenance</span></div>
                                <div class="flex flex-col items-center justify-center gap-2 rounded-xl bg-white/5"><x-icon name="shield" class="h-8 w-8 text-brand-blue-300" /><span class="text-xs text-slate-300">Overhaul</span></div>
                                <div class="flex flex-col items-center justify-center gap-2 rounded-xl bg-white/5"><x-icon name="truck" class="h-8 w-8 text-brand-blue-300" /><span class="text-xs text-slate-300">Pengadaan Unit</span></div>
                                <div class="flex flex-col items-center justify-center gap-2 rounded-xl bg-white/5"><x-icon name="check" class="h-8 w-8 text-brand-blue-300" /><span class="text-xs text-slate-300">Commissioning</span></div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    {{-- ===== 2 Divisi ===== --}}
    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="text-center">
            <h2 class="text-2xl font-bold text-slate-900 sm:text-3xl">Dua Lini Layanan Utama</h2>
            <p class="mx-auto mt-2 max-w-xl text-sm text-slate-500">Mendukung siklus hidup pompa industri Anda secara menyeluruh, dari pengadaan hingga perawatan jangka panjang.</p>
        </div>
        <div class="mt-10 grid gap-6 sm:grid-cols-2">
            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-8">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-blue-600 text-white"><x-icon name="refresh" class="h-6 w-6" /></div>
                <h3 class="mt-4 text-lg font-semibold text-slate-900">Service & Maintenance Pompa</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-600">Assessment, preventive maintenance, corrective maintenance & overhaul, hingga commissioning -- menjaga pompa Anda tetap andal beroperasi.</p>
                <a href="{{ route('website.services') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-blue-600 hover:text-brand-blue-500">Selengkapnya <x-icon name="arrow-right" class="h-4 w-4" /></a>
            </div>
            <div class="rounded-2xl border border-slate-100 bg-slate-50 p-8">
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-brand-red-600 text-white"><x-icon name="truck" class="h-6 w-6" /></div>
                <h3 class="mt-4 text-lg font-semibold text-slate-900">Sales & Pengadaan Pompa Baru</h3>
                <p class="mt-2 text-sm leading-relaxed text-slate-600">Survey kebutuhan, pengadaan unit sesuai spesifikasi, instalasi, hingga dukungan after-sales dan garansi.</p>
                <a href="{{ route('website.services') }}" class="mt-4 inline-flex items-center gap-1 text-sm font-semibold text-brand-blue-600 hover:text-brand-blue-500">Selengkapnya <x-icon name="arrow-right" class="h-4 w-4" /></a>
            </div>
        </div>
    </section>

    @if ($products->isNotEmpty())
        {{-- ===== Produk unggulan ===== --}}
        <section class="bg-slate-50 py-16">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between">
                    <h2 class="text-2xl font-bold text-slate-900">Produk Pompa</h2>
                    <a href="{{ route('website.products') }}" class="hidden text-sm font-semibold text-brand-blue-600 hover:text-brand-blue-500 sm:inline-flex">Lihat Semua Produk →</a>
                </div>
                <div class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($products as $product)
                        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                            <div class="flex h-36 items-center justify-center bg-slate-100">
                                @if ($product->image_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                @else
                                    <x-icon name="cube" class="h-10 w-10 text-slate-300" />
                                @endif
                            </div>
                            <div class="p-4">
                                <div class="text-xs font-medium text-brand-blue-600">{{ $product->category }}</div>
                                <div class="mt-1 text-sm font-semibold text-slate-800">{{ $product->name }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('website.products') }}" class="mt-6 inline-flex text-sm font-semibold text-brand-blue-600 hover:text-brand-blue-500 sm:hidden">Lihat Semua Produk →</a>
            </div>
        </section>
    @endif

    @if ($portfolioItems->isNotEmpty())
        {{-- ===== Portofolio ===== --}}
        <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between">
                <h2 class="text-2xl font-bold text-slate-900">Proyek Terpilih</h2>
                <a href="{{ route('website.portfolio') }}" class="hidden text-sm font-semibold text-brand-blue-600 hover:text-brand-blue-500 sm:inline-flex">Lihat Semua Portofolio →</a>
            </div>
            <div class="mt-8 grid gap-5 sm:grid-cols-3">
                @foreach ($portfolioItems as $item)
                    <div class="overflow-hidden rounded-xl border border-slate-200">
                        <div class="flex h-40 items-center justify-center bg-slate-100">
                            @if ($item->image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($item->image_path) }}" alt="{{ $item->title }}" class="h-full w-full object-cover">
                            @else
                                <x-icon name="doc-text" class="h-10 w-10 text-slate-300" />
                            @endif
                        </div>
                        <div class="p-4">
                            <div class="text-xs font-medium text-brand-blue-600">{{ $item->category }} @if ($item->year) · {{ $item->year }} @endif</div>
                            <div class="mt-1 text-sm font-semibold text-slate-800">{{ $item->title }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
            <a href="{{ route('website.portfolio') }}" class="mt-6 inline-flex text-sm font-semibold text-brand-blue-600 hover:text-brand-blue-500 sm:hidden">Lihat Semua Portofolio →</a>
        </section>
    @endif

    {{-- ===== CTA ===== --}}
    <section class="bg-brand-blue-600">
        <div class="mx-auto max-w-6xl px-4 py-14 text-center sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold text-white sm:text-3xl">Butuh Konsultasi Kebutuhan Pompa Anda?</h2>
            <p class="mx-auto mt-2 max-w-xl text-sm text-brand-blue-100">Tim kami siap membantu, dari assessment kondisi pompa hingga rekomendasi pengadaan unit baru.</p>
            <a href="{{ route('website.contact') }}" class="mt-6 inline-block rounded-lg bg-white px-6 py-3 text-sm font-semibold text-brand-blue-600 transition-colors hover:bg-brand-blue-50">
                Hubungi Kami Sekarang
            </a>
        </div>
    </section>

@endsection
