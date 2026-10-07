<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Client Portal - ' . config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">
    <div class="min-h-screen">
        <header class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-5xl items-center justify-between px-4 py-3 sm:px-6">
                <a href="{{ route('client.dashboard') }}" class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-600 text-white">
                        <x-icon name="building" class="h-5 w-5" />
                    </div>
                    <div class="leading-tight">
                        <div class="text-sm font-bold text-slate-800">Client Portal</div>
                        <div class="text-[11px] text-slate-400">PT RTN</div>
                    </div>
                </a>

                <div class="flex items-center gap-3">
                    <nav class="hidden items-center gap-1 sm:flex">
                        <a href="{{ route('client.dashboard') }}" class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors {{ request()->routeIs('client.dashboard') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-50' }}">Proyek</a>
                        <a href="{{ route('client.invoices') }}" class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors {{ request()->routeIs('client.invoices') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600 hover:bg-slate-50' }}">Invoice</a>
                    </nav>

                    <livewire:client.client-notification-bell />
                    <div class="flex items-center gap-2 border-l border-slate-200 pl-3">
                        <div class="hidden text-right leading-tight sm:block">
                            <div class="text-xs font-semibold text-slate-700">{{ auth('client')->user()->name }}</div>
                            <div class="text-[11px] text-slate-400">{{ auth('client')->user()->customer->name ?? '-' }}</div>
                        </div>
                        <form method="POST" action="{{ route('client.logout') }}">
                            @csrf
                            <button type="submit" class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-400 transition-colors hover:bg-rose-50 hover:text-rose-500" title="Keluar">
                                <x-icon name="logout" class="h-4.5 w-4.5" />
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- nav mobile --}}
            <div class="flex items-center gap-1 border-t border-slate-100 px-4 py-2 sm:hidden">
                <a href="{{ route('client.dashboard') }}" class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors {{ request()->routeIs('client.dashboard') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600' }}">Proyek</a>
                <a href="{{ route('client.invoices') }}" class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors {{ request()->routeIs('client.invoices') ? 'bg-indigo-50 text-indigo-600' : 'text-slate-600' }}">Invoice</a>
            </div>
        </header>

        <main class="mx-auto max-w-5xl px-4 py-6 sm:px-6">
            @if (session('success'))
                <div class="mb-4 flex items-center gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    <x-icon name="check" class="h-4 w-4 shrink-0" /> {{ session('success') }}
                </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
