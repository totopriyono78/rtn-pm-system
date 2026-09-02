<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('purchasing.vendors') }}" class="text-sm text-indigo-600 hover:underline">&larr; Kembali ke Daftar Vendor</a>
            <h2 class="mt-1 text-xl font-semibold text-slate-800">{{ $vendor->name }}</h2>
            <p class="text-sm text-slate-500">{{ $vendor->code }}</p>
        </div>
        @if ($vendor->is_active)
            <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-700">Aktif</span>
        @else
            <span class="rounded-full bg-slate-200 px-3 py-1 text-xs font-medium text-slate-600">Nonaktif</span>
        @endif
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <div class="text-xs uppercase text-slate-400">Total Penawaran Vendor</div>
            <div class="mt-1 text-lg font-semibold">{{ $vendor->vendorQuotations->count() }}</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <div class="text-xs uppercase text-slate-400">PO Aktif</div>
            <div class="mt-1 text-lg font-semibold">{{ $activePoCount }}</div>
        </div>
        @if ($canViewHarga)
            <div class="rounded-xl bg-white p-4 shadow-sm">
                <div class="text-xs uppercase text-slate-400">Total Nilai PO</div>
                <div class="mt-1 text-lg font-semibold">Rp {{ number_format($totalPoValue, 0, ',', '.') }}</div>
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
        <h3 class="mb-4 text-sm font-semibold text-slate-700">Riwayat Purchase Order</h3>
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
                    <tr class="border-t border-slate-100">
                        <td class="py-2 font-medium">{{ $po->code }}</td>
                        <td class="py-2">{{ $po->project->name }}</td>
                        @if ($canViewHarga)
                            <td class="py-2 text-right">Rp {{ number_format($po->total, 0, ',', '.') }}</td>
                        @endif
                        <td class="py-2"><span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs">{{ \App\Models\PurchaseOrder::STATUSES[$po->status] }}</span></td>
                        <td class="py-2 text-slate-500">{{ $po->created_at->format('d M Y') }}</td>
                        <td class="py-2 text-right">
                            <a href="{{ route('purchasing.po.print', $po) }}" target="_blank" class="text-indigo-600 hover:underline">Cetak</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-4 text-center text-slate-400">Belum ada Purchase Order untuk vendor ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <h3 class="mb-4 text-sm font-semibold text-slate-700">Riwayat Penawaran (RFQ diikuti)</h3>
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
                    <tr class="border-t border-slate-100">
                        <td class="py-2 font-medium">{{ $vq->rfq->code }}</td>
                        <td class="py-2">{{ $vq->rfq->project->name }}</td>
                        <td class="py-2 text-slate-500">{{ $vq->reference_number ?: '-' }}</td>
                        @if ($canViewHarga)
                            <td class="py-2 text-right">Rp {{ number_format($vq->items->sum('subtotal'), 0, ',', '.') }}</td>
                        @endif
                        <td class="py-2 text-right">
                            <a href="{{ route('purchasing.rfq.show', $vq->rfq) }}" class="text-indigo-600 hover:underline">Lihat RFQ</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-4 text-center text-slate-400">Vendor ini belum pernah mengajukan penawaran.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
