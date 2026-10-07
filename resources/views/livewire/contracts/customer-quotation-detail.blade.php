<div class="max-w-4xl space-y-6">
    <x-page-header icon="doc-text" color="sky" :title="$quotation->code" :subtitle="$quotation->releaseOrder->contract->customer->name.' · RO '.$quotation->releaseOrder->ro_number" />

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium">{{ \App\Models\CustomerQuotation::STATUSES[$quotation->status] }}</span>
            <div class="text-right">
                <div class="text-xs text-slate-400">Total Penawaran</div>
                <div class="text-xl font-semibold text-slate-800">Rp {{ number_format($quotation->total, 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">Deskripsi</th>
                        <th class="pb-2 text-right">Qty</th>
                        <th class="pb-2">Satuan</th>
                        <th class="pb-2 text-right">Harga Satuan</th>
                        <th class="pb-2 text-right">Subtotal</th>
                        @if ($quotation->status === 'draft' && $canManage)
                            <th class="pb-2 text-right">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($quotation->items as $item)
                        <tr class="border-t border-slate-100">
                            <td class="py-2">
                                {{ $item->description }}
                                @if ($item->vendor_reference_note)
                                    <div class="text-[11px] text-slate-400">Ref: {{ $item->vendor_reference_note }}</div>
                                @endif
                            </td>
                            <td class="py-2 text-right">{{ rtrim(rtrim(number_format($item->qty, 2, '.', ''), '0'), '.') }}</td>
                            <td class="py-2 text-slate-500">{{ $item->unit ?? '-' }}</td>
                            <td class="py-2 text-right">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                            <td class="py-2 text-right font-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                            @if ($quotation->status === 'draft' && $canManage)
                                <td class="py-2 text-right">
                                    <button wire:click="removeItem({{ $item->id }})" wire:confirm="Hapus item ini?" class="text-red-500 hover:text-red-700">
                                        <x-icon name="trash" class="h-3.5 w-3.5" />
                                    </button>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state icon="inbox" title="Belum ada item penawaran." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($quotation->status === 'draft' && $canManage)
            <button wire:click="openAddItem" class="mt-4 inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">
                <x-icon name="plus" class="h-3.5 w-3.5" /> Tambah Item
            </button>
        @endif
    </div>

    @if ($quotation->releaseOrder->items->isNotEmpty())
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h3 class="mb-3 text-sm font-semibold text-slate-700">Referensi: Item Release Order Customer</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-500">
                    <tbody>
                        @foreach ($quotation->releaseOrder->items as $roItem)
                            <tr class="border-t border-slate-100">
                                <td class="py-1.5">{{ $roItem->description }}</td>
                                <td class="py-1.5 text-right">{{ rtrim(rtrim(number_format($roItem->qty, 2, '.', ''), '0'), '.') }} {{ $roItem->unit }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="flex flex-wrap items-center gap-2">
        @if ($quotation->status === 'draft' && $canManage)
            <button wire:click="submitForApproval" wire:confirm="Ajukan penawaran ini untuk approval Direktur?" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                <x-icon name="check" class="h-4 w-4" /> Ajukan ke Direktur
            </button>
        @endif

        @if ($quotation->status === 'submitted' && $canApprove)
            <button wire:click="approve" wire:confirm="Setujui penawaran ini? Akan dianggap terkirim ke customer." class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                <x-icon name="check" class="h-4 w-4" /> Setujui
            </button>
            <button wire:click="$set('showRejectModal', true)" class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50">
                <x-icon name="x-circle" class="h-4 w-4" /> Tolak
            </button>
        @endif

        @if ($quotation->status === 'approved' && $canManage)
            <button wire:click="openCustomerDecisionModal" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                <x-icon name="mail" class="h-4 w-4" /> Catat Keputusan Customer
            </button>
        @endif

        @if ($quotation->status === 'customer_accepted' && $canManage)
            @if ($quotation->customerPurchaseOrder)
                @if ($quotation->customerPurchaseOrder->status === 'received')
                    <button wire:click="convertToProject" wire:confirm="Buat Project baru dari PO customer ini?" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                        <x-icon name="briefcase" class="h-4 w-4" /> Buat Project dari PO Ini
                    </button>
                @else
                    <a href="{{ route('projects.show', $quotation->customerPurchaseOrder->project) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                        <x-icon name="briefcase" class="h-4 w-4" /> Buka Project
                    </a>
                @endif
            @else
                <button wire:click="openPoModal" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                    <x-icon name="doc-plus" class="h-4 w-4" /> Catat PO Customer
                </button>
            @endif
        @endif
    </div>

    @if ($quotation->signatures->isNotEmpty())
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h3 class="mb-3 text-sm font-semibold text-slate-700">Jejak Tanda Tangan Digital</h3>
            <div class="space-y-2">
                @foreach ($quotation->signatures as $sig)
                    <div class="flex items-center gap-3 text-sm">
                        <x-icon name="shield" class="h-4 w-4 text-emerald-500" />
                        <span class="font-medium">{{ $sig->user->name ?? '-' }}</span>
                        <span class="text-slate-400">— {{ $sig->role_label }}</span>
                        <span class="ml-auto text-xs text-slate-400">{{ $sig->signed_at->format('d M Y H:i') }}</span>
                    </div>
                @endforeach
            </div>
            <p class="mt-3 text-[11px] text-slate-400">Paraf digital internal untuk kebutuhan operasional (bukan tanda tangan elektronik bersertifikat pihak ketiga).</p>
        </div>
    @endif

    @if ($showRejectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showRejectModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-slate-800">Tolak Penawaran</h3>
                <textarea wire:model="rejectNote" rows="3" placeholder="Alasan penolakan (opsional)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                <div class="mt-4 flex justify-end gap-2">
                    <button wire:click="$set('showRejectModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                    <button wire:click="reject" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500">Tolak Penawaran</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showCustomerDecisionModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showCustomerDecisionModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-2 text-lg font-semibold text-slate-800">Catat Keputusan Customer</h3>
                <p class="mb-3 text-xs text-slate-500">Dicatat manual karena keputusan customer datang dari luar sistem (email/meeting).</p>
                <textarea wire:model="customerDecisionNote" rows="3" placeholder="Catatan (opsional)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                <div class="mt-4 flex justify-end gap-2">
                    <button wire:click="$set('showCustomerDecisionModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                    <button wire:click="recordCustomerDecision(false)" class="rounded-lg border border-red-200 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50">Customer Menolak</button>
                    <button wire:click="recordCustomerDecision(true)" class="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">Customer Menerima</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showPoModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showPoModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-slate-800">Catat PO Customer</h3>
                <form wire:submit="savePo" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nomor PO Customer</label>
                        <input type="text" wire:model="poNumber" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('poNumber') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal PO</label>
                            <input type="date" wire:model="poDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Nilai PO (Rp)</label>
                            <input type="number" step="0.01" wire:model="poValue" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('poValue') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal Mulai Pekerjaan</label>
                            <input type="date" wire:model="poStartDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal Selesai</label>
                            <input type="date" wire:model="poEndDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('poEndDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showPoModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                            <x-icon name="check" class="h-4 w-4" /> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showItemModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showItemModal', false)">
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-slate-800">Tambah Item Penawaran</h3>
                @if ($quotation->releaseOrder->items->isNotEmpty())
                    <div class="mb-3">
                        <label class="mb-1 block text-xs font-medium text-slate-500">Salin dari item RO (opsional)</label>
                        <select onchange="if(this.value) @this.fillFromRoItem(this.value)" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- pilih item RO --</option>
                            @foreach ($quotation->releaseOrder->items as $roItem)
                                <option value="{{ $roItem->id }}">{{ $roItem->description }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
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
                        <label class="mb-1 block text-sm font-medium text-slate-700">Referensi Harga Vendor (opsional)</label>
                        <input type="text" wire:model="vendorReferenceNote" placeholder="mis. RFQ-2026-0007 / PT Vendor A" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Harga Jual Satuan (Rp)</label>
                        <input type="number" step="0.01" wire:model="unitPrice" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('unitPrice') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
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
</div>
