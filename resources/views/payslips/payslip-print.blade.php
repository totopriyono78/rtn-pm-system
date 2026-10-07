<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Slip Gaji {{ $payslip->user->name }} - {{ $payslip->payrollRun->period_label }}</title>
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
        <a href="{{ route('admin.payroll.show', $payslip->payroll_run_id) }}" class="text-sm font-medium text-indigo-600 hover:underline">&larr; Kembali ke Payroll</a>
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
                <div>Periode: {{ $payslip->payrollRun->period_label }}</div>
                <div>Dicetak: {{ now()->translatedFormat('d F Y') }}</div>
            </div>
        </div>

        {{-- ===== Judul ===== --}}
        <div class="mt-6 text-center">
            <h1 class="text-lg font-bold uppercase tracking-wide text-slate-800 underline underline-offset-4">Slip Gaji</h1>
        </div>

        {{-- ===== Info karyawan ===== --}}
        <div class="mt-6 grid grid-cols-2 gap-x-6 gap-y-1.5">
            <div>
                <p class="mb-1 text-xs uppercase text-slate-400">Nama Karyawan</p>
                <p class="font-semibold text-slate-800">{{ $payslip->user->name }}</p>
                <p class="text-slate-500">{{ $payslip->user->roleLabel() }}</p>
            </div>
            <div>
                <div class="flex"><span class="w-32 shrink-0 text-slate-500">Periode</span><span>: {{ $payslip->payrollRun->period_label }}</span></div>
                <div class="flex"><span class="w-32 shrink-0 text-slate-500">Status Payroll</span><span>: {{ \App\Models\PayrollRun::STATUSES[$payslip->payrollRun->status] }}</span></div>
                <div class="flex"><span class="w-32 shrink-0 text-slate-500">Jam Lembur</span><span>: {{ number_format((float) $payslip->overtime_hours, 2, ',', '.') }} jam</span></div>
            </div>
        </div>

        {{-- ===== Tabel rincian ===== --}}
        <table class="mt-6 w-full border-collapse">
            <thead>
                <tr class="border-y border-slate-800 bg-slate-50">
                    <th class="px-2 py-2 text-left font-semibold" colspan="2">Pendapatan</th>
                    <th class="px-2 py-2 text-left font-semibold" colspan="2">Potongan</th>
                </tr>
            </thead>
            <tbody>
                <tr class="border-b border-slate-200">
                    <td class="px-2 py-2 align-top">Gaji Pokok</td>
                    <td class="px-2 py-2 align-top text-right">Rp {{ number_format((float) $payslip->base_salary, 0, ',', '.') }}</td>
                    <td class="px-2 py-2 align-top">Kasbon Belum Lunas</td>
                    <td class="px-2 py-2 align-top text-right">Rp {{ number_format((float) $payslip->cash_advance_deduction, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b border-slate-200">
                    <td class="px-2 py-2 align-top">Tunjangan Tetap</td>
                    <td class="px-2 py-2 align-top text-right">Rp {{ number_format((float) $payslip->fixed_allowance, 0, ',', '.') }}</td>
                    <td class="px-2 py-2 align-top">BPJS</td>
                    <td class="px-2 py-2 align-top text-right">Rp {{ number_format((float) $payslip->bpjs_deduction, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b border-slate-200">
                    <td class="px-2 py-2 align-top">Lembur ({{ number_format((float) $payslip->overtime_hours, 2, ',', '.') }} jam &times; Rp {{ number_format((float) $payslip->overtime_rate, 0, ',', '.') }})</td>
                    <td class="px-2 py-2 align-top text-right">Rp {{ number_format((float) $payslip->overtime_amount, 0, ',', '.') }}</td>
                    <td class="px-2 py-2 align-top">PPh 21</td>
                    <td class="px-2 py-2 align-top text-right">Rp {{ number_format((float) $payslip->pph21_deduction, 0, ',', '.') }}</td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-slate-800 font-semibold">
                    <td class="px-2 py-2 text-right">Total Pendapatan</td>
                    <td class="px-2 py-2 text-right">Rp {{ number_format((float) $payslip->gross_amount, 0, ',', '.') }}</td>
                    <td class="px-2 py-2 text-right">Total Potongan</td>
                    <td class="px-2 py-2 text-right">Rp {{ number_format((float) $payslip->total_deduction, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        <div class="mt-4 flex items-center justify-between border-t-2 border-slate-800 pt-3">
            <p class="text-base font-bold uppercase text-slate-800">Gaji Bersih (Take Home Pay)</p>
            <p class="text-lg font-bold text-slate-800">Rp {{ number_format((float) $payslip->net_amount, 0, ',', '.') }}</p>
        </div>
        <p class="mt-1 text-xs italic text-slate-500">Terbilang: {{ $netWords }}</p>

        @if ($payslip->notes)
            <div class="mt-6 text-slate-700">
                <p class="font-semibold">Catatan:</p>
                <p class="mt-1">{{ $payslip->notes }}</p>
            </div>
        @endif

        <p class="mt-6 text-xs text-slate-500">
            Potongan BPJS dan PPh 21 diisi manual oleh Administrator berdasarkan aturan perusahaan yang berlaku
            dan bukan hasil perhitungan otomatis sistem.
        </p>

        {{-- ===== Tanda tangan ===== --}}
        <div class="mt-14 grid grid-cols-2 gap-8">
            <div>
                <p>Diterima oleh,<br>{{ $payslip->user->name }}</p>
                <div class="mt-20 border-t border-slate-400 pt-1">
                    <p class="text-xs text-slate-500">(Tanda Tangan &amp; Tanggal)</p>
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
