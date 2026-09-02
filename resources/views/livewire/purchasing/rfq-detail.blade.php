<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('purchasing.rfq') }}" class="text-sm text-indigo-600 hover:underline">&larr; Kembali</a>
            <h2 class="mt-1 text-xl font-semibold text-slate-800">{{ $rfq->code }}</h2>
            <p class="text-sm text-slate-500">{{ $rfq->project->name }} &middot; Dibuat oleh {{ $rfq->creator->name ?? '-' }}</p>
        </div>
        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium">{{ \App\Models\RequestForQuotation::STATUSES[$rfq->status] }}</span>
    </div>

    {{-- ===== Daftar material dibutuhkan ===== --}}
    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-700">Daftar Material / Jasa Dibutuhkan</h3>
            @if ($rfq->status === 'draft' && $canManage)
                <button wire:click="openAddItemModal" class="text-sm text-indigo-600 hover:underline">+ Tambah Item</button>
            @endif
        </div>
        <table class="w-full text-left text-sm">
            <thead class="text-xs uppercase text-slate-400">
                <tr>
                    <th class="pb-2">Item</th>
                    <th class="pb-2">Kategori</th>
                    <th class="pb-2 text-right">Qty</th>
                    <th class="pb-2">Satuan</th>
                    <th class="pb-2">Pemenang</th>
                    @if ($rfq->status === 'draft' && $canManage)
                        <th class="pb-2 text-right">Aksi</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @forelse ($rfq->items as $line)
                    <tr class="border-t border-slate-100">
                        <td class="py-2">{{ $line->item->name }}</td>
                        <td class="py-2 text-slate-500">{{ \App\Models\Item::CATEGORIES[$line->item->category] ?? $line->item->category }}</td>
                        <td class="py-2 text-right">{{ rtrim(rtrim(number_format($line->qty, 2), '0'), '.') }}</td>
                        <td class="py-2 text-slate-500">{{ $line->item->unit_of_measure }}</td>
                        <td class="py-2">
                            @if ($line->awardedVendorQuotationItem)
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs text-emerald-700">
                                    <x-icon name="check" class="h-3 w-3" />
                                    {{ $line->awardedVendorQuotationItem->vendorQuotation->vendor->name }}
                                </span>
                            @else
                                <span class="text-xs text-slate-400">Belum dipilih</span>
                            @endif
                        </td>
                        @if ($rfq->status === 'draft' && $canManage)
                            <td class="py-2 text-right">
                                <button wire:click="removeMaterialLine({{ $line->id }})" wire:confirm="Hapus item ini dari RFQ?" class="text-xs text-red-600 hover:underline">Hapus</button>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-4 text-center text-slate-400">Belum ada item.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ===== Penawaran vendor & perbandingan harga ===== --}}
    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-3 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-700">Penawaran Vendor &amp; Perbandingan Harga</h3>
            @if ($rfq->status === 'draft' && $canManage)
                <button wire:click="openVendorModal" class="text-sm text-indigo-600 hover:underline">+ Tambah Penawaran Vendor</button>
            @endif
        </div>

        @if ($rfq->vendorQuotations->isEmpty())
            <p class="py-4 text-center text-sm text-slate-400">Belum ada penawaran vendor yang diinput.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="text-xs uppercase text-slate-400">
                        <tr>
                            <th class="pb-2 pr-3">Item</th>
                            <th class="pb-2 pr-3 text-right">Qty</th>
                            @foreach ($rfq->vendorQuotations as $vq)
                                <th class="pb-2 pr-3">
                                    {{ $vq->vendor->name }}
                                    @if ($vq->reference_number)
                                        <div class="text-[10px] font-normal normal-case text-slate-400">Ref: {{ $vq->reference_number }}</div>
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rfq->items as $line)
                            @php
                                $offers = $line->vendorQuotationItems;
                                $cheapest = $offers->isNotEmpty() ? (float) $offers->min('unit_price') : null;
                            @endphp
                            <tr class="border-t border-slate-100 align-top">
                                <td class="py-2 pr-3">{{ $line->item->name }}</td>
                                <td class="py-2 pr-3 text-right">{{ rtrim(rtrim(number_format($line->qty, 2), '0'), '.') }}</td>
                                @foreach ($rfq->vendorQuotations as $vq)
                                    @php $offer = $offers->firstWhere('vendor_quotation_id', $vq->id); @endphp
                                    <td class="py-2 pr-3">
                                        @if ($offer)
                                            <div class="flex items-center gap-2">
                                                @if ($rfq->status === 'draft' && $canManage)
                                                    <label class="flex items-center gap-1.5">
                                                        <input type="radio" name="award-line-{{ $line->id }}"
                                                            wire:click="awardItem({{ $line->id }}, {{ $offer->id }})"
                                                            @checked($line->awarded_vendor_quotation_item_id === $offer->id)
                                                            class="text-emerald-600 focus:ring-emerald-500">
                                                        @if ($canViewHarga)
                                                            <input type="number" step="1" min="0" value="{{ (int) $offer->unit_price }}"
                                                                wire:blur="updateNegotiatedPrice({{ $offer->id }}, $event.target.value)"
                                                                class="w-24 rounded-lg border px-2 py-1 text-xs {{ $cheapest !== null && (float) $offer->unit_price === $cheapest ? 'border-emerald-400 bg-emerald-50 font-semibold text-emerald-700' : 'border-slate-300' }}">
                                                        @endif
                                                    </label>
                                                @else
                                                    <span class="inline-flex items-center gap-1 {{ $cheapest !== null && (float) $offer->unit_price === $cheapest ? 'font-semibold text-emerald-700' : 'text-slate-700' }}">
                                                        @if ($line->awarded_vendor_quotation_item_id === $offer->id)
                                                            <x-icon name="check" class="h-3.5 w-3.5 text-emerald-600" />
                                                        @endif
                                                        @if ($canViewHarga)
                                                            Rp {{ number_format($offer->unit_price, 0, ',', '.') }}
                                                        @else
                                                            Ditawar
                                                        @endif
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-slate-300">-</span>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <p class="mt-2 text-[11px] text-slate-400">Harga tersorot hijau = harga termurah pada baris tersebut. Pilih radio untuk menetapkan pemenang per item — hasil negosiasi bisa diperbarui langsung di kolom harga.</p>
        @endif

        @if ($rfq->status === 'draft' && $canManage)
            <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4">
                <span class="text-xs text-slate-500">{{ $awardedCount }} dari {{ $rfq->items->count() }} item sudah punya pemenang.</span>
                <button wire:click="submitForApproval" wire:confirm="Ajukan RFQ ini untuk approval Direktur?"
                    @disabled(! $isFullyAwarded)
                    class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:cursor-not-allowed disabled:bg-slate-300">
                    Ajukan untuk Approval
                </button>
            </div>
        @endif
    </div>

    {{-- ===== Ringkasan untuk approval Direktur ===== --}}
    @if ($rfq->status === 'submitted')
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-slate-700">Ringkasan Pemenang per Vendor</h3>
            @php $groups = $rfq->items->groupBy(fn ($l) => $l->awardedVendorQuotationItem->vendorQuotation->vendor->name); @endphp
            <div class="space-y-4">
                @foreach ($groups as $vendorName => $lines)
                    <div class="rounded-lg border border-slate-100 p-4">
                        <div class="mb-2 font-medium text-slate-800">{{ $vendorName }}</div>
                        <table class="w-full text-left text-sm">
                            <tbody>
                                @foreach ($lines as $line)
                                    <tr class="border-t border-slate-100 first:border-0">
                                        <td class="py-1.5">{{ $line->item->name }}</td>
                                        <td class="py-1.5 text-slate-500">{{ rtrim(rtrim(number_format($line->qty, 2), '0'), '.') }} {{ $line->item->unit_of_measure }}</td>
                                        @if ($canViewHarga)
                                            <td class="py-1.5 text-right">Rp {{ number_format($line->awardedVendorQuotationItem->subtotal, 0, ',', '.') }}</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            </div>

            @if ($canViewHarga)
                <div class="mt-4 text-right text-sm font-semibold text-slate-700">
                    Total Keseluruhan: Rp {{ number_format($rfq->items->sum(fn ($l) => $l->awardedVendorQuotationItem->subtotal), 0, ',', '.') }}
                </div>
            @endif

            @if ($canApprove)
                <div class="mt-6 flex gap-2 border-t border-slate-100 pt-4">
                    <button wire:click="approve" wire:confirm="Setujui RFQ ini? Purchase Order akan otomatis diterbitkan per vendor terpilih." class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">Approve</button>
                    <button wire:click="$set('showRejectModal', true)" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500">Reject</button>
                </div>
            @endif
        </div>
    @endif

    {{-- ===== Status approved/rejected ===== --}}
    @if ($rfq->status === 'approved')
        <div class="rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            Disetujui oleh {{ $rfq->approver->name ?? '-' }} pada {{ $rfq->approved_at?->format('d M Y H:i') }}
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-slate-700">Purchase Order Diterbitkan</h3>
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">No. PO</th>
                        <th class="pb-2">Vendor</th>
                        @if ($canViewHarga)
                            <th class="pb-2 text-right">Total</th>
                        @endif
                        <th class="pb-2">Status</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rfq->purchaseOrders as $po)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 font-medium">{{ $po->code }}</td>
                            <td class="py-2">{{ $po->vendor->name }}</td>
                            @if ($canViewHarga)
                                <td class="py-2 text-right">Rp {{ number_format($po->total, 0, ',', '.') }}</td>
                            @endif
                            <td class="py-2"><span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs">{{ \App\Models\PurchaseOrder::STATUSES[$po->status] }}</span></td>
                            <td class="py-2 text-right">
                                <a href="{{ route('purchasing.po.print', $po) }}" target="_blank" class="text-indigo-600 hover:underline">Cetak</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($rfq->status === 'rejected')
        <div class="rounded-lg bg-red-50 px-4 py-3 text-sm text-red-700">
            Ditolak oleh {{ $rfq->approver->name ?? '-' }} pada {{ $rfq->approved_at?->format('d M Y H:i') }}
        </div>
    @endif

    @if ($rfq->notes)
        <p class="text-sm text-slate-500"><span class="font-medium text-slate-700">Catatan:</span> {{ $rfq->notes }}</p>
    @endif

    {{-- ===== Modal: tambah item material ===== --}}
    @if ($showAddItemModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showAddItemModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-slate-800">Tambah Item</h3>
                <form wire:submit="addMaterialLine" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Item</label>
                        <select wire:model="newItemId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- pilih item --</option>
                            @foreach ($items as $item)
                                <option value="{{ $item->id }}">{{ $item->code }} - {{ $item->name }}</option>
                            @endforeach
                        </select>
                        @error('newItemId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Qty</label>
                        <input type="number" step="0.01" min="0" wire:model="newItemQty" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('newItemQty') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showAddItemModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">Batal</button>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Tambah</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ===== Modal: tambah penawaran vendor ===== --}}
    @if ($showVendorModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showVendorModal', false)">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-slate-800">Tambah Penawaran Vendor</h3>
                <form wire:submit="saveVendorQuotation" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Vendor</label>
                            <select wire:model="vendorId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">-- pilih vendor --</option>
                                @foreach ($vendors as $v)
                                    <option value="{{ $v->id }}">{{ $v->code }} - {{ $v->name }}</option>
                                @endforeach
                            </select>
                            @error('vendorId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            <p class="mt-1 text-[11px] text-slate-400">Vendor belum terdaftar? Tambahkan dulu di halaman Vendor.</p>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">No. Referensi Penawaran</label>
                            <input type="text" wire:model="referenceNumber" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="opsional">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal Penawaran</label>
                        <input type="date" wire:model="quotedAt" class="w-full max-w-xs rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Harga per Item</label>
                        <p class="mb-2 text-xs text-slate-500">Kosongkan bila vendor tidak menawar item tertentu.</p>
                        <div class="space-y-2">
                            @foreach ($rfq->items as $line)
                                <div class="grid grid-cols-12 items-center gap-2">
                                    <div class="col-span-7 text-sm">{{ $line->item->name }} <span class="text-slate-400">({{ rtrim(rtrim(number_format($line->qty, 2), '0'), '.') }} {{ $line->item->unit_of_measure }})</span></div>
                                    <div class="col-span-5">
                                        <input type="number" step="1" min="0" wire:model="vendorPrices.{{ $line->id }}" placeholder="Harga satuan" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('vendorPrices') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Catatan</label>
                        <textarea wire:model="vendorNotes" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showVendorModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">Batal</button>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Simpan Penawaran</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- ===== Modal: reject ===== --}}
    @if ($showRejectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showRejectModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-slate-800">Tolak RFQ</h3>
                <textarea wire:model="rejectNote" rows="3" placeholder="Alasan penolakan (opsional)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                <div class="mt-4 flex justify-end gap-2">
                    <button wire:click="$set('showRejectModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">Batal</button>
                    <button wire:click="reject" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500">Tolak</button>
                </div>
            </div>
        </div>
    @endif
</div>
