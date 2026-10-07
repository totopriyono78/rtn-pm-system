<div class="space-y-6">
    <div class="flex items-center justify-between">
        <x-page-header icon="doc-text" color="sky" title="Contract" subtitle="Kontrak spesifik (nilai pasti) maupun kontrak payung (plafon maksimal) dengan customer." />
        @if ($canManage)
            <button wire:click="openCreate" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-500">
                <x-icon name="plus" class="h-4 w-4" /> Buat Contract
            </button>
        @endif
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <div class="relative max-w-sm flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <x-icon name="search" class="h-4 w-4" />
                </span>
                <input wire:model.live.debounce.400ms="search" type="text" placeholder="Cari nomor kontrak / customer..."
                    class="w-full rounded-lg border border-slate-300 py-2 pl-9 pr-3 text-sm">
            </div>
            <select wire:model.live="typeFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Tipe</option>
                @foreach (\App\Models\Contract::TYPES as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">No. Kontrak</th>
                        <th class="pb-2">Customer</th>
                        <th class="pb-2">Tipe</th>
                        <th class="pb-2">Jangka Waktu</th>
                        <th class="pb-2 text-right">Nilai</th>
                        <th class="pb-2">Status</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($contracts as $contract)
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2 font-medium">
                                <a href="{{ route('contracts.show', $contract) }}" class="text-indigo-600 hover:underline">{{ $contract->contract_number }}</a>
                            </td>
                            <td class="py-2">{{ $contract->customer->name }}</td>
                            <td class="py-2">
                                <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs">{{ \App\Models\Contract::TYPES[$contract->contract_type] }}</span>
                            </td>
                            <td class="py-2 text-slate-500">
                                {{ optional($contract->start_date)->format('d/m/Y') ?? '-' }} &ndash; {{ optional($contract->end_date)->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="py-2 text-right">
                                @if ($contract->contract_type === 'spesifik')
                                    Rp {{ number_format((float) $contract->fixed_value, 0, ',', '.') }}
                                @else
                                    <span title="Plafon maksimal">Rp {{ number_format((float) $contract->max_value, 0, ',', '.') }}</span>
                                @endif
                            </td>
                            <td class="py-2"><span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs">{{ \App\Models\Contract::STATUSES[$contract->status] }}</span></td>
                            <td class="py-2 text-right text-slate-400">{{ $contract->release_orders_count }} RO</td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-empty-state icon="doc-text" title="Belum ada contract." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $contracts->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showModal', false)">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="plus-circle" class="h-5 w-5 text-indigo-500" /> Buat Contract
                </h3>
                <form wire:submit="save" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
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
                            <label class="mb-1 block text-sm font-medium text-slate-700">Unit Penanggung Jawab</label>
                            <select wire:model="unitId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">-- pilih unit --</option>
                                @foreach ($units as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->region->name ?? '-' }})</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-[11px] text-slate-400">Menentukan region scoping untuk proyek yang lahir dari kontrak ini.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Nomor Kontrak</label>
                            <input type="text" wire:model="contractNumber" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('contractNumber') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal Kontrak</label>
                            <input type="date" wire:model="contractDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('contractDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Tipe Kontrak</label>
                        <div class="flex gap-4">
                            <label class="flex items-center gap-2 text-sm">
                                <input type="radio" value="spesifik" wire:model.live="contractType"> Spesifik (nilai pasti, 1:1 ke Project)
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="radio" value="payung" wire:model.live="contractType"> Payung (plafon maksimal)
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal Mulai</label>
                            <input type="date" wire:model="startDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal Selesai</label>
                            <input type="date" wire:model="endDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('endDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    @if ($contractType === 'spesifik')
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Nilai Kontrak (Rp) — sudah pasti</label>
                            <input type="number" step="0.01" wire:model="fixedValue" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('fixedValue') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    @else
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Nilai Plafon Maksimal (Rp)</label>
                            <input type="number" step="0.01" wire:model="maxValue" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('maxValue') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            <p class="mt-1 text-[11px] text-slate-400">Nilai riil per pekerjaan ditentukan belakangan lewat Release Order &rarr; Penawaran &rarr; PO Customer.</p>
                        </div>
                    @endif

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Ruang Lingkup</label>
                        <textarea wire:model="scopeDescription" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Site / Lokasi Tercakup</label>
                        <div class="max-h-32 space-y-1.5 overflow-y-auto rounded-lg border border-slate-200 p-3">
                            @forelse ($sites as $site)
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" value="{{ $site->id }}" wire:model="siteIds"> {{ $site->name }}
                                </label>
                            @empty
                                <p class="text-xs text-slate-400">Belum ada Site terdaftar. Tambahkan di menu Region &amp; Unit.</p>
                            @endforelse
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 flex items-center gap-1.5 text-sm font-medium text-slate-700">
                            <x-icon name="doc-text" class="h-4 w-4 text-slate-400" /> Dokumen Kontrak (opsional)
                        </label>
                        <input type="file" wire:model="document"
                            class="block w-full cursor-pointer rounded-lg border border-slate-300 p-2 text-sm text-slate-500 file:mr-4 file:cursor-pointer file:rounded-lg file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-500">
                        @error('document') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
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
