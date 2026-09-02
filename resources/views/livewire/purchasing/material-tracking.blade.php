<div class="space-y-6">
    <x-page-header icon="package" color="violet" title="Material Tracking" subtitle="Daftar barang yang dipesan per proyek — nomor PO, tanggal pesan, dan status penerimaannya." />

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex items-center gap-2">
            <x-icon name="filter" class="h-4 w-4 text-slate-400" />
            <select wire:model.live="projectFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Proyek</option>
                @foreach ($projects as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>
        </div>

        @forelse ($trackingsByProject as $group)
            <div class="mb-5 rounded-lg border border-slate-100 last:mb-0">
                <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 bg-slate-50/60 px-4 py-2.5">
                    <a href="{{ route('projects.show', $group['project']) }}" class="flex items-center gap-2 text-sm font-semibold text-slate-800 hover:text-indigo-600 hover:underline">
                        <x-icon name="briefcase" class="h-4 w-4 text-slate-400" /> {{ $group['project']->name }}
                    </a>
                    <span class="rounded-full bg-white px-2.5 py-0.5 text-xs font-medium text-slate-500 ring-1 ring-slate-200">
                        {{ $group['receivedCount'] }}/{{ $group['items']->count() }} item sudah diterima
                    </span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs uppercase text-slate-400">
                            <tr>
                                <th class="px-4 pb-2 pt-3">Item</th>
                                <th class="pb-2 pt-3">Qty</th>
                                <th class="pb-2 pt-3">No. PO</th>
                                <th class="pb-2 pt-3">Tanggal Pesan</th>
                                <th class="pb-2 pt-3">Status</th>
                                <th class="pb-2 pt-3">Diterima?</th>
                                <th class="pb-2 pr-4 pt-3">Update Terakhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($group['items'] as $t)
                                @php $po = $t->purchaseOrderItem?->purchaseOrder; @endphp
                                <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                                    <td class="px-4 py-2 font-medium">{{ $t->item->name }}</td>
                                    <td class="py-2">{{ rtrim(rtrim(number_format($t->qty, 2), '0'), '.') }}</td>
                                    <td class="py-2 text-slate-500">
                                        @if ($po)
                                            <a href="{{ route('purchasing.po.print', $po) }}" target="_blank" class="text-indigo-600 hover:underline">{{ $po->code }}</a>
                                        @else
                                            <span class="text-slate-300">-</span>
                                        @endif
                                    </td>
                                    <td class="py-2 text-slate-500">{{ $po?->created_at?->format('d M Y') ?? '-' }}</td>
                                    <td class="py-2">
                                        @if ($canManage)
                                            <select wire:change="updateStatus({{ $t->id }}, $event.target.value)" class="rounded-lg border border-slate-300 px-2 py-1 text-xs">
                                                @foreach (\App\Models\MaterialTracking::STATUSES as $key => $label)
                                                    <option value="{{ $key }}" @selected($t->status === $key)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        @else
                                            <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs">{{ \App\Models\MaterialTracking::STATUSES[$t->status] }}</span>
                                        @endif
                                    </td>
                                    <td class="py-2">
                                        @if (in_array($t->status, ['arrived', 'installed']))
                                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs text-emerald-700">
                                                <x-icon name="check" class="h-3 w-3" /> Sudah Diterima
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-0.5 text-xs text-amber-700">
                                                <x-icon name="clock" class="h-3 w-3" /> Belum Diterima
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2 pr-4 text-slate-500">{{ $t->updatedBy->name ?? '-' }} &middot; {{ $t->updated_at->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <x-empty-state icon="package" title="Belum ada data material tracking." />
        @endforelse
    </div>
</div>
