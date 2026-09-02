<div class="space-y-6">
    <x-page-header icon="map-pin" color="sky" title="Region & Unit" subtitle="Master hierarki lokasi: Region → Integrated Terminal / Unit → Proyek." />

    <div class="flex gap-2 border-b border-slate-200">
        <button wire:click="$set('activeTab', 'region')" class="flex items-center gap-1.5 border-b-2 px-4 py-2 text-sm transition-colors {{ $activeTab === 'region' ? 'border-indigo-600 font-semibold text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            <x-icon name="map-pin" class="h-4 w-4" /> Region
        </button>
        <button wire:click="$set('activeTab', 'unit')" class="flex items-center gap-1.5 border-b-2 px-4 py-2 text-sm transition-colors {{ $activeTab === 'unit' ? 'border-indigo-600 font-semibold text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
            <x-icon name="building" class="h-4 w-4" /> Unit / Integrated Terminal
        </button>
    </div>

    @if ($activeTab === 'region')
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="mb-4 flex justify-end">
                <button wire:click="openCreateRegion" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-500">
                    <x-icon name="plus" class="h-4 w-4" /> Tambah Region
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase text-slate-400">
                        <tr><th class="pb-2">Kode</th><th class="pb-2">Nama</th><th class="pb-2">Jumlah Unit</th><th class="pb-2 text-right">Aksi</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($regions as $region)
                            <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                                <td class="py-2 font-medium">{{ $region->code }}</td>
                                <td class="py-2">{{ $region->name }}</td>
                                <td class="py-2 text-slate-500">{{ $region->units_count }}</td>
                                <td class="py-2 text-right">
                                    <button wire:click="openEditRegion({{ $region->id }})" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 transition-colors hover:bg-indigo-50">
                                        <x-icon name="edit" class="h-3.5 w-3.5" /> Edit
                                    </button>
                                    <button wire:click="deleteRegion({{ $region->id }})" wire:confirm="Hapus region ini?" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-red-600 transition-colors hover:bg-red-50">
                                        <x-icon name="trash" class="h-3.5 w-3.5" /> Hapus
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><x-empty-state icon="map-pin" title="Belum ada region." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="mb-4 flex justify-end">
                <button wire:click="openCreateUnit" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-500">
                    <x-icon name="plus" class="h-4 w-4" /> Tambah Unit
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase text-slate-400">
                        <tr><th class="pb-2">Kode</th><th class="pb-2">Nama</th><th class="pb-2">Region</th><th class="pb-2 text-right">Aksi</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($units as $unit)
                            <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                                <td class="py-2 font-medium">{{ $unit->code }}</td>
                                <td class="py-2">{{ $unit->name }}</td>
                                <td class="py-2 text-slate-500">{{ $unit->region->name }}</td>
                                <td class="py-2 text-right">
                                    <button wire:click="openEditUnit({{ $unit->id }})" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 transition-colors hover:bg-indigo-50">
                                        <x-icon name="edit" class="h-3.5 w-3.5" /> Edit
                                    </button>
                                    <button wire:click="deleteUnit({{ $unit->id }})" wire:confirm="Hapus unit ini?" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-red-600 transition-colors hover:bg-red-50">
                                        <x-icon name="trash" class="h-3.5 w-3.5" /> Hapus
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><x-empty-state icon="building" title="Belum ada unit." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($showRegionModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showRegionModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="{{ $editingRegionId ? 'edit' : 'plus-circle' }}" class="h-5 w-5 text-indigo-500" />
                    {{ $editingRegionId ? 'Edit Region' : 'Tambah Region' }}
                </h3>
                <form wire:submit="saveRegion" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Kode</label>
                        <input type="text" wire:model="regionCode" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="mis. JBB">
                        @error('regionCode') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama</label>
                        <input type="text" wire:model="regionName" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="mis. Region Jawa Bagian Barat">
                        @error('regionName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showRegionModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                            <x-icon name="check" class="h-4 w-4" /> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showUnitModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showUnitModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="{{ $editingUnitId ? 'edit' : 'plus-circle' }}" class="h-5 w-5 text-indigo-500" />
                    {{ $editingUnitId ? 'Edit Unit' : 'Tambah Unit' }}
                </h3>
                <form wire:submit="saveUnit" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Region</label>
                        <select wire:model="unitRegionId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- pilih region --</option>
                            @foreach ($regions as $region)
                                <option value="{{ $region->id }}">{{ $region->name }}</option>
                            @endforeach
                        </select>
                        @error('unitRegionId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Kode</label>
                        <input type="text" wire:model="unitCode" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="mis. IT-JKT">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama</label>
                        <input type="text" wire:model="unitName" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="mis. IT Jakarta">
                        @error('unitName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showUnitModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                            <x-icon name="check" class="h-4 w-4" /> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
