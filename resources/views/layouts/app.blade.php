<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
    <div
        x-data="{
            collapsed: (localStorage.getItem('rtn_sidebar_collapsed') ?? 'false') === 'true',
            mobileOpen: false,
            toggleCollapse() {
                this.collapsed = !this.collapsed;
                localStorage.setItem('rtn_sidebar_collapsed', this.collapsed);
            },
        }"
        class="flex min-h-screen"
    >
        {{-- ===== Sidebar (desktop) ===== --}}
        <aside
            :class="collapsed ? 'md:w-[76px]' : 'md:w-64'"
            class="hidden shrink-0 flex-col border-r border-slate-800/60 bg-slate-900 text-slate-200 transition-[width] duration-200 ease-in-out md:flex"
        >
            <div
                class="flex h-16 shrink-0 items-center border-b border-slate-800/60 px-4"
                :class="collapsed ? 'justify-center' : 'justify-between gap-3'"
            >
                <div x-show="!collapsed" x-transition.opacity class="flex min-w-0 items-center gap-3">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-600 text-white">
                        <x-icon name="building" class="h-5 w-5" />
                    </div>
                    <div class="min-w-0 leading-tight">
                        <div class="truncate text-sm font-bold text-white">PT RTN</div>
                        <div class="truncate text-[11px] text-slate-400">Project Management System</div>
                    </div>
                </div>
                <button
                    @click="toggleCollapse()"
                    class="shrink-0 rounded-lg p-2 text-slate-400 transition-colors hover:bg-white/5 hover:text-white"
                    :title="collapsed ? 'Lebarkan menu' : 'Ciutkan menu'"
                >
                    <x-icon name="sidebar" class="h-5 w-5" />
                </button>
            </div>

            @include('partials.sidebar-nav')

            <div class="border-t border-slate-800/60 p-3">
                <div class="flex items-center gap-3 rounded-lg px-2 py-2" :class="{ 'justify-center': collapsed }">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-500/20 text-sm font-semibold text-indigo-300">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div x-show="!collapsed" x-transition.opacity class="min-w-0 flex-1 leading-tight">
                        <div class="truncate text-sm font-medium text-slate-100">{{ auth()->user()->name }}</div>
                        <div class="truncate text-xs text-slate-400">{{ auth()->user()->roleLabel() }}</div>
                    </div>
                </div>
            </div>
        </aside>

        {{-- ===== Sidebar (mobile drawer) ===== --}}
        <div x-show="mobileOpen" x-cloak class="fixed inset-0 z-40 md:hidden" style="display: none;">
            <div x-show="mobileOpen" x-transition.opacity @click="mobileOpen = false" class="absolute inset-0 bg-slate-900/60"></div>
            <aside
                x-show="mobileOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="-translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="-translate-x-full"
                class="relative flex h-full w-72 flex-col bg-slate-900 text-slate-200"
                @click.outside="mobileOpen = false"
            >
                <div class="flex h-16 shrink-0 items-center justify-between gap-3 border-b border-slate-800/60 px-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-600 text-white">
                            <x-icon name="building" class="h-5 w-5" />
                        </div>
                        <div class="leading-tight">
                            <div class="text-sm font-bold text-white">PT RTN</div>
                            <div class="text-[11px] text-slate-400">Project Management System</div>
                        </div>
                    </div>
                    <button @click="mobileOpen = false" class="rounded-lg p-1.5 text-slate-400 hover:bg-white/5 hover:text-white">
                        <x-icon name="close" class="h-5 w-5" />
                    </button>
                </div>

                @include('partials.sidebar-nav')

                <div class="border-t border-slate-800/60 p-3">
                    <div class="flex items-center gap-3 rounded-lg px-2 py-2">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-500/20 text-sm font-semibold text-indigo-300">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0 flex-1 leading-tight">
                            <div class="truncate text-sm font-medium text-slate-100">{{ auth()->user()->name }}</div>
                            <div class="truncate text-xs text-slate-400">{{ auth()->user()->roleLabel() }}</div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        {{-- ===== Main column ===== --}}
        <div class="flex min-h-screen flex-1 flex-col">
            <header class="sticky top-0 z-30 flex h-16 shrink-0 items-center justify-between gap-3 border-b border-slate-200 bg-white/80 px-4 backdrop-blur md:px-8">
                <div class="flex items-center gap-3">
                    <button @click="mobileOpen = true" class="rounded-lg p-2 text-slate-500 hover:bg-slate-100 md:hidden">
                        <x-icon name="menu" class="h-5 w-5" />
                    </button>
                    <h1 class="text-sm font-semibold text-slate-800 md:text-base">{{ config('app.name') }}</h1>
                </div>

                <div class="flex items-center gap-3">
                    <span class="hidden rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-600 sm:inline-block">
                        {{ auth()->user()->roleLabel() }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-sm font-medium text-slate-600 transition-colors hover:border-slate-300 hover:bg-slate-50">
                            <x-icon name="logout" class="h-4 w-4" />
                            <span class="hidden sm:inline">Keluar</span>
                        </button>
                    </form>
                </div>
            </header>

            <main class="flex-1 px-4 py-6 md:px-8">
                @if (session('success'))
                    <div class="mb-4 flex items-center gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4 flex items-center gap-2 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ session('error') }}
                    </div>
                @endif

                {{ $slot }}
            </main>

            @include('partials.footer')
        </div>
    </div>

    @livewireScripts
</body>
</html>
