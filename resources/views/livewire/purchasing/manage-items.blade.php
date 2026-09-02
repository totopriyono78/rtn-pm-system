<div class="space-y-6">
    <div class="flex items-center justify-between">
        <x-page-header icon="cube" color="sky" title="Master Item" subtitle="Database sparepart, material, dan jasa untuk penawaran & BOQ." />
        <button wire:click="openCreate" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-500">
            <x-icon name="plus" class="h-4 w-4" /> Tambah Item
        </button>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="relative mb-4 max-w-sm">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <x-icon name="search" class="h-4 w-4" />
            </span>
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Cari kode / nama item..."
                class="w-full rounded-lg border border-slate-300 py-2 pl-9 pr-3 text-sm">
        </div>

        <div class="overflow-x-auto">
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
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2 font-medium">{{ $item->code }}</td>
                            <td class="py-2">{{ $item->name }}</td>
                            <td class="py-2 text-slate-500">{{ \App\Models\Item::CATEGORIES[$item->category] }}</td>
                            <td class="py-2 text-slate-500">{{ $item->unit_of_measure }}</td>
                            <td class="py-2 text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="py-2">
                                @if ($item->is_active)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-200 px-2 py-0.5 text-xs text-slate-600">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-2 text-right">
                                <button wire:click="openEdit({{ $item->id }})" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 transition-colors hover:bg-indigo-50">
                                    <x-icon name="edit" class="h-3.5 w-3.5" /> Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-empty-state icon="cube" title="Belum ada item." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $items->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="{{ $editingId ? 'edit' : 'plus-circle' }}" class="h-5 w-5 text-indigo-500" />
                    {{ $editingId ? 'Edit Item' : 'Tambah Item' }}
                </h3>
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
