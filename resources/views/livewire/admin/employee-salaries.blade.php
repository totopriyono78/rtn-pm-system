<div>
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-slate-800">Komponen Gaji Karyawan</h1>
            <p class="mt-1 text-sm text-slate-500">Gaji pokok, tunjangan tetap, dan tarif lembur per jam untuk setiap karyawan. Hanya karyawan yang diaktifkan di sini yang akan disertakan saat Payroll bulanan di-generate.</p>
        </div>
        <a href="{{ route('admin.payroll.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
            Lihat Payroll &rarr;
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="mb-4">
        <input type="text" wire:model.live.debounce.400ms="search" placeholder="Cari nama karyawan..." class="w-full max-w-sm rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </div>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3 text-right">Gaji Pokok</th>
                    <th class="px-4 py-3 text-right">Tunjangan Tetap</th>
                    <th class="px-4 py-3 text-right">Tarif Lembur/Jam</th>
                    <th class="px-4 py-3 text-center">Status</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($users as $user)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $user->roleLabel() }}</td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format((float) ($user->employeeSalary->base_salary ?? 0), 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format((float) ($user->employeeSalary->fixed_allowance ?? 0), 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format((float) ($user->employeeSalary->overtime_hourly_rate ?? 0), 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            @if ($user->employeeSalary?->is_active)
                                <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Aktif</span>
                            @else
                                <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500">Nonaktif</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button wire:click="edit({{ $user->id }})" class="text-sm font-medium text-indigo-600 hover:underline">Edit</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-6 text-center text-slate-400">Tidak ada karyawan ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>

    {{-- ===== Modal edit ===== --}}
    @if ($editingUserId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 px-4">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-slate-800">Edit Komponen Gaji</h2>
                <p class="mt-1 text-sm text-slate-500">{{ \App\Models\User::find($editingUserId)?->name }}</p>

                <div class="mt-4 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Gaji Pokok (Rp)</label>
                        <input type="number" step="0.01" wire:model="baseSalary" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('baseSalary') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Tunjangan Tetap (Rp)</label>
                        <input type="number" step="0.01" wire:model="fixedAllowance" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('fixedAllowance') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700">Tarif Lembur per Jam (Rp)</label>
                        <input type="number" step="0.01" wire:model="overtimeHourlyRate" class="mt-1 w-full rounded-lg border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @error('overtimeHourlyRate') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        <p class="mt-1 text-xs text-slate-400">Dipakai untuk menghitung lembur otomatis dari jam kerja (WorkLog) di atas ambang jam kerja normal/hari pada Pengaturan KPI.</p>
                    </div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model="isActive" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-slate-700">Aktif (disertakan saat Payroll di-generate)</span>
                    </label>
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <button wire:click="cancelEdit" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Batal</button>
                    <button wire:click="save" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Simpan</button>
                </div>
            </div>
        </div>
    @endif
</div>
