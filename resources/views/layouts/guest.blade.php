<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-screen overflow-hidden bg-white antialiased">
    <div class="flex h-screen">

        {{-- ===== Panel kiri: header + foto (desktop only) ===== --}}
        <div class="relative hidden h-full min-h-0 w-[54%] flex-col overflow-hidden bg-gradient-to-br from-indigo-950 via-slate-900 to-slate-950 text-white lg:flex">

            {{-- grid blueprint halus --}}
            <svg class="pointer-events-none absolute inset-0 h-full w-full opacity-[0.07]" aria-hidden="true">
                <defs>
                    <pattern id="rtn-grid" width="42" height="42" patternUnits="userSpaceOnUse">
                        <path d="M42 0H0V42" fill="none" stroke="white" stroke-width="1" />
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#rtn-grid)" />
            </svg>

            {{-- glow accents --}}
            <div class="pointer-events-none absolute -left-20 -top-24 h-72 w-72 rounded-full bg-indigo-500/30 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-24 -right-10 h-80 w-80 rounded-full bg-indigo-400/10 blur-3xl"></div>

            {{-- header: logo, judul, tagline & sub judul --}}
            <div class="relative z-10 flex shrink-0 items-start gap-3 px-12 py-7 xl:px-16">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10 text-white ring-1 ring-white/20">
                    <x-icon name="building" class="h-6 w-6" />
                </div>
                <div class="leading-tight">
                    <div class="text-lg font-bold tracking-wide text-white">Project Management System</div>
                    <p class="mt-1 text-sm text-indigo-100/80">Kelola proyek, tim, dan purchasing dalam satu platform terintegrasi.</p>
                    <div class="mt-1.5 text-xs text-indigo-200/70">By PT. Gamatechno Indonesia</div>
                </div>
            </div>

            {{-- foto ditampilkan penuh di bawah header --}}
            <div class="relative z-10 min-h-0 flex-1 overflow-hidden">
                <img
                    src="{{ asset('images/login-hero.jpg') }}"
                    alt="Tim membahas laporan dan data proyek"
                    class="h-full w-full object-cover"
                >
                {{-- tint brand tipis + gradasi halus agar foto menyatu dengan panel gelap --}}
                <div class="pointer-events-none absolute inset-0 bg-indigo-950/20 mix-blend-multiply"></div>
                <div class="pointer-events-none absolute inset-x-0 top-0 h-20 bg-gradient-to-b from-slate-950/70 to-transparent"></div>
                <div class="pointer-events-none absolute inset-x-0 bottom-0 h-16 bg-gradient-to-t from-slate-950/50 to-transparent"></div>
            </div>
        </div>

        {{-- ===== Panel kanan: form ===== --}}
        {{-- overflow-y-auto di sini (bukan justify-center langsung) supaya kalau kontennya
             lebih tinggi dari layar, yang ke-scroll wajar dari atas -- bukan malah
             bagian atasnya yang kepotong karena ikut ke-tengah-kan. --}}
        <div class="h-full min-h-0 w-full flex-1 overflow-y-auto bg-white lg:w-[46%]">
            <div class="flex min-h-full flex-col justify-center px-6 py-6 sm:px-10 lg:px-16">
                <div class="mb-6 flex items-center gap-3 lg:hidden">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white">
                        <x-icon name="building" class="h-6 w-6" />
                    </div>
                    <div class="leading-tight">
                        <div class="text-base font-bold text-slate-800">Project Management System</div>
                        <p class="mt-0.5 text-xs text-slate-500">Kelola proyek, tim, dan purchasing dalam satu platform terintegrasi.</p>
                        <div class="mt-1 text-xs text-slate-400">By PT. Gamatechno Indonesia</div>
                    </div>
                </div>

                <div class="mx-auto w-full max-w-sm">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>
