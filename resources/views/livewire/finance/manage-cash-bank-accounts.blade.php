<div class="space-y-6">
    <div class="flex items-center justify-between">
        <x-page-header icon="wallet" color="indigo" title="Akun Kas/Bank" subtitle="Master akun kas fisik & rekening bank, dipakai sebagai tujuan pelunasan Invoice dan sumber pencairan Kasbon." />
        <div class="flex gap-3">
            <a href="{{ route('admin.cash-bank.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                Lihat Buku Kas/Bank &rarr;
            </a>
            <button wire:click="openCreate" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-500">
                <x-icon name="plus" class="h-4 w-4" /> Tambah Akun
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 px-4 py-2.5 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">Nama Akun</th>
                        <th class="pb-2">Tipe</th>
                        <th class="pb-2">Bank / No. Rekening</th>
                        <th class="pb-2 text-right">Saldo Saat Ini</th>
                        <th class="pb-2 text-center">Status</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($accounts as $account)
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2 font-medium">{{ $account->name }}</td>
                            <td class="py-2 text-slate-500">{{ \App\Models\CashBankAccount::TYPES[$account->type] }}</td>
                            <td class="py-2 text-slate-500">
                                @if ($account->bank_name || $account->account_number)
                                    {{ $account->bank_name }} {{ $account->account_number ? '-- '.$account->account_number : '' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-2 text-right font-medium">Rp {{ number_format($account->current_balance, 0, ',', '.') }}</td>
                            <td class="py-2 text-center">
                                @if ($account->is_active)
                                    <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-700">Aktif</span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500">Nonaktif</span>
                                @endif
                            </td>
                            <td class="py-2 text-right">
                                <button wire:click="openEdit({{ $account->id }})" class="text-sm font-medium text-indigo-600 hover:underline">Edit</button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state icon="wallet" title="Belum ada akun Kas/Bank." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="plus-circle" class="h-5 w-5 text-indigo-500" /> {{ $editingId ? 'Ubah Akun' : 'Tambah Akun' }}
                </h3>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama Akun</label>
                        <input type="text" wire:model="name" placeholder="mis. Kas Kantor Pusat / BCA Operasional" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Tipe</label>
                        <select wire:model="type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @foreach (\App\Models\CashBankAccount::TYPES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if ($type === 'bank')
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">Nama Bank</label>
                                <input type="text" wire:model="bankName" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                @error('bankName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-medium text-slate-700">No. Rekening</label>
                                <input type="text" wire:model="accountNumber" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                @error('accountNumber') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    @endif
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Saldo Awal (Rp)</label>
                        <input type="number" step="0.01" wire:model="openingBalance" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('openingBalance') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        <p class="mt-1 text-[11px] text-slate-400">Saldo sebelum transaksi apa pun tercatat di sistem.</p>
                    </div>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model="isActive" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="text-sm text-slate-700">Aktif (muncul sebagai pilihan akun saat mencatat transaksi)</span>
                    </label>
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
