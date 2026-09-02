<div class="space-y-6">
    <div>
        <h2 class="text-xl font-semibold text-slate-800">Purchase Order</h2>
        <p class="text-sm text-slate-500">PO diterbitkan otomatis per vendor saat RFQ disetujui Direktur.</p>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap gap-3">
            <select wire:model.live="vendorFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Vendor</option>
                @foreach ($vendors as $v)
                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="statusFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                @foreach (\App\Models\PurchaseOrder::STATUSES as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <table class="w-full text-left text-sm">
            <thead class="text-xs uppercase text-slate-400">
                <tr>
                    <th class="pb-2">No. PO</th>
                    <th class="pb-2">Vendor</th>
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
                @forelse ($orders as $po)
                    <tr class="border-t border-slate-100">
                        <td class="py-2 font-medium">{{ $po->code }}</td>
                        <td class="py-2">
                            <a href="{{ route('purchasing.vendors.show', $po->vendor) }}" class="text-indigo-600 hover:underline">{{ $po->vendor->name }}</a>
                        </td>
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
                    <tr><td colspan="7" class="py-4 text-center text-slate-400">Belum ada Purchase Order.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $orders->links() }}</div>
    </div>
</div>
