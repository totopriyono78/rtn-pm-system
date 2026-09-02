<div class="space-y-6">
    <div>
        <h2 class="text-xl font-semibold text-slate-800">Region & Unit</h2>
        <p class="text-sm text-slate-500">Master hierarki lokasi: Region &rarr; Integrated Terminal / Unit &rarr; Proyek.</p>
    </div>

    <div class="flex gap-2 border-b border-slate-200">
        <button wire:click="$set('activeTab', 'region')" class="border-b-2 px-4 py-2 text-sm {{ $activeTab === 'region' ? 'border-indigo-600 font-semibold text-indigo-600' : 'border-transparent text-slate-500' }}">Region</button>
        <button wire:click="$set('activeTab', 'unit')" class="border-b-2 px-4 py-2 text-sm {{ $activeTab === 'unit' ? 'border-indigo-600 font-semibold text-indigo-600' : 'border-transparent text-slate-500' }}">Unit / Integrated Terminal</button>
    </div>

    @if ($activeTab === 'region')
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="mb-4 flex justify-end">
                <button wire:click="openCreateRegion" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">+ Tambah Region</button>
            </div>
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr><th class="pb-2">Kode</th><th class="pb-2">Nama</th><th class="pb-2">Jumlah Unit</th><th class="pb-2 text-right">Aksi</th></tr>
                </thead>
                <tbody>
                    @foreach ($regions as $region)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 font-medium">{{ $region->code }}</td>
                            <td class="py-2">{{ $region->name }}</td>
                            <td class="py-2 text-slate-500">{{ $region->units_count }}</td>
                            <td class="py-2 text-right space-x-2">
                                <button wire:click="openEditRegion({{ $region->id }})" class="text-indigo-600 hover:underline">Edit</button>
                                <button wire:click="deleteRegion({{ $region->id }})" wire:confirm="Hapus region ini?" class="text-red-600 hover:underline">Hapus</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="mb-4 flex justify-end">
                <button wire:click="openCreateUnit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">+ Tambah Unit</button>
            </div>
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr><th class="pb-2">Kode</th><th class="pb-2">Nama</th><th class="pb-2">Region</th><th class="pb-2 text-right">Aksi</th></tr>
                </thead>
                <tbody>
                    @foreach ($units as $unit)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 font-medium">{{ $unit->code }}</td>
                            <td class="py-2">{{ $unit->name }}</td>
                            <td class="py-2 text-slate-500">{{ $unit->region->name }}</td>
                            <td class="py-2 text-right space-x-2">
                                <button wire:click="openEditUnit({{ $unit->id }})" class="text-indigo-600 hover:underline">Edit</button>
                                <button wire:click="deleteUnit({{ $unit->id }})" wire:confirm="Hapus unit ini?" class="text-red-600 hover:underline">Hapus</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($showRegionModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showRegionModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-slate-800">{{ $editingRegionId ? 'Edit Region' : 'Tambah Region' }}</h3>
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
                        <button type="button" wire:click="$set('showRegionModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">Batal</button>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showUnitModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showUnitModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-slate-800">{{ $editingUnitId ? 'Edit Unit' : 'Tambah Unit' }}</h3>
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
                        <button type="button" wire:click="$set('showUnitModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">Batal</button>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
