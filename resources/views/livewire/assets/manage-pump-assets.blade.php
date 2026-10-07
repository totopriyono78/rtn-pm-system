<div class="space-y-6">
    <div class="flex items-center justify-between">
        <x-page-header icon="cube" color="sky" title="Master Unit Pompa" subtitle="Aset pompa terpasang di lokasi klien -- tipe, serial number, dan riwayat service." />
        <button wire:click="openCreate" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-500">
            <x-icon name="plus" class="h-4 w-4" /> Tambah Unit Pompa
        </button>
    </div>

    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 px-4 py-2.5 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <div class="relative max-w-sm flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <x-icon name="search" class="h-4 w-4" />
                </span>
                <input wire:model.live.debounce.400ms="search" type="text" placeholder="Cari serial number / tipe pompa..."
                    class="w-full rounded-lg border border-slate-300 py-2 pl-9 pr-3 text-sm">
            </div>
            <select wire:model.live="conditionFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Kondisi</option>
                @foreach (\App\Models\PumpAsset::CONDITIONS as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">Tipe Pompa / Engine</th>
                        <th class="pb-2">Serial Number</th>
                        <th class="pb-2">Klien / Lokasi</th>
                        <th class="pb-2">Service Terakhir</th>
                        <th class="pb-2">Kondisi</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assets as $asset)
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2">
                                <div class="font-medium">{{ $asset->pump_type }} {{ $asset->pump_model }}</div>
                                @if ($asset->engine_type)
                                    <div class="text-xs text-slate-400">Engine: {{ $asset->engine_type }} {{ $asset->engine_model }}</div>
                                @endif
                            </td>
                            <td class="py-2 text-slate-500">{{ $asset->serial_number ?: '-' }}</td>
                            <td class="py-2 text-slate-500">
                                {{ $asset->customer?->name ?? '-' }}
                                @if ($asset->site)
                                    <div class="text-xs text-slate-400">{{ $asset->site->name }}</div>
                                @endif
                            </td>
                            <td class="py-2 text-slate-500">{{ optional($asset->last_service_date)->format('d M Y') ?? '-' }}</td>
                            <td class="py-2">
                                @php
                                    $condColor = match ($asset->condition) {
                                        'baik' => 'bg-green-100 text-green-700',
                                        'perlu_perhatian' => 'bg-amber-100 text-amber-700',
                                        default => 'bg-red-100 text-red-700',
                                    };
                                @endphp
                                <span class="inline-flex items-center rounded-full {{ $condColor }} px-2 py-0.5 text-xs">
                                    {{ \App\Models\PumpAsset::CONDITIONS[$asset->condition] }}
                                </span>
                            </td>
                            <td class="py-2 text-right">
                                <button wire:click="openServiceLog({{ $asset->id }})" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-100">
                                    <x-icon name="clock" class="h-3.5 w-3.5" /> Riwayat
                                </button>
                                <button wire:click="openEdit({{ $asset->id }})" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 transition-colors hover:bg-indigo-50">
                                    <x-icon name="edit" class="h-3.5 w-3.5" /> Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state icon="cube" title="Belum ada unit pompa terdaftar." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $assets->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showModal', false)">
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="{{ $editingId ? 'edit' : 'plus-circle' }}" class="h-5 w-5 text-indigo-500" />
                    {{ $editingId ? 'Edit Unit Pompa' : 'Tambah Unit Pompa' }}
                </h3>
                <form wire:submit="save" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tipe Pompa</label>
                            <input type="text" wire:model="pumpType" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="mis. Centrifugal">
                            @error('pumpType') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Model Pompa</label>
                            <input type="text" wire:model="pumpModel" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Serial Number</label>
                            <input type="text" wire:model="serialNumber" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Kapasitas</label>
                            <input type="text" wire:model="pumpCapacity" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="mis. 50 m3/jam @ 30m">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tipe Engine</label>
                            <input type="text" wire:model="engineType" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="mis. Diesel">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Model Engine</label>
                            <input type="text" wire:model="engineModel" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Klien</label>
                            <select wire:model="customerId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">-- Pilih Klien --</option>
                                @foreach ($customers as $customer)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Site / Lokasi</label>
                            <select wire:model="siteId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">-- Pilih Site --</option>
                                @foreach ($sites as $site)
                                    <option value="{{ $site->id }}">{{ $site->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal Instalasi</label>
                            <input type="date" wire:model="installDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Kondisi</label>
                            <select wire:model="condition" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                @foreach (\App\Models\PumpAsset::CONDITIONS as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Catatan</label>
                        <textarea wire:model="notes" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                    </div>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model="isActive"> Unit aktif
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

    @if ($showServiceLogModal && $serviceLogAsset)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="closeServiceLog">
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-1 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="clock" class="h-5 w-5 text-indigo-500" /> Riwayat Service
                </h3>
                <p class="mb-4 text-sm text-slate-500">{{ $serviceLogAsset->label() }}</p>

                <form wire:submit="saveServiceLog" class="mb-5 space-y-3 rounded-lg border border-slate-200 p-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal Service</label>
                            <input type="date" wire:model="serviceDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('serviceDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Teknisi</label>
                            <select wire:model="serviceTechnicianId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">-- Pilih Teknisi --</option>
                                @foreach ($technicians as $tech)
                                    <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Proyek Terkait (opsional)</label>
                        <select wire:model="serviceProjectId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- Tidak terkait proyek --</option>
                            @foreach ($projects as $project)
                                <option value="{{ $project->id }}">{{ $project->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Deskripsi Pekerjaan</label>
                        <textarea wire:model="serviceDescription" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('serviceDescription') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">
                            <x-icon name="plus" class="h-3.5 w-3.5" /> Tambah Riwayat
                        </button>
                    </div>
                </form>

                <div class="space-y-2">
                    @forelse ($serviceLogAsset->serviceLogs as $log)
                        <div class="rounded-lg bg-slate-50 p-3 text-sm">
                            <div class="flex items-center justify-between">
                                <span class="font-medium">{{ $log->service_date->format('d M Y') }}</span>
                                @if ($log->technician)
                                    <span class="text-xs text-slate-400">{{ $log->technician->name }}</span>
                                @endif
                            </div>
                            <p class="mt-1 text-slate-600">{{ $log->description }}</p>
                            @if ($log->project)
                                <p class="mt-1 text-xs text-indigo-500">Proyek: {{ $log->project->name }}</p>
                            @endif
                        </div>
                    @empty
                        <x-empty-state icon="clock" title="Belum ada riwayat service." />
                    @endforelse
                </div>

                <div class="mt-4 flex justify-end">
                    <button type="button" wire:click="closeServiceLog" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Tutup</button>
                </div>
            </div>
        </div>
    @endif
</div>
