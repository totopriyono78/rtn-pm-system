<div class="space-y-6">
    <x-page-header icon="chart-bar" color="indigo" title="Laporan Keuangan" subtitle="Ringkasan arus kas, piutang, hutang vendor, dan payroll per periode. Bukan laba-rugi/neraca formal -- murni agregasi dari data yang sudah tercatat." />

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Bulan</label>
                <select wire:model.live="periodMonth" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @foreach (\App\Livewire\Finance\FinancialReports::MONTHS as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Tahun</label>
                <input type="number" wire:model.live="periodYear" class="w-28 rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <p class="pb-2 text-xs text-slate-400">Periode: {{ $start->translatedFormat('d M Y') }} -- {{ $end->translatedFormat('d M Y') }}</p>
        </div>
    </div>

    {{-- ===== Arus Kas ===== --}}
    <div class="rounded-xl bg-white p-5 shadow-sm">
        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">Arus Kas &amp; Bank -- Periode Terpilih</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-lg bg-slate-50 p-4">
                <p class="text-xs text-slate-400">Saldo Awal Periode</p>
                <p class="mt-1 text-lg font-bold text-slate-800">Rp {{ number_format($openingBalance, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-lg bg-emerald-50 p-4">
                <p class="text-xs text-emerald-600">Total Masuk</p>
                <p class="mt-1 text-lg font-bold text-emerald-700">+ Rp {{ number_format($totalIn, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-lg bg-rose-50 p-4">
                <p class="text-xs text-rose-600">Total Keluar</p>
                <p class="mt-1 text-lg font-bold text-rose-700">- Rp {{ number_format($totalOut, 0, ',', '.') }}</p>
            </div>
            <div class="rounded-lg bg-indigo-50 p-4">
                <p class="text-xs text-indigo-600">Saldo Akhir Periode</p>
                <p class="mt-1 text-lg font-bold text-indigo-700">Rp {{ number_format($closingBalance, 0, ',', '.') }}</p>
            </div>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <p class="mb-2 text-xs font-semibold uppercase text-slate-400">Rincian Masuk per Kategori</p>
                @forelse ($inByCategory as $category => $amount)
                    <div class="flex items-center justify-between border-t border-slate-100 py-1.5 text-sm">
                        <span class="text-slate-600">{{ \App\Models\CashBankTransaction::CATEGORIES[$category] ?? $category }}</span>
                        <span class="font-medium text-emerald-600">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Tidak ada transaksi masuk periode ini.</p>
                @endforelse
            </div>
            <div>
                <p class="mb-2 text-xs font-semibold uppercase text-slate-400">Rincian Keluar per Kategori</p>
                @forelse ($outByCategory as $category => $amount)
                    <div class="flex items-center justify-between border-t border-slate-100 py-1.5 text-sm">
                        <span class="text-slate-600">{{ \App\Models\CashBankTransaction::CATEGORIES[$category] ?? $category }}</span>
                        <span class="font-medium text-rose-600">Rp {{ number_format($amount, 0, ',', '.') }}</span>
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Tidak ada transaksi keluar periode ini.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ===== AR & AP ===== --}}
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">AR -- Piutang (Invoice)</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-600">Invoice terbit periode ini</span>
                    <span class="font-semibold text-slate-800">Rp {{ number_format($totalInvoicedPeriod, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-600">Piutang belum lunas (saat ini, semua periode)</span>
                    <span class="font-semibold text-amber-600">Rp {{ number_format($totalReceivableNow, 0, ',', '.') }}</span>
                </div>
            </div>
            <a href="{{ route('invoices.index') }}" class="mt-4 inline-block text-xs text-indigo-600 hover:underline">Lihat daftar Invoice &rarr;</a>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">AP -- Hutang Vendor (Purchase Order)</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-600">PO terbit periode ini</span>
                    <span class="font-semibold text-slate-800">Rp {{ number_format($totalPoPeriod, 0, ',', '.') }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-slate-600">Hutang belum dibayar (saat ini, semua periode)</span>
                    <span class="font-semibold text-amber-600">Rp {{ number_format($totalPayableNow, 0, ',', '.') }}</span>
                </div>
            </div>
            @can('view-purchasing')
                <a href="{{ route('purchasing.po') }}" class="mt-4 inline-block text-xs text-indigo-600 hover:underline">Lihat daftar Purchase Order &rarr;</a>
            @endcan
        </div>
    </div>

    {{-- ===== Payroll ===== --}}
    <div class="rounded-xl bg-white p-5 shadow-sm">
        <h3 class="mb-4 text-sm font-semibold uppercase tracking-wide text-slate-500">Payroll -- Periode Terpilih</h3>
        @if ($payrollRun)
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-sm text-slate-600">Status: <span class="font-medium">{{ \App\Models\PayrollRun::STATUSES[$payrollRun->status] ?? $payrollRun->status }}</span></p>
                    <p class="mt-1 text-lg font-bold text-slate-800">Rp {{ number_format($payrollRun->total_net, 0, ',', '.') }}</p>
                    <p class="text-xs text-slate-400">Total gaji bersih (net) seluruh karyawan, periode ini.</p>
                </div>
                @can('manage-payroll')
                    <a href="{{ route('admin.payroll.show', $payrollRun) }}" class="text-xs text-indigo-600 hover:underline">Lihat detail Payroll Run &rarr;</a>
                @endcan
            </div>
            <p class="mt-3 text-[11px] text-slate-400">Catatan: nominal ini TIDAK ikut masuk ke arus kas di atas -- pembayaran gaji fisik tetap berpindah tangan manual di luar sistem (lihat catatan modul Payroll).</p>
        @else
            <p class="text-sm text-slate-400">Belum ada Payroll Run untuk periode ini.</p>
        @endif
    </div>
</div>
