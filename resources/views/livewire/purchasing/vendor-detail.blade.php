<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('purchasing.vendors') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:underline">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke Daftar Vendor
            </a>
            <div class="mt-2">
                <x-page-header icon="store" color="amber" :title="$vendor->name" :subtitle="$vendor->code" />
            </div>
        </div>
        @if ($vendor->is_active)
            <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">
                <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span> Aktif
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 rounded-full bg-slate-200 px-3 py-1 text-xs font-medium text-slate-600">
                <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span> Nonaktif
            </span>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="flex items-start gap-3 rounded-xl bg-white p-4 shadow-sm">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-indigo-50 text-indigo-600">
                <x-icon name="doc-text" class="h-4 w-4" />
            </div>
            <div>
                <div class="text-xs uppercase text-slate-400">Total Penawaran Vendor</div>
                <div class="mt-0.5 text-lg font-semibold">{{ $vendor->vendorQuotations->count() }}</div>
            </div>
        </div>
        <div class="flex items-start gap-3 rounded-xl bg-white p-4 shadow-sm">
            <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-emerald-50 text-emerald-600">
                <x-icon name="truck" class="h-4 w-4" />
            </div>
            <div>
                <div class="text-xs uppercase text-slate-400">PO Aktif</div>
                <div class="mt-0.5 text-lg font-semibold">{{ $activePoCount }}</div>
            </div>
        </div>
        @if ($canViewHarga)
            <div class="flex items-start gap-3 rounded-xl bg-white p-4 shadow-sm">
                <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-50 text-amber-600">
                    <x-icon name="wallet" class="h-4 w-4" />
                </div>
                <div>
                    <div class="text-xs uppercase text-slate-400">Total Nilai PO</div>
                    <div class="mt-0.5 text-lg font-semibold">Rp {{ number_format($totalPoValue, 0, ',', '.') }}</div>
                </div>
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="text-xs uppercase text-slate-400">Contact Person</div>
            <div class="mt-1 text-sm text-slate-700">{{ $vendor->contact_person ?: '-' }}</div>
            <div class="mt-3 text-xs uppercase text-slate-400">Telepon / Email</div>
            <div class="mt-1 text-sm text-slate-700">{{ $vendor->phone ?: '-' }} &middot; {{ $vendor->email ?: '-' }}</div>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="text-xs uppercase text-slate-400">Alamat</div>
            <div class="mt-1 text-sm text-slate-700">{{ $vendor->address ?: '-' }}</div>
            <div class="mt-3 text-xs uppercase text-slate-400">NPWP</div>
            <div class="mt-1 text-sm text-slate-700">{{ $vendor->npwp ?: '-' }}</div>
        </div>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-slate-700">
            <x-icon name="truck" class="h-4 w-4 text-slate-400" /> Riwayat Purchase Order
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">No. PO</th>
                        <th class="pb-2">Proyek</th>
                        @if ($canViewHarga)
                            <th class="pb-2 text-right">Total</th>
                        @endif
                        <th class="pb-2">Status</th>
                        <th class="pb-2">Tanggal</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vendor->purchaseOrders as $po)
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2 font-medium">{{ $po->code }}</td>
                            <td class="py-2">{{ $po->project->name }}</td>
                            @if ($canViewHarga)
                                <td class="py-2 text-right">Rp {{ number_format($po->total, 0, ',', '.') }}</td>
                            @endif
                            <td class="py-2"><span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs">{{ \App\Models\PurchaseOrder::STATUSES[$po->status] }}</span></td>
                            <td class="py-2 text-slate-500">{{ $po->created_at->format('d M Y') }}</td>
                            <td class="py-2 text-right">
                                <a href="{{ route('purchasing.po.print', $po) }}" target="_blank" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 transition-colors hover:bg-indigo-50">
                                    <x-icon name="printer" class="h-3.5 w-3.5" /> Cetak
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state icon="truck" title="Belum ada Purchase Order untuk vendor ini." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <h3 class="mb-4 flex items-center gap-2 text-sm font-semibold text-slate-700">
            <x-icon name="doc-text" class="h-4 w-4 text-slate-400" /> Riwayat Penawaran (RFQ diikuti)
        </h3>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">RFQ</th>
                        <th class="pb-2">Proyek</th>
                        <th class="pb-2">No. Referensi Vendor</th>
                        @if ($canViewHarga)
                            <th class="pb-2 text-right">Total Ditawar</th>
                        @endif
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vendor->vendorQuotations as $vq)
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2 font-medium">{{ $vq->rfq->code }}</td>
                            <td class="py-2">{{ $vq->rfq->project->name }}</td>
                            <td class="py-2 text-slate-500">{{ $vq->reference_number ?: '-' }}</td>
                            @if ($canViewHarga)
                                <td class="py-2 text-right">Rp {{ number_format($vq->items->sum('subtotal'), 0, ',', '.') }}</td>
                            @endif
                            <td class="py-2 text-right">
                                <a href="{{ route('purchasing.rfq.show', $vq->rfq) }}" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 transition-colors hover:bg-indigo-50">
                                    <x-icon name="arrow-right" class="h-3.5 w-3.5" /> Lihat RFQ
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty-state icon="doc-text" title="Vendor ini belum pernah mengajukan penawaran." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
