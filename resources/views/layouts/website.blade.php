<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', $settings->company_name.' -- '.($settings->tagline ?: 'Solusi Service, Maintenance & Pengadaan Pompa Industri'))</title>
    <meta name="description" content="@yield('meta_description', $settings->tagline ?: 'Layanan service, maintenance, dan pengadaan pompa industri terpercaya.')">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-white text-slate-800 antialiased">

    {{-- ===== Header ===== --}}
    <header class="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="{{ route('website.home') }}" class="flex items-center gap-2.5">
                @if ($settings->logo_path)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($settings->logo_path) }}" alt="{{ $settings->company_name }}" class="h-9 w-auto">
                @else
                    <img src="{{ asset('images/logo-rtn-icon.png') }}" alt="{{ $settings->company_name }}" class="h-9 w-9 rounded-lg object-contain">
                @endif
                <span class="text-base font-bold text-slate-900">{{ $settings->company_name }}</span>
            </a>

            <nav class="hidden items-center gap-7 text-sm font-medium text-slate-600 md:flex">
                <a href="{{ route('website.home') }}" class="transition-colors hover:text-brand-blue-600 {{ request()->routeIs('website.home') ? 'text-brand-blue-600' : '' }}">Beranda</a>
                <a href="{{ route('website.about') }}" class="transition-colors hover:text-brand-blue-600 {{ request()->routeIs('website.about') ? 'text-brand-blue-600' : '' }}">Tentang Kami</a>
                <a href="{{ route('website.products') }}" class="transition-colors hover:text-brand-blue-600 {{ request()->routeIs('website.products') ? 'text-brand-blue-600' : '' }}">Produk</a>
                <a href="{{ route('website.services') }}" class="transition-colors hover:text-brand-blue-600 {{ request()->routeIs('website.services') ? 'text-brand-blue-600' : '' }}">Layanan</a>
                <a href="{{ route('website.portfolio') }}" class="transition-colors hover:text-brand-blue-600 {{ request()->routeIs('website.portfolio') ? 'text-brand-blue-600' : '' }}">Portofolio</a>
                <a href="{{ route('website.contact') }}" class="transition-colors hover:text-brand-blue-600 {{ request()->routeIs('website.contact') ? 'text-brand-blue-600' : '' }}">Kontak</a>
            </nav>

            <div class="hidden items-center gap-3 md:flex">
                <a href="{{ route('login') }}" class="flex items-center gap-1.5 text-xs font-medium text-slate-400 transition-colors hover:text-slate-600">
                    <x-icon name="lock" class="h-3.5 w-3.5" /> Portal Karyawan/Klien
                </a>
                <a href="{{ route('website.contact') }}" class="rounded-lg bg-brand-blue-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-brand-blue-500">
                    Hubungi Kami
                </a>
            </div>

            <button type="button" id="mobile-menu-toggle" aria-expanded="false" aria-controls="mobile-menu-panel" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 md:hidden">
                <x-icon name="menu" class="h-6 w-6" id="mobile-menu-icon-open" />
                <x-icon name="close" class="hidden h-6 w-6" id="mobile-menu-icon-close" />
            </button>
        </div>

        <div id="mobile-menu-panel" class="hidden border-t border-slate-200 bg-white px-4 py-4 md:hidden">
            <div class="flex flex-col gap-3 text-sm font-medium text-slate-600">
                <a href="{{ route('website.home') }}">Beranda</a>
                <a href="{{ route('website.about') }}">Tentang Kami</a>
                <a href="{{ route('website.products') }}">Produk</a>
                <a href="{{ route('website.services') }}">Layanan</a>
                <a href="{{ route('website.portfolio') }}">Portofolio</a>
                <a href="{{ route('website.contact') }}">Kontak</a>
                <a href="{{ route('login') }}" class="text-slate-400">Portal Karyawan/Klien</a>
                <a href="{{ route('website.contact') }}" class="mt-1 inline-block rounded-lg bg-brand-blue-600 px-4 py-2 text-center text-white">Hubungi Kami</a>
            </div>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    {{-- ===== Footer ===== --}}
    <footer class="border-t border-brand-blue-900 bg-brand-blue-950 text-slate-300">
        <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
            <div class="grid gap-10 md:grid-cols-4">
                <div class="md:col-span-2">
                    <div class="flex items-center gap-2.5">
                        @if ($settings->logo_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($settings->logo_path) }}" alt="{{ $settings->company_name }}" class="h-9 w-9 rounded-lg bg-white object-contain p-0.5">
                        @else
                            <img src="{{ asset('images/logo-rtn-icon.png') }}" alt="{{ $settings->company_name }}" class="h-9 w-9 rounded-lg object-contain">
                        @endif
                        <span class="text-base font-bold text-white">{{ $settings->company_name }}</span>
                    </div>
                    <p class="mt-3 max-w-sm text-sm leading-relaxed text-slate-400">{{ $settings->tagline ?: $settings->about }}</p>
                    @if ($settings->instagram_url || $settings->facebook_url || $settings->linkedin_url)
                        <div class="mt-4 flex gap-3">
                            @if ($settings->instagram_url)
                                <a href="{{ $settings->instagram_url }}" target="_blank" class="text-slate-400 hover:text-white text-sm">Instagram</a>
                            @endif
                            @if ($settings->facebook_url)
                                <a href="{{ $settings->facebook_url }}" target="_blank" class="text-slate-400 hover:text-white text-sm">Facebook</a>
                            @endif
                            @if ($settings->linkedin_url)
                                <a href="{{ $settings->linkedin_url }}" target="_blank" class="text-slate-400 hover:text-white text-sm">LinkedIn</a>
                            @endif
                        </div>
                    @endif
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Navigasi</div>
                    <div class="mt-3 flex flex-col gap-2 text-sm">
                        <a href="{{ route('website.about') }}" class="text-slate-400 hover:text-white">Tentang Kami</a>
                        <a href="{{ route('website.products') }}" class="text-slate-400 hover:text-white">Produk</a>
                        <a href="{{ route('website.services') }}" class="text-slate-400 hover:text-white">Layanan</a>
                        <a href="{{ route('website.portfolio') }}" class="text-slate-400 hover:text-white">Portofolio</a>
                    </div>
                </div>
                <div>
                    <div class="text-xs font-semibold uppercase tracking-wider text-slate-500">Kontak</div>
                    <div class="mt-3 flex flex-col gap-2 text-sm text-slate-400">
                        @if ($settings->address)<span class="flex items-start gap-2"><x-icon name="map-pin" class="mt-0.5 h-4 w-4 shrink-0" /> {{ $settings->address }}</span>@endif
                        @if ($settings->phone)<span class="flex items-center gap-2"><x-icon name="mail" class="h-4 w-4 shrink-0" /> {{ $settings->phone }}</span>@endif
                        @if ($settings->email)<a href="mailto:{{ $settings->email }}" class="flex items-center gap-2 hover:text-white"><x-icon name="mail" class="h-4 w-4 shrink-0" /> {{ $settings->email }}</a>@endif
                    </div>
                </div>
            </div>
            <div class="mt-10 border-t border-slate-800 pt-6 text-center text-xs text-slate-500">
                Copyright &copy; {{ $settings->company_name }} - {{ date('Y') }}
            </div>
        </div>
    </footer>
</body>
</html>
