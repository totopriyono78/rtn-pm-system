<div class="space-y-6">
    <div class="flex items-center justify-between">
        <x-page-header icon="truck" color="orange" title="Surat Jalan & Gatepass" subtitle="Admin Purchase -- tracking dokumen pengiriman dan akses keluar/masuk material." />
        <button wire:click="openCreate" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-500">
            <x-icon name="plus" class="h-4 w-4" /> Tambah Dokumen
        </button>
    </div>

    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 px-4 py-2.5 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-4">
            <select wire:model.live="typeFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Tipe</option>
                @foreach (\App\Models\DeliveryGatepass::TYPES as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">Tipe</th>
                        <th class="pb-2">No. Dokumen</th>
                        <th class="pb-2">Tanggal</th>
                        <th class="pb-2">Supir / Plat</th>
                        <th class="pb-2">PO / Proyek</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2">
                                <span class="inline-flex items-center rounded-full bg-orange-100 px-2 py-0.5 text-xs text-orange-700">{{ \App\Models\DeliveryGatepass::TYPES[$record->type] }}</span>
                            </td>
                            <td class="py-2 font-medium">{{ $record->document_number ?: '-' }}</td>
                            <td class="py-2 text-slate-500">{{ $record->gate_date->format('d M Y') }}</td>
                            <td class="py-2 text-slate-500">{{ $record->driver_name ?: '-' }} {{ $record->vehicle_plate ? '('.$record->vehicle_plate.')' : '' }}</td>
                            <td class="py-2 text-slate-500">
                                {{ $record->purchaseOrder?->code ?? '-' }}
                                @if ($record->project)
                                    <div class="text-xs text-slate-400">{{ $record->project->name }}</div>
                                @endif
                            </td>
                            <td class="py-2 text-right">
                                @if ($record->disk_path)
                                    <a href="{{ route('operasional.gatepasses.file', $record) }}" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-100">
                                        <x-icon name="download" class="h-3.5 w-3.5" /> Scan
                                    </a>
                                @endif
                                <button wire:click="openEdit({{ $record->id }})" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 transition-colors hover:bg-indigo-50">
                                    <x-icon name="edit" class="h-3.5 w-3.5" /> Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state icon="truck" title="Belum ada surat jalan / gatepass." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $records->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showModal', false)">
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="{{ $editingId ? 'edit' : 'plus-circle' }}" class="h-5 w-5 text-indigo-500" />
                    {{ $editingId ? 'Edit Dokumen' : 'Tambah Dokumen' }}
                </h3>
                <form wire:submit="save" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tipe Dokumen</label>
                            <select wire:model="type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                @foreach (\App\Models\DeliveryGatepass::TYPES as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">No. Dokumen</label>
                            <input type="text" wire:model="documentNumber" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal</label>
                            <input type="date" wire:model="gateDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('gateDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Nama Vendor</label>
                            <input type="text" wire:model="vendorName" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Nama Supir</label>
                            <input type="text" wire:model="driverName" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Plat Kendaraan</label>
                            <input type="text" wire:model="vehiclePlate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Purchase Order</label>
                            <select wire:model="purchaseOrderId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">-- Tidak terkait PO --</option>
                                @foreach ($purchaseOrders as $po)
                                    <option value="{{ $po->id }}">{{ $po->code }} ({{ $po->vendor?->name }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Proyek</label>
                            <select wire:model="projectId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">-- Tidak terkait proyek --</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Catatan</label>
                        <textarea wire:model="notes" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Scan Dokumen (opsional)</label>
                        <input type="file" wire:model="file" class="block w-full cursor-pointer rounded-lg border border-slate-300 p-2 text-sm text-slate-500 file:mr-4 file:cursor-pointer file:rounded-lg file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-500">
                        <div wire:loading wire:target="file" class="mt-1 text-xs text-slate-400">Mengunggah...</div>
                        @error('file') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
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
