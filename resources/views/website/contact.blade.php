@extends('layouts.website')

@section('title', 'Kontak -- '.$settings->company_name)
@section('meta_description', 'Hubungi '.$settings->company_name.' untuk konsultasi service, maintenance, dan pengadaan pompa industri')

@section('content')

    <section class="bg-brand-blue-950">
        <div class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <span class="mx-auto mb-3 block h-1 w-14 rounded-full bg-brand-red-500"></span>
            <h1 class="text-3xl font-bold text-white sm:text-4xl">Hubungi Kami</h1>
            <p class="mx-auto mt-3 max-w-2xl text-sm text-slate-300">Konsultasikan kebutuhan service, maintenance, atau pengadaan pompa industri Anda bersama tim kami.</p>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-10 lg:grid-cols-5">
            <div class="lg:col-span-2">
                <h2 class="text-lg font-semibold text-slate-900">Informasi Kontak</h2>
                <div class="mt-5 space-y-4 text-sm text-slate-600">
                    @if ($settings->address)
                        <div class="flex items-start gap-3"><x-icon name="map-pin" class="mt-0.5 h-5 w-5 shrink-0 text-brand-blue-500" /> {{ $settings->address }}</div>
                    @endif
                    @if ($settings->phone)
                        <div class="flex items-center gap-3"><x-icon name="mail" class="h-5 w-5 shrink-0 text-brand-blue-500" /> {{ $settings->phone }}</div>
                    @endif
                    @if ($settings->email)
                        <a href="mailto:{{ $settings->email }}" class="flex items-center gap-3 hover:text-brand-blue-600"><x-icon name="mail" class="h-5 w-5 shrink-0 text-brand-blue-500" /> {{ $settings->email }}</a>
                    @endif
                    @if ($settings->whatsapp)
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $settings->whatsapp) }}" target="_blank" class="flex items-center gap-3 hover:text-brand-blue-600"><x-icon name="users" class="h-5 w-5 shrink-0 text-brand-blue-500" /> WhatsApp: {{ $settings->whatsapp }}</a>
                    @endif
                </div>
            </div>

            <div class="lg:col-span-3">
                @if (session('contact_success'))
                    <div class="mb-5 flex items-center gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        <x-icon name="check" class="h-4 w-4" /> Pesan Anda berhasil terkirim. Tim kami akan segera menghubungi Anda.
                    </div>
                @endif

                <form method="POST" action="{{ route('website.contact.submit') }}" class="space-y-4">
                    @csrf
                    {{-- Honeypot anti-spam: field tersembunyi, harus tetap kosong --}}
                    <input type="text" name="website" value="" class="hidden" tabindex="-1" autocomplete="off">

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Nama</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Perusahaan (opsional)</label>
                            <input type="text" name="company" value="{{ old('company') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                            <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">No. Telepon (opsional)</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Subjek</label>
                        <input type="text" name="subject" value="{{ old('subject') }}" placeholder="mis. Konsultasi Preventive Maintenance" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Pesan</label>
                        <textarea name="message" rows="5" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">{{ old('message') }}</textarea>
                        @error('message') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-brand-blue-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-brand-blue-500">
                        <x-icon name="mail" class="h-4 w-4" /> Kirim Pesan
                    </button>
                </form>
            </div>
        </div>
    </section>

@endsection
