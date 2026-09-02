<div class="space-y-6">
    <div>
        <h2 class="text-xl font-semibold text-slate-800">Material Tracking</h2>
        <p class="text-sm text-slate-500">Pantau progress pengadaan material per proyek secara real-time.</p>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <select wire:model.live="projectFilter" class="mb-4 rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <option value="">Semua Proyek</option>
            @foreach ($projects as $p)
                <option value="{{ $p->id }}">{{ $p->name }}</option>
            @endforeach
        </select>

        <table class="w-full text-left text-sm">
            <thead class="text-xs uppercase text-slate-400">
                <tr>
                    <th class="pb-2">Proyek</th>
                    <th class="pb-2">Item</th>
                    <th class="pb-2">Qty</th>
                    <th class="pb-2">Status</th>
                    <th class="pb-2">Update Terakhir</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($trackings as $t)
                    <tr class="border-t border-slate-100">
                        <td class="py-2">{{ $t->project->name }}</td>
                        <td class="py-2 font-medium">{{ $t->item->name }}</td>
                        <td class="py-2">{{ rtrim(rtrim(number_format($t->qty, 2), '0'), '.') }}</td>
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
                        <td class="py-2 text-slate-500">{{ $t->updatedBy->name ?? '-' }} &middot; {{ $t->updated_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-4 text-center text-slate-400">Belum ada data material tracking.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
