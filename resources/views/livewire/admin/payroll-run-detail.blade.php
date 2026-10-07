<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <a href="{{ route('admin.payroll.index') }}" class="text-sm font-medium text-indigo-600 hover:underline">&larr; Kembali ke Payroll</a>
            <h1 class="mt-1 text-xl font-bold text-slate-800">Payroll {{ $payrollRun->period_label }}</h1>
            <p class="mt-1 text-sm text-slate-500">
                Status:
                @if ($payrollRun->status === 'draft')
                    <span class="font-medium text-amber-600">Draft</span>
                @elseif ($payrollRun->status === 'finalized')
                    <span class="font-medium text-emerald-600">Final</span>
                @else
                    <span class="font-medium text-slate-500">Dibatalkan</span>
                @endif
            </p>
        </div>
        @if ($payrollRun->status === 'draft')
            <div class="flex gap-3">
                <button wire:click="regenerate" onclick="return confirm('Generate ulang slip gaji dari data terbaru (komponen gaji, lembur, kasbon)? Potongan BPJS/PPh21 yang sudah diisi tetap dipertahankan.')" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                    Generate Ulang
                </button>
                <button wire:click="cancelRun" onclick="return confirm('Batalkan payroll run ini?')" class="rounded-lg border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-600 shadow-sm hover:bg-red-50">
                    Batalkan
                </button>
                <button wire:click="finalize" onclick="return confirm('Finalisasi payroll run ini? Kasbon yang sudah dipotong di slip gaji akan otomatis ditutup dan tidak dapat diubah lagi setelah ini.')" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Finalisasi
                </button>
            </div>
        @endif
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Karyawan</th>
                    <th class="px-4 py-3 text-right">Gaji Pokok</th>
                    <th class="px-4 py-3 text-right">Tunjangan</th>
                    <th class="px-4 py-3 text-right">Lembur</th>
                    <th class="px-4 py-3 text-right">Kasbon</th>
                    <th class="px-4 py-3 text-right">BPJS</th>
                    <th class="px-4 py-3 text-right">PPh21</th>
                    <th class="px-4 py-3 text-right">Gaji Bersih</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($payslips as $payslip)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $payslip->user->name }}</td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format((float) $payslip->base_salary, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format((float) $payslip->fixed_allowance, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">
                            Rp {{ number_format((float) $payslip->overtime_amount, 0, ',', '.') }}
                            <span class="block text-xs text-slate-400">{{ number_format((float) $payslip->overtime_hours, 2, ',', '.') }} jam</span>
                        </td>
                        <td class="px-4 py-3 text-right text-red-600">Rp {{ number_format((float) $payslip->cash_advance_deduction, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-red-600">Rp {{ number_format((float) $payslip->bpjs_deduction, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-red-600">Rp {{ number_format((float) $payslip->pph21_deduction, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-slate-800">Rp {{ number_format((float) $payslip->net_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            @if ($payrollRun->status === 'draft')
                                <button wire:click="editPayslip({{ $payslip->id }})" class="text-sm font-medium text-indigo-600 hover:underline">Edit</button>
                            @endif
                            <a href="{{ route('payslips.print', $payslip) }}" target="_blank" class="ml-2 text-sm font-medium text-slate-500 hover:underline">Cetak</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-6 text-center text-slate-400">Belum ada slip gaji -- pastikan ada karyawan dengan komponen gaji aktif.</td>
                    </tr>
                @endforelse
            </tbody>
            @if ($payslips->isNotEmpty())
                <tfoot>
                    <tr class="border-t-2 border-slate-800 bg-slate-50 font-semibold">
                        <td class="px-4 py-3" colspan="7">Total Gaji Bersih</td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format((float) $payslips->sum('net_amount'), 0, ',', '.') }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>

    {{-- ===== Modal edit BPJS/PPh21 ===== --}}
    @if ($editingPayslipId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-slate-800">Edit Potongan Manual</h2>
                <p class="mt-1 text-sm text-slate-500">BPJS dan PPh 21 diisi manual sesuai aturan perusahaan yang berlaku -- sistem tidak menghitung otomatis.</p>

                <div class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Potongan BPJS (Rp)</label>
                        <input type="number" step="0.01" wire:model="bpjsDeduction" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('bpjsDeduction') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Potongan PPh 21 (Rp)</label>
                        <input type="number" step="0.01" wire:model="pph21Deduction" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('pph21Deduction') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Catatan (opsional)</label>
                        <textarea wire:model="notes" rows="2" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="cancelEditPayslip" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                    <button wire:click="savePayslip" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Simpan</button>
                </div>
            </div>
        </div>
    @endif
</div>
