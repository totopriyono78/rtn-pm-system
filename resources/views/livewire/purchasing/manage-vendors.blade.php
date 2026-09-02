<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-slate-800">Vendor</h2>
            <p class="text-sm text-slate-500">Master data vendor/supplier untuk kebutuhan Request for Quotation & Purchase Order.</p>
        </div>
        @if ($canManage)
            <button wire:click="openCreate" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">+ Tambah Vendor</button>
        @endif
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <input wire:model.live.debounce.400ms="search" type="text" placeholder="Cari kode / nama vendor..."
            class="mb-4 w-full max-w-sm rounded-lg border border-slate-300 px-3 py-2 text-sm">

        <table class="w-full text-left text-sm">
            <thead class="text-xs uppercase text-slate-400">
                <tr>
                    <th class="pb-2">Kode</th>
                    <th class="pb-2">Nama Vendor</th>
                    <th class="pb-2">Kontak</th>
                    <th class="pb-2 text-right">Jumlah PO</th>
                    <th class="pb-2">Status</th>
                    <th class="pb-2 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($vendors as $vendor)
                    <tr class="border-t border-slate-100">
                        <td class="py-2 font-medium">{{ $vendor->code }}</td>
                        <td class="py-2">
                            <a href="{{ route('purchasing.vendors.show', $vendor) }}" class="text-indigo-600 hover:underline">{{ $vendor->name }}</a>
                        </td>
                        <td class="py-2 text-slate-500">
                            {{ $vendor->contact_person ?: '-' }}
                            @if ($vendor->phone)
                                &middot; {{ $vendor->phone }}
                            @endif
                        </td>
                        <td class="py-2 text-right">{{ $vendor->purchase_orders_count }}</td>
                        <td class="py-2">
                            @if ($vendor->is_active)
                                <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-700">Aktif</span>
                            @else
                                <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs text-slate-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="py-2 text-right">
                            @if ($canManage)
                                <button wire:click="openEdit({{ $vendor->id }})" class="text-indigo-600 hover:underline">Edit</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-4 text-center text-slate-400">Belum ada vendor terdaftar.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $vendors->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showModal', false)">
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-slate-800">{{ $editingId ? 'Edit Vendor' : 'Tambah Vendor' }}</h3>
                <form wire:submit="save" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Kode Vendor</label>
                            <input type="text" wire:model="code" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('code') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">NPWP</label>
                            <input type="text" wire:model="npwp" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama Vendor</label>
                        <input type="text" wire:model="name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Contact Person</label>
                            <input type="text" wire:model="contactPerson" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Telepon</label>
                            <input type="text" wire:model="phone" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                        <input type="email" wire:model="email" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Alamat</label>
                        <textarea wire:model="address" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Catatan</label>
                        <textarea wire:model="notes" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                    </div>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model="isActive"> Vendor aktif
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
