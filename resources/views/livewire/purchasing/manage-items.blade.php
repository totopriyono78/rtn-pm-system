<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-slate-800">Master Item</h2>
            <p class="text-sm text-slate-500">Database sparepart, material, dan jasa untuk penawaran & BOQ.</p>
        </div>
        <button wire:click="openCreate" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">+ Tambah Item</button>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <input wire:model.live.debounce.400ms="search" type="text" placeholder="Cari kode / nama item..."
            class="mb-4 w-full max-w-sm rounded-lg border border-slate-300 px-3 py-2 text-sm">

        <table class="w-full text-left text-sm">
            <thead class="text-xs uppercase text-slate-400">
                <tr>
                    <th class="pb-2">Kode</th>
                    <th class="pb-2">Nama</th>
                    <th class="pb-2">Kategori</th>
                    <th class="pb-2">Satuan</th>
                    <th class="pb-2 text-right">Harga Satuan</th>
                    <th class="pb-2">Status</th>
                    <th class="pb-2 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr class="border-t border-slate-100">
                        <td class="py-2 font-medium">{{ $item->code }}</td>
                        <td class="py-2">{{ $item->name }}</td>
                        <td class="py-2 text-slate-500">{{ \App\Models\Item::CATEGORIES[$item->category] }}</td>
                        <td class="py-2 text-slate-500">{{ $item->unit_of_measure }}</td>
                        <td class="py-2 text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                        <td class="py-2">
                            @if ($item->is_active)
                                <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-700">Aktif</span>
                            @else
                                <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs text-slate-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="py-2 text-right">
                            <button wire:click="openEdit({{ $item->id }})" class="text-indigo-600 hover:underline">Edit</button>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="py-4 text-center text-slate-400">Belum ada item.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $items->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-slate-800">{{ $editingId ? 'Edit Item' : 'Tambah Item' }}</h3>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Kode Item</label>
                        <input type="text" wire:model="code" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="mis. MAT-045">
                        @error('code') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama Item</label>
                        <input type="text" wire:model="name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Kategori</label>
                        <select wire:model="category" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @foreach (\App\Models\Item::CATEGORIES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Satuan</label>
                            <input type="text" wire:model="unitOfMeasure" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="pcs/meter/set/ls">
                            @error('unitOfMeasure') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Harga Satuan</label>
                            <input type="number" step="1" min="0" wire:model="unitPrice" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('unitPrice') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model="isActive"> Item aktif
                    </label>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">Batal</button>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
