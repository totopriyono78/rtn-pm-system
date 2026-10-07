<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <x-page-header icon="doc-text" color="violet" :title="'RO '.$releaseOrder->ro_number" :subtitle="$releaseOrder->contract->customer->name.' · Kontrak '.$releaseOrder->contract->contract_number" />
        @if ($canManageContract)
            <div class="flex items-center gap-2">
                <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600">{{ \App\Models\ReleaseOrder::STATUSES[$releaseOrder->status] }}</span>
                <button wire:click="openEdit" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-600 hover:bg-slate-50">
                    <x-icon name="edit" class="h-3.5 w-3.5" /> Edit
                </button>
                @if ($releaseOrder->rfqs->isEmpty() && $releaseOrder->quotations->isEmpty())
                    <button wire:click="deleteRo" wire:confirm="Hapus Release Order ini beserta seluruh itemnya? Tindakan ini tidak bisa dibatalkan." class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">
                        <x-icon name="trash" class="h-3.5 w-3.5" /> Hapus
                    </button>
                @elseif (! in_array($releaseOrder->status, ['batal', 'selesai'], true))
                    <button wire:click="cancelRo" wire:confirm="Batalkan Release Order ini? RFQ/Penawaran yang sudah dibuat tetap tersimpan sebagai histori." class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">
                        <x-icon name="close" class="h-3.5 w-3.5" /> Batalkan
                    </button>
                @endif
            </div>
        @endif
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-700">Item Kebutuhan (dari Customer)</h3>
            @if ($canManageContract)
                <button wire:click="openAddItem" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">
                    <x-icon name="plus" class="h-3.5 w-3.5" /> Tambah Item
                </button>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">Deskripsi</th>
                        <th class="pb-2">Site</th>
                        <th class="pb-2 text-right">Qty</th>
                        <th class="pb-2">Satuan</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($releaseOrder->items as $item)
                        <tr class="border-t border-slate-100">
                            <td class="py-2">{{ $item->description }}</td>
                            <td class="py-2 text-slate-500">{{ $item->site->name ?? '-' }}</td>
                            <td class="py-2 text-right">{{ rtrim(rtrim(number_format($item->qty, 2, '.', ''), '0'), '.') }}</td>
                            <td class="py-2 text-slate-500">{{ $item->unit ?? '-' }}</td>
                            <td class="py-2 text-right">
                                @if ($canManageContract)
                                    <button wire:click="removeItem({{ $item->id }})" wire:confirm="Hapus item ini?" class="text-red-500 hover:text-red-700">
                                        <x-icon name="trash" class="h-3.5 w-3.5" />
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty-state icon="inbox" title="Belum ada item dari Release Order ini." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-700">RFQ Costing (harga vendor — dasar Penawaran)</h3>
            @if ($canManagePurchasing)
                <button wire:click="openRfqModal" class="inline-flex items-center gap-1.5 rounded-lg bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-400">
                    <x-icon name="truck" class="h-3.5 w-3.5" /> Minta Harga ke Vendor
                </button>
            @endif
        </div>
        <p class="mb-3 text-xs text-slate-500">RFQ di sini tidak menerbitkan Purchase Order riil — hasilnya cuma dipakai Project Controller sebagai dasar harga penawaran ke customer.</p>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">Kode RFQ</th>
                        <th class="pb-2">Status</th>
                        <th class="pb-2 text-right">Total Hasil Award</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($releaseOrder->rfqs as $rfq)
                        <tr class="border-t border-slate-100 hover:bg-slate-50/70">
                            <td class="py-2 font-medium"><a href="{{ route('purchasing.rfq.show', $rfq) }}" class="text-indigo-600 hover:underline">{{ $rfq->code }}</a></td>
                            <td class="py-2"><span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs">{{ \App\Models\RequestForQuotation::STATUSES[$rfq->status] }}</span></td>
                            <td class="py-2 text-right">Rp {{ number_format($rfq->awarded_total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><x-empty-state icon="truck" title="Belum ada RFQ costing dibuat." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-700">Penawaran ke Customer (CustomerQuotation)</h3>
            @if ($canManageContract)
                <button wire:click="createQuotation" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">
                    <x-icon name="plus" class="h-3.5 w-3.5" /> Buat Penawaran Baru
                </button>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">Kode</th>
                        <th class="pb-2 text-right">Revisi</th>
                        <th class="pb-2">Status</th>
                        <th class="pb-2 text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($releaseOrder->quotations->sortByDesc('revision_no') as $q)
                        <tr class="border-t border-slate-100 hover:bg-slate-50/70">
                            <td class="py-2 font-medium"><a href="{{ route('contracts.quotations.show', $q) }}" class="text-indigo-600 hover:underline">{{ $q->code }}</a></td>
                            <td class="py-2 text-right">{{ $q->revision_no }}</td>
                            <td class="py-2"><span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs">{{ \App\Models\CustomerQuotation::STATUSES[$q->status] }}</span></td>
                            <td class="py-2 text-right">Rp {{ number_format($q->total, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-empty-state icon="doc-text" title="Belum ada penawaran dibuat." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($showItemModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showItemModal', false)">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="plus-circle" class="h-5 w-5 text-indigo-500" /> Tambah Item Release Order
                </h3>
                <form wire:submit="saveItem" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Deskripsi</label>
                        <input type="text" wire:model="itemDescription" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('itemDescription') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Qty</label>
                            <input type="number" step="0.01" wire:model="itemQty" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('itemQty') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Satuan</label>
                            <input type="text" wire:model="itemUnit" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Site Tujuan</label>
                        <select wire:model="itemSiteId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- tidak spesifik --</option>
                            @foreach ($sites as $site)
                                <option value="{{ $site->id }}">{{ $site->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Catatan Spesifikasi</label>
                        <textarea wire:model="itemSpecNotes" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showItemModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                            <x-icon name="check" class="h-4 w-4" /> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showRfqModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showRfqModal', false)">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-1 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="truck" class="h-5 w-5 text-amber-500" /> Minta Harga ke Vendor
                </h3>
                <p class="mb-4 text-xs text-slate-500">Pilih item dari master Item Purchasing beserta qty. Harga diisi vendor di halaman detail RFQ setelahnya.</p>
                <form wire:submit="saveRfq" class="space-y-4">
                    <div class="space-y-2">
                        @foreach ($rfqLines as $i => $line)
                            <div class="grid grid-cols-12 items-center gap-2">
                                <div class="col-span-8">
                                    <select wire:model="rfqLines.{{ $i }}.item_id" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                        <option value="">-- pilih item --</option>
                                        @foreach ($items as $item)
                                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-span-3">
                                    <input type="number" step="0.01" wire:model="rfqLines.{{ $i }}.qty" placeholder="Qty" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                </div>
                                <div class="col-span-1 text-right">
                                    <button type="button" wire:click="removeRfqLine({{ $i }})" class="text-red-500 hover:text-red-700">
                                        <x-icon name="trash" class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" wire:click="addRfqLine" class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:underline">
                        <x-icon name="plus-circle" class="h-3.5 w-3.5" /> Tambah baris
                    </button>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showRfqModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                            <x-icon name="check" class="h-4 w-4" /> Buat RFQ
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showEditModal', false)">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="edit" class="h-5 w-5 text-violet-500" /> Edit Release Order
                </h3>
                <form wire:submit="saveEdit" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nomor RO</label>
                        <input type="text" wire:model="roNumber" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('roNumber') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal RO</label>
                        <input type="date" wire:model="roDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('roDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Catatan</label>
                        <textarea wire:model="roNotes" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('roNotes') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showEditModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                            <x-icon name="check" class="h-4 w-4" /> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
