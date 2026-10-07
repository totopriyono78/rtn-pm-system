@extends('layouts.website')

@section('title', 'Produk -- '.$settings->company_name)
@section('meta_description', 'Lini produk pompa industri yang disediakan '.$settings->company_name)

@section('content')

    <section class="bg-brand-blue-950">
        <div class="mx-auto max-w-4xl px-4 py-16 text-center sm:px-6 lg:px-8">
            <span class="mx-auto mb-3 block h-1 w-14 rounded-full bg-brand-red-500"></span>
            <h1 class="text-3xl font-bold text-white sm:text-4xl">Produk Pompa</h1>
            <p class="mx-auto mt-3 max-w-2xl text-sm text-slate-300">Lini produk pompa industri yang kami sediakan, disesuaikan dengan kebutuhan teknis dan operasional Anda.</p>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
        @if ($products->isEmpty())
            <x-empty-state icon="cube" title="Belum ada produk yang ditampilkan." />
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)
                    <div class="overflow-hidden rounded-2xl border border-slate-200">
                        <div class="flex h-44 items-center justify-center bg-slate-100">
                            @if ($product->image_path)
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($product->image_path) }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                            @else
                                <x-icon name="cube" class="h-12 w-12 text-slate-300" />
                            @endif
                        </div>
                        <div class="p-5">
                            @if ($product->category)
                                <span class="inline-block rounded-full bg-brand-blue-50 px-2.5 py-0.5 text-xs font-medium text-brand-blue-600">{{ $product->category }}</span>
                            @endif
                            <h3 class="mt-2 text-base font-semibold text-slate-900">{{ $product->name }}</h3>
                            @if ($product->short_description)
                                <p class="mt-1.5 text-sm leading-relaxed text-slate-500">{{ $product->short_description }}</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <div class="mt-12 rounded-2xl bg-slate-50 p-8 text-center">
            <h2 class="text-lg font-semibold text-slate-900">Tidak menemukan spesifikasi yang Anda cari?</h2>
            <p class="mt-1 text-sm text-slate-500">Hubungi tim kami untuk konsultasi spesifikasi teknis sesuai kebutuhan proyek Anda.</p>
            <a href="{{ route('website.contact') }}" class="mt-4 inline-block rounded-lg bg-brand-blue-600 px-6 py-2.5 text-sm font-semibold text-white hover:bg-brand-blue-500">Konsultasi Sekarang</a>
        </div>
    </section>

@endsection
