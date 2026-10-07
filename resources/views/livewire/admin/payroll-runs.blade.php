<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Payroll</h1>
            <p class="mt-1 text-sm text-slate-500">Slip gaji bulanan karyawan: gaji pokok + tunjangan tetap, lembur otomatis dari jam kerja, dan potongan kasbon belum lunas dihitung otomatis. BPJS/PPh21 diisi manual per karyawan.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.employee-salaries') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                Komponen Gaji
            </a>
            <button wire:click="openCreate" class="flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                <x-icon name="plus" class="h-4 w-4" />
                Buat Payroll Run
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Periode</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Dibuat Oleh</th>
                    <th class="px-4 py-3">Difinalisasi</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($runs as $run)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $run->period_label }}</td>
                        <td class="px-4 py-3">
                            @if ($run->status === 'draft')
                                <span class="rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-700">Draft</span>
                            @elseif ($run->status === 'finalized')
                                <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Final</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500">Dibatalkan</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $run->generator?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $run->finalized_at?->translatedFormat('d M Y H:i') ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.payroll.show', $run) }}" class="text-sm font-medium text-indigo-600 hover:underline">Lihat Detail &rarr;</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-slate-400">Belum ada payroll run.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $runs->links() }}</div>

    {{-- ===== Modal buat payroll run ===== --}}
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
            <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-slate-800">Buat Payroll Run</h2>
                <p class="mt-1 text-sm text-slate-500">Slip gaji akan di-generate otomatis untuk semua karyawan dengan komponen gaji aktif.</p>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Bulan</label>
                        <select wire:model="periodMonth" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach (\App\Livewire\Admin\PayrollRuns::MONTHS as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('periodMonth') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Tahun</label>
                        <input type="number" wire:model="periodYear" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('periodYear') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="$set('showCreateModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                    <button wire:click="create" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Buat & Generate</button>
                </div>
            </div>
        </div>
    @endif
</div>
