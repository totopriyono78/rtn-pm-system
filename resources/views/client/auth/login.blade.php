<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Client Portal - {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 antialiased" x-data="{ showPassword: false }">
    <div class="flex min-h-screen flex-col items-center justify-center px-6 py-10">
        <div class="mb-6 flex items-center gap-3">
            <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white">
                <x-icon name="building" class="h-6 w-6" />
            </div>
            <div class="leading-tight">
                <div class="text-base font-bold text-slate-800">Client Portal</div>
                <p class="mt-0.5 text-xs text-slate-500">PT RTN — Project Management System</p>
            </div>
        </div>

        <div class="w-full max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h1 class="mb-5 text-lg font-semibold text-slate-800">Masuk ke Portal Klien</h1>

            @if ($errors->any())
                <div class="mb-4 flex items-start gap-2.5 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <x-icon name="alert-circle" class="mt-0.5 h-4 w-4 shrink-0" />
                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('client.login') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <x-icon name="mail" class="h-[18px] w-[18px]" />
                        </span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/60 py-2.5 pl-11 pr-3.5 text-sm text-slate-800 transition-colors focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <x-icon name="lock" class="h-[18px] w-[18px]" />
                        </span>
                        <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required
                            class="w-full rounded-xl border border-slate-200 bg-slate-50/60 py-2.5 pl-11 pr-11 text-sm text-slate-800 transition-colors focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                        <button type="button" @click="showPassword = !showPassword"
                            class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600">
                            <x-icon x-show="!showPassword" name="eye" class="h-[18px] w-[18px]" />
                            <x-icon x-show="showPassword" name="eye-off" class="h-[18px] w-[18px]" x-cloak />
                        </button>
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/30">
                    Ingat saya
                </label>

                <button type="submit"
                    class="flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-600/20 transition-colors hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:ring-offset-2">
                    Masuk
                </button>
            </form>

            <p class="mt-5 text-center text-xs text-slate-400">Lupa password? Hubungi PIC PT RTN Anda.</p>
        </div>
    </div>
    @livewireScripts
</body>
</html>
