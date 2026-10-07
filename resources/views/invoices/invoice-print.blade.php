<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $invoice->invoice_number }} - Invoice</title>
    @vite(['resources/css/app.css'])
    <style>
        @page { size: A4; margin: 16mm 16mm; }
        @media print {
            .no-print { display: none !important; }
            body { background: #ffffff; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased">

    {{-- toolbar layar (disembunyikan saat print) --}}
    <div class="no-print sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-white px-6 py-3 shadow-sm">
        <a href="{{ route('invoices.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">&larr; Kembali ke Daftar Invoice</a>
        <button onclick="window.print()" class="flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-indigo-500">
            <x-icon name="printer" class="h-4 w-4" />
            Cetak / Simpan PDF
        </button>
    </div>

    <div class="mx-auto my-8 max-w-3xl bg-white p-10 text-sm shadow-lg print:my-0 print:max-w-none print:p-0 print:shadow-none">

        {{-- ===== Kop surat ===== --}}
        <div class="flex items-start justify-between border-b-2 border-slate-800 pb-4">
            <div class="flex items-center gap-3">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-indigo-600 text-white">
                    <x-icon name="building" class="h-8 w-8" />
                </div>
                <div>
                    <div class="text-lg font-bold uppercase tracking-wide text-slate-800">{{ config('company.name') }}</div>
                    <div class="text-xs leading-relaxed text-slate-500">{{ config('company.address') }}</div>
                    <div class="text-xs text-slate-500">Telp: {{ config('company.phone') }} &middot; Email: {{ config('company.email') }}</div>
                </div>
            </div>
            <div class="shrink-0 text-right text-xs text-slate-500">
                <div>No. {{ $invoice->invoice_number }}</div>
                <div>{{ $invoice->invoice_date->translatedFormat('d F Y') }}</div>
            </div>
        </div>

        {{-- ===== Judul ===== --}}
        <div class="mt-6 text-center">
            <h1 class="text-lg font-bold uppercase tracking-wide text-slate-800 underline underline-offset-4">Invoice</h1>
        </div>

        {{-- ===== Info customer & proyek ===== --}}
        <div class="mt-6 grid grid-cols-2 gap-x-6 gap-y-1.5">
            <div>
                <p class="mb-1 text-xs uppercase text-slate-400">Kepada Yth.</p>
                <p class="font-semibold text-slate-800">{{ $customer->name ?? '-' }}</p>
                @if ($customer?->address)
                    <p class="text-slate-500">{{ $customer->address }}</p>
                @endif
                @if ($customer?->phone || $customer?->email)
                    <p class="text-slate-500">{{ $customer->phone }} @if($customer->phone && $customer->email) &middot; @endif {{ $customer->email }}</p>
                @endif
            </div>
            <div>
                <div class="flex"><span class="w-28 shrink-0 text-slate-500">Proyek</span><span>: {{ $invoice->project->name }}</span></div>
                <div class="flex"><span class="w-28 shrink-0 text-slate-500">Lokasi</span><span>: {{ $invoice->project->unit->name }}, {{ $invoice->project->unit->region->name }}</span></div>
                <div class="flex"><span class="w-28 shrink-0 text-slate-500">Periode</span><span>: {{ $invoice->period_label ?: '-' }}</span></div>
                <div class="flex"><span class="w-28 shrink-0 text-slate-500">Jatuh Tempo</span><span>: {{ optional($invoice->due_date)->translatedFormat('d F Y') ?? '-' }}</span></div>
                <div class="flex"><span class="w-28 shrink-0 text-slate-500">Status</span><span>: {{ \App\Models\Invoice::STATUSES[$invoice->status] }}</span></div>
            </div>
        </div>

        <p class="mt-6 leading-relaxed text-slate-700">
            Dengan ini kami mengajukan penagihan (Invoice) atas pekerjaan yang telah dilaksanakan
            @if ($invoice->supportingDocument)
                sesuai dengan dokumen pendukung <strong>{{ $invoice->supportingDocument->categoryLabel() }}</strong> ({{ $invoice->supportingDocument->original_name }})
            @else
                sesuai dengan progres pekerjaan pada proyek tersebut di atas
            @endif
            , dengan rincian sebagai berikut:
        </p>

        {{-- ===== Tabel rincian ===== --}}
        <table class="mt-4 w-full border-collapse">
            <thead>
                <tr class="border-y border-slate-800 bg-slate-50">
                    <th class="px-2 py-2 text-left font-semibold">No</th>
                    <th class="px-2 py-2 text-left font-semibold">Uraian</th>
                    <th class="px-2 py-2 text-right font-semibold">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b border-slate-200">
                    <td class="px-2 py-2 align-top">1</td>
                    <td class="px-2 py-2 align-top">
                        {{ $invoice->project->name }}
                        @if ($invoice->period_label)
                            &ndash; Periode {{ $invoice->period_label }}
                        @endif
                    </td>
                    <td class="px-2 py-2 align-top text-right">Rp {{ number_format((float) $invoice->subtotal, 0, ',', '.') }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="border-t border-slate-200">
                    <td colspan="2" class="px-2 py-1.5 text-right text-slate-500">Subtotal</td>
                    <td class="px-2 py-1.5 text-right">Rp {{ number_format((float) $invoice->subtotal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="px-2 py-1.5 text-right text-slate-500">PPN ({{ rtrim(rtrim(number_format((float) $invoice->tax_percent, 2), '0'), '.') }}%)</td>
                    <td class="px-2 py-1.5 text-right">Rp {{ number_format((float) $invoice->tax_amount, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-t-2 border-slate-800 font-semibold">
                    <td colspan="2" class="px-2 py-2 text-right">Total Invoice</td>
                    <td class="px-2 py-2 text-right">Rp {{ number_format((float) $invoice->total_amount, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <p class="mt-2 text-xs italic text-slate-500">Terbilang: {{ $totalWords }}</p>

        @if ($invoice->notes)
            <div class="mt-6 text-slate-700">
                <p class="font-semibold">Catatan:</p>
                <p class="mt-1">{{ $invoice->notes }}</p>
            </div>
        @endif

        {{-- ===== Ketentuan ===== --}}
        <div class="mt-6 text-slate-700">
            <p class="font-semibold">Ketentuan:</p>
            <ol class="mt-1 list-decimal space-y-1 pl-5">
                <li>Pembayaran mohon dilakukan selambat-lambatnya pada tanggal jatuh tempo yang tercantum di atas.</li>
                <li>Mohon konfirmasi bukti transfer/pembayaran kepada kami setelah pembayaran dilakukan.</li>
                <li>Syarat pembayaran lainnya mengikuti kesepakatan kerja sama yang berlaku antara kedua belah pihak.</li>
            </ol>
        </div>

        {{-- ===== Tanda tangan ===== --}}
        <div class="mt-14 grid grid-cols-2 gap-8">
            <div>
                <p>Diterima oleh,<br>{{ $customer->name ?? '(.......................)' }}</p>
                <div class="mt-20 border-t border-slate-400 pt-1">
                    <p class="text-xs text-slate-500">(Nama &amp; Jabatan)</p>
                </div>
            </div>
            <div class="text-right">
                <p>Hormat kami,<br>{{ config('company.name') }}</p>
                <div class="mt-16 border-t border-slate-400 pt-1">
                    <p class="font-medium">{{ $signatoryName ?: '(.......................)' }}</p>
                    <p class="text-xs text-slate-500">{{ $signatoryTitle }}</p>
                </div>
            </div>
        </div>

        <p class="mt-10 text-center text-[10px] text-slate-400">Dokumen dihasilkan otomatis oleh {{ config('app.name') }} pada {{ now()->translatedFormat('d F Y H:i') }}.</p>
    </div>
</body>
</html>
