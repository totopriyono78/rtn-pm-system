<div class="space-y-6">
    <div class="flex items-center justify-between">
        <x-page-header icon="clipboard-list" color="emerald" title="Sales Order" subtitle="Order pompa baru dari Customer -- sekali dikonfirmasi otomatis membentuk Contract &amp; Project." />
        <button wire:click="openCreate" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-500">
            <x-icon name="plus" class="h-4 w-4" /> Buat Sales Order
        </button>
    </div>

    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <div class="relative max-w-sm flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <x-icon name="search" class="h-4 w-4" />
                </span>
                <input wire:model.live.debounce.400ms="search" type="text" placeholder="Cari no. SO / customer..."
                    class="w-full rounded-lg border border-slate-300 py-2 pl-9 pr-3 text-sm">
            </div>
            <select wire:model.live="statusFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                @foreach (\App\Models\SalesOrder::STATUSES as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">No. SO</th>
                        <th class="pb-2">Customer</th>
                        <th class="pb-2">Tanggal</th>
                        <th class="pb-2">Item</th>
                        <th class="pb-2 text-right">Nilai</th>
                        <th class="pb-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($salesOrders as $so)
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2 font-medium">
                                <a href="{{ route('sales.orders.show', $so) }}" class="text-indigo-600 hover:underline">{{ $so->so_number }}</a>
                            </td>
                            <td class="py-2">{{ $so->customer->name }}</td>
                            <td class="py-2 text-slate-500">{{ $so->so_date->format('d/m/Y') }}</td>
                            <td class="py-2 text-slate-400">{{ $so->items_count }} item</td>
                            <td class="py-2 text-right">Rp {{ number_format((float) $so->total_value, 0, ',', '.') }}</td>
                            <td class="py-2">
                                @php
                                    $soColor = match ($so->status) {
                                        'confirmed' => 'bg-emerald-50 text-emerald-700',
                                        'cancelled' => 'bg-red-50 text-red-600',
                                        default => 'bg-slate-100 text-slate-600',
                                    };
                                @endphp
                                <span class="rounded-full {{ $soColor }} px-2 py-0.5 text-xs">{{ \App\Models\SalesOrder::STATUSES[$so->status] }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state icon="clipboard-list" title="Belum ada Sales Order." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $salesOrders->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showModal', false)">
            <div class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="plus-circle" class="h-5 w-5 text-indigo-500" /> Buat Sales Order
                </h3>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Dari Prospect yang Won (opsional)</label>
                        <select wire:model.live="prospectId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- tidak terkait prospect --</option>
                            @foreach ($wonProspects as $p)
                                <option value="{{ $p->id }}">{{ $p->code }} &middot; {{ $p->company_name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-[11px] text-slate-400">Memilih prospect otomatis mengisi Customer &amp; deskripsi di bawah.</p>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Customer</label>
                        <select wire:model="customerId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- pilih customer --</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('customerId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal Sales Order</label>
                        <input type="date" wire:model="soDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('soDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Deskripsi / Ruang Lingkup Pekerjaan</label>
                        <textarea wire:model="description" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Catatan</label>
                        <textarea wire:model="notes" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
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
