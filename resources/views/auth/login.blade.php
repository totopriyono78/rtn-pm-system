@extends('layouts.guest')

@section('title', 'Login - ' . config('app.name'))

@section('content')
    <div x-data="{ showPassword: false }">

        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-800">Selamat Datang Kembali</h1>
            <p class="mt-2 text-sm text-slate-500">Masuk untuk mengakses sistem manajemen proyek PT RTN.</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 flex items-start gap-2.5 rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm text-red-700">
                <x-icon name="alert-circle" class="mt-0.5 h-4 w-4 shrink-0" />
                <div>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <div>
                <label for="email" class="mb-1.5 block text-sm font-medium text-slate-700">Email</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <x-icon name="mail" class="h-[18px] w-[18px]" />
                    </span>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                        placeholder="nama@rtn.co.id"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/60 py-2.5 pl-11 pr-3.5 text-sm text-slate-800 transition-colors placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                </div>
            </div>

            <div>
                <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Password</label>
                <div class="relative">
                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                        <x-icon name="lock" class="h-[18px] w-[18px]" />
                    </span>
                    <input id="password" :type="showPassword ? 'text' : 'password'" name="password" required
                        placeholder="••••••••"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50/60 py-2.5 pl-11 pr-11 text-sm text-slate-800 transition-colors placeholder:text-slate-400 focus:border-indigo-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/20">
                    <button type="button" @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-400 hover:text-slate-600"
                        :aria-label="showPassword ? 'Sembunyikan password' : 'Tampilkan password'">
                        <x-icon x-show="!showPassword" name="eye" class="h-[18px] w-[18px]" />
                        <x-icon x-show="showPassword" name="eye-off" class="h-[18px] w-[18px]" x-cloak />
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/30">
                    Ingat saya
                </label>
                <span class="text-xs text-slate-400">Lupa password? Hubungi Admin</span>
            </div>

            <button type="submit"
                class="flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-indigo-600/20 transition-colors hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:ring-offset-2">
                Masuk
                <x-icon name="chevron-left" class="h-4 w-4 rotate-180" />
            </button>
        </form>

        <div class="mt-8 flex items-start gap-2.5 rounded-xl border border-slate-100 bg-slate-50 px-4 py-3 text-xs leading-relaxed text-slate-500">
            <x-icon name="shield" class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
            <span>Akun hanya dibuat oleh Administrator. Hubungi Admin jika Anda belum memiliki akses.</span>
        </div>

        @if (config('demo.show_accounts'))
            <div class="mt-4 rounded-xl border border-amber-100 bg-amber-50/60 p-4">
                <div class="mb-3 flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-amber-700">
                    <x-icon name="users" class="h-3.5 w-3.5" />
                    Akun Demo
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead>
                            <tr class="text-amber-700/60">
                                <th class="pb-1.5 pr-2 font-medium">Role</th>
                                <th class="pb-1.5 pr-2 font-medium">Email</th>
                                <th class="pb-1.5 font-medium">Password</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach (config('demo.accounts') as $account)
                                <tr class="cursor-pointer border-t border-amber-100 transition-colors hover:bg-amber-100/50"
                                    title="Klik untuk isi form otomatis"
                                    onclick="document.getElementById('email').value='{{ $account['email'] }}'; document.getElementById('password').value='{{ config('demo.password') }}';">
                                    <td class="py-1.5 pr-2 text-slate-600">{{ $account['role'] }}</td>
                                    <td class="py-1.5 pr-2 font-medium text-slate-700">{{ $account['email'] }}</td>
                                    <td class="py-1.5 font-mono text-slate-500">{{ config('demo.password') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <p class="mt-2.5 text-[11px] leading-relaxed text-amber-700/70">
                    Klik salah satu baris untuk mengisi form secara otomatis.
                </p>
            </div>
        @endif
    </div>
@endsection
