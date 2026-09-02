<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-white antialiased">
    <div class="flex min-h-screen">

        {{-- ===== Panel kiri: brand & ilustrasi (desktop only) ===== --}}
        <div class="relative hidden w-[54%] flex-col overflow-hidden bg-gradient-to-br from-indigo-950 via-slate-900 to-slate-950 px-12 py-10 text-white lg:flex xl:px-16">

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

            {{-- brand --}}
            <div class="relative z-10 flex items-center gap-3">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-white/10 text-white ring-1 ring-white/20">
                    <x-icon name="building" class="h-6 w-6" />
                </div>
                <div class="leading-tight">
                    <div class="text-lg font-bold tracking-wide text-white">PT RTN</div>
                    <div class="text-xs text-indigo-200/70">Project Management System</div>
                </div>
            </div>

            {{-- fitur unggulan --}}
            <div class="relative z-10 mt-12 space-y-5">
                <h2 class="max-w-sm text-2xl font-semibold leading-snug text-white">
                    Satu sistem terpadu untuk seluruh operasional proyek Anda.
                </h2>

                <ul class="mt-6 space-y-4">
                    <li class="flex items-start gap-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/10 text-indigo-200">
                            <x-icon name="map-pin" class="h-4 w-4" />
                        </span>
                        <span class="pt-1 text-sm text-indigo-100/90">Kelola proyek multi-region secara terpusat, dari region hingga activity.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/10 text-indigo-200">
                            <x-icon name="shield" class="h-4 w-4" />
                        </span>
                        <span class="pt-1 text-sm text-indigo-100/90">Kontrol akses granular — role dan clearance per individu.</span>
                    </li>
                    <li class="flex items-start gap-3">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-white/10 text-indigo-200">
                            <x-icon name="chart-bar" class="h-4 w-4" />
                        </span>
                        <span class="pt-1 text-sm text-indigo-100/90">Pantau KPI, laporan, dan pengadaan secara real-time.</span>
                    </li>
                </ul>
            </div>

            {{-- ilustrasi vektor: fasilitas + dashboard --}}
            <div class="relative z-10 mt-10 flex flex-1 items-end">
                <div class="relative w-full">
                    <svg viewBox="0 0 420 260" class="h-auto w-full text-indigo-300/60" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                        {{-- ground --}}
                        <line x1="10" y1="248" x2="410" y2="248" stroke-opacity="0.4" />

                        {{-- building A --}}
                        <rect x="34" y="168" width="52" height="80" rx="3" />
                        <line x1="34" y1="192" x2="86" y2="192" stroke-opacity="0.5" />
                        <line x1="34" y1="216" x2="86" y2="216" stroke-opacity="0.5" />

                        {{-- tank 1 --}}
                        <ellipse cx="132" cy="188" rx="28" ry="8" />
                        <rect x="104" y="188" width="56" height="60" rx="4" />
                        <line x1="104" y1="216" x2="160" y2="216" stroke-opacity="0.5" />

                        {{-- tank 2 --}}
                        <ellipse cx="196" cy="204" rx="20" ry="6" />
                        <rect x="176" y="204" width="40" height="44" rx="3" />

                        {{-- crane --}}
                        <line x1="230" y1="248" x2="230" y2="120" />
                        <line x1="230" y1="120" x2="284" y2="120" />
                        <line x1="272" y1="120" x2="272" y2="136" />

                        {{-- building B --}}
                        <rect x="248" y="150" width="46" height="98" rx="3" />
                        <line x1="248" y1="172" x2="294" y2="172" stroke-opacity="0.5" />
                        <line x1="248" y1="196" x2="294" y2="196" stroke-opacity="0.5" />
                        <line x1="248" y1="220" x2="294" y2="220" stroke-opacity="0.5" />
                        <line x1="271" y1="150" x2="271" y2="132" />
                        <circle cx="271" cy="128" r="3" />

                        {{-- connector titik-titik menuju kartu dashboard --}}
                        <path d="M196 204 L 250 130 L 310 62" stroke-dasharray="3 6" stroke-opacity="0.6" />
                    </svg>

                    {{-- kartu dashboard mengambang --}}
                    <div class="absolute -top-6 right-2 w-56 rounded-2xl bg-white p-4 text-slate-700 shadow-2xl ring-1 ring-black/5 sm:w-64">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Ringkasan Proyek</span>
                            <span class="flex h-6 w-6 items-center justify-center rounded-md bg-indigo-50 text-indigo-600">
                                <x-icon name="chart-bar" class="h-3.5 w-3.5" />
                            </span>
                        </div>
                        <div class="mt-3 flex items-end gap-1.5">
                            <div class="h-10 w-4 rounded-sm" style="background-color:#2a78d6"></div>
                            <div class="h-14 w-4 rounded-sm" style="background-color:#eb6834"></div>
                            <div class="h-8 w-4 rounded-sm" style="background-color:#1baf7a"></div>
                            <div class="h-16 w-4 rounded-sm" style="background-color:#1baf7a"></div>
                            <div class="h-6 w-4 rounded-sm" style="background-color:#eda100"></div>
                        </div>
                        <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-500">
                            <span class="h-1.5 w-1.5 rounded-full" style="background-color:#1baf7a"></span>
                            18 proyek selesai bulan ini
                        </div>
                    </div>

                    {{-- badge laporan --}}
                    <div class="absolute left-4 top-2 hidden items-center gap-2 rounded-xl bg-white/95 px-3 py-2 text-xs font-medium text-slate-700 shadow-lg sm:flex">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-emerald-100 text-emerald-600">
                            <x-icon name="check" class="h-3 w-3" />
                        </span>
                        Laporan Terverifikasi
                    </div>
                </div>
            </div>

            <div class="relative z-10 mt-8 border-t border-white/10 pt-6 text-xs leading-relaxed text-indigo-200/60">
                &ldquo;Visibilitas real-time atas proyek, laporan lapangan, dan pengadaan material — dalam satu platform.&rdquo;
            </div>
        </div>

        {{-- ===== Panel kanan: form ===== --}}
        <div class="flex w-full flex-1 flex-col justify-center bg-white px-6 py-12 sm:px-10 lg:w-[46%] lg:px-16">
            <div class="mb-10 flex items-center gap-3 lg:hidden">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white">
                    <x-icon name="building" class="h-6 w-6" />
                </div>
                <div class="leading-tight">
                    <div class="text-base font-bold text-slate-800">PT RTN</div>
                    <div class="text-xs text-slate-500">Project Management System</div>
                </div>
            </div>

            <div class="mx-auto w-full max-w-sm">
                @yield('content')
            </div>
        </div>
    </div>

    @livewireScripts
</body>
</html>
