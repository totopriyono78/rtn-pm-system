<div class="space-y-6">
    <x-page-header icon="clipboard-list" color="emerald" :title="$salesOrder->so_number" :subtitle="$salesOrder->customer->name.' · '.\App\Models\SalesOrder::STATUSES[$salesOrder->status]" />

    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded-xl bg-white p-5 shadow-sm md:col-span-2">
            <h3 class="mb-3 text-sm font-semibold text-slate-700">Informasi Sales Order</h3>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-xs text-slate-400">Tanggal SO</dt><dd>{{ $salesOrder->so_date->format('d M Y') }}</dd></div>
                <div><dt class="text-xs text-slate-400">Status</dt><dd><span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs">{{ \App\Models\SalesOrder::STATUSES[$salesOrder->status] }}</span></dd></div>
                <div><dt class="text-xs text-slate-400">Dari Prospect</dt><dd>{{ $salesOrder->prospect?->company_name ?? '-' }}</dd></div>
                <div><dt class="text-xs text-slate-400">Dibuat Oleh</dt><dd>{{ $salesOrder->creator->name ?? '-' }}</dd></div>
            </dl>
            @if ($salesOrder->description)
                <div class="mt-3 border-t border-slate-100 pt-3">
                    <dt class="text-xs text-slate-400">Deskripsi / Ruang Lingkup</dt>
                    <dd class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $salesOrder->description }}</dd>
                </div>
            @endif
            @if ($salesOrder->notes)
                <div class="mt-3 border-t border-slate-100 pt-3">
                    <dt class="text-xs text-slate-400">Catatan</dt>
                    <dd class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $salesOrder->notes }}</dd>
                </div>
            @endif
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h3 class="mb-2 text-sm font-semibold text-slate-700">Total Nilai</h3>
            <div class="text-2xl font-semibold text-slate-800">Rp {{ number_format((float) $salesOrder->total_value, 0, ',', '.') }}</div>

            <div class="mt-4 border-t border-slate-100 pt-4">
                @if ($salesOrder->status === 'draft')
                    <button wire:click="confirm" wire:confirm="Konfirmasi Sales Order ini? Contract &amp; Project baru akan otomatis dibuat dan tidak bisa dibatalkan lagi setelahnya." class="inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-500">
                        <x-icon name="check" class="h-3.5 w-3.5" /> Konfirmasi SO
                    </button>
                    <button wire:click="$set('showCancelModal', true)" class="mt-2 inline-flex w-full items-center justify-center gap-1.5 rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-100">
                        <x-icon name="x-circle" class="h-3.5 w-3.5" /> Batalkan SO
                    </button>
                @elseif ($salesOrder->status === 'confirmed')
                    <p class="mb-2 text-xs text-slate-500">Dikonfirmasi pada {{ optional($salesOrder->confirmed_at)->format('d M Y H:i') }}.</p>
                    @if ($salesOrder->contract?->directProject->first())
                        <a href="{{ route('projects.show', $salesOrder->contract->directProject->first()) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-500">
                            <x-icon name="briefcase" class="h-3.5 w-3.5" /> Buka Project
                        </a>
                    @endif
                @else
                    <p class="text-xs text-red-500">Sales Order ini sudah dibatalkan.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-700">Item Pompa / Barang</h3>
            @if ($salesOrder->status === 'draft')
                <button wire:click="openAddItem" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">
                    <x-icon name="plus" class="h-3.5 w-3.5" /> Tambah Item
                </button>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">Nama Item</th>
                        <th class="pb-2">Spesifikasi</th>
                        <th class="pb-2 text-right">Qty</th>
                        <th class="pb-2 text-right">Harga Satuan</th>
                        <th class="pb-2 text-right">Subtotal</th>
                        @if ($salesOrder->status === 'draft')
                            <th class="pb-2 text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($salesOrder->items as $item)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 font-medium">{{ $item->item_name }}</td>
                            <td class="py-2 text-slate-500">{{ $item->specification ?? '-' }}</td>
                            <td class="py-2 text-right">{{ rtrim(rtrim(number_format((float) $item->quantity, 2, '.', ''), '0'), '.') }} {{ $item->unit }}</td>
                            <td class="py-2 text-right">Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</td>
                            <td class="py-2 text-right font-medium">Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</td>
                            @if ($salesOrder->status === 'draft')
                                <td class="py-2 text-right">
                                    <button wire:click="removeItem({{ $item->id }})" wire:confirm="Hapus item ini?" class="text-xs text-red-500 hover:underline">Hapus</button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state icon="package" title="Belum ada item pompa/barang." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-700">Dokumen Teknis</h3>
            <button wire:click="openDocumentModal" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">
                <x-icon name="doc-plus" class="h-3.5 w-3.5" /> Unggah Dokumen
            </button>
        </div>
        <div class="space-y-2">
            @forelse ($salesOrder->documents as $document)
                <div class="flex items-center justify-between rounded-lg border border-slate-100 px-3 py-2 text-sm">
                    <div class="flex items-center gap-2">
                        <x-icon name="doc-text" class="h-4 w-4 text-slate-400" />
                        <div>
                            <a href="{{ route('sales.orders.documents.show', $document) }}" class="font-medium text-indigo-600 hover:underline">{{ $document->original_name }}</a>
                            <div class="text-xs text-slate-400">{{ $document->categoryLabel() }} &middot; {{ $document->uploader->name ?? '-' }}</div>
                        </div>
                    </div>
                    <button wire:click="removeDocument({{ $document->id }})" wire:confirm="Hapus dokumen ini?" class="text-xs text-red-500 hover:underline">Hapus</button>
                </div>
            @empty
                <x-empty-state icon="doc-text" title="Belum ada dokumen teknis diunggah." />
            @endforelse
        </div>
    </div>

    @if ($showItemModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showItemModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-slate-800">Tambah Item Pompa</h3>
                <form wire:submit="saveItem" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama Item</label>
                        <input type="text" wire:model="itemName" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('itemName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Spesifikasi</label>
                        <textarea wire:model="specification" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Qty</label>
                            <input type="number" step="0.01" wire:model="quantity" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Satuan</label>
                            <input type="text" wire:model="unit" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Harga Satuan</label>
                            <input type="number" step="0.01" wire:model="unitPrice" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('unitPrice') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showItemModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showDocumentModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showDocumentModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-slate-800">Unggah Dokumen Teknis</h3>
                <form wire:submit="saveDocument" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Kategori</label>
                        <select wire:model="documentCategory" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @foreach (\App\Models\SalesOrderDocument::CATEGORIES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">File</label>
                        <input type="file" wire:model="documentFile"
                            class="block w-full cursor-pointer rounded-lg border border-slate-300 p-2 text-sm text-slate-500 file:mr-4 file:cursor-pointer file:rounded-lg file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-500">
                        @error('documentFile') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showDocumentModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Unggah</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showCancelModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showCancelModal', false)">
            <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-2 text-lg font-semibold text-slate-800">Batalkan Sales Order?</h3>
                <p class="mb-4 text-sm text-slate-500">Sales Order yang dibatalkan tidak bisa diaktifkan kembali.</p>
                <div class="flex justify-end gap-2">
                    <button wire:click="$set('showCancelModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                    <button wire:click="cancel" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500">Ya, Batalkan</button>
                </div>
            </div>
        </div>
    @endif
</div>
