<div class="space-y-6">
    <div class="flex items-center justify-between">
        <x-page-header icon="clipboard-list" color="indigo" title="Chart of Account" subtitle="Master akun akuntansi. Normal balance ditentukan otomatis dari tipe akun." />
        <button wire:click="openCreate" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-500">
            <x-icon name="plus" class="h-4 w-4" /> Tambah Akun
        </button>
    </div>

    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 px-4 py-2.5 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">Kode</th>
                        <th class="pb-2">Nama Akun</th>
                        <th class="pb-2">Tipe</th>
                        <th class="pb-2">Normal</th>
                        <th class="pb-2 text-right">Saldo (Posted)</th>
                        <th class="pb-2">Status</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($accounts as $account)
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2 font-medium">{{ $account->code }}</td>
                            <td class="py-2">{{ $account->name }}</td>
                            <td class="py-2 text-slate-500">{{ $account->typeLabel() }}</td>
                            <td class="py-2 text-slate-500">{{ $account->normal_balance === 'debit' ? 'Debit' : 'Kredit' }}</td>
                            <td class="py-2 text-right">Rp {{ number_format($account->postedBalance(), 0, ',', '.') }}</td>
                            <td class="py-2">
                                @if ($account->is_active)
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700">Aktif</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs text-slate-500">Nonaktif</span>
                                @endif
                            </td>
                            <td class="py-2 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <button wire:click="openEdit({{ $account->id }})" class="rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-50">Ubah</button>
                                    @if (! $account->isUsedInAnyJournal())
                                        <button wire:click="deleteAccount({{ $account->id }})" wire:confirm="Hapus akun ini?" class="rounded-lg px-2 py-1 text-xs font-medium text-rose-600 hover:bg-rose-50">Hapus</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-empty-state icon="clipboard-list" title="Belum ada akun. Jalankan ChartOfAccountSeeder untuk COA default." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-slate-800">{{ $editingId ? 'Ubah Akun' : 'Tambah Akun' }}</h3>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Kode</label>
                        <input type="text" wire:model="code" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('code') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama Akun</label>
                        <input type="text" wire:model="name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Tipe</label>
                        <select wire:model="type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @foreach (\App\Models\ChartOfAccount::TYPES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-[11px] text-slate-400">Normal balance otomatis: Aset/Beban = Debit, Kewajiban/Modal/Pendapatan = Kredit.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" wire:model="isActive" id="isActive" class="rounded border-slate-300">
                        <label for="isActive" class="text-sm text-slate-700">Aktif</label>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                            <x-icon name="check" class="h-4 w-4" /> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
