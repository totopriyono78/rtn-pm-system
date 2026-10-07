<div class="space-y-6">
    <div class="flex items-center justify-between">
        <x-page-header icon="user-plus" color="violet" title="Prospect / Pipeline" subtitle="CRM & Sales Pipeline: prospek baru hingga deal (won) atau batal (lost)." />
        <button wire:click="openCreate" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-500">
            <x-icon name="plus" class="h-4 w-4" /> Tambah Prospect
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
                <input wire:model.live.debounce.400ms="search" type="text" placeholder="Cari nama perusahaan / kontak / kode..."
                    class="w-full rounded-lg border border-slate-300 py-2 pl-9 pr-3 text-sm">
            </div>
            <select wire:model.live="statusFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Tahap</option>
                @foreach (\App\Models\Prospect::STATUSES as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">Kode</th>
                        <th class="pb-2">Perusahaan</th>
                        <th class="pb-2">Kontak</th>
                        <th class="pb-2">Tahap</th>
                        <th class="pb-2 text-right">Estimasi Nilai</th>
                        <th class="pb-2">PIC Marketing</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($prospects as $prospect)
                        @php
                            $stageColor = match ($prospect->status) {
                                'won' => 'bg-emerald-50 text-emerald-700',
                                'lost' => 'bg-red-50 text-red-600',
                                'negosiasi' => 'bg-amber-50 text-amber-700',
                                'proposal' => 'bg-sky-50 text-sky-700',
                                'qualified' => 'bg-violet-50 text-violet-700',
                                default => 'bg-slate-100 text-slate-600',
                            };
                        @endphp
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2 font-medium">
                                <a href="{{ route('sales.prospects.show', $prospect) }}" class="text-indigo-600 hover:underline">{{ $prospect->code }}</a>
                            </td>
                            <td class="py-2">{{ $prospect->company_name }}</td>
                            <td class="py-2 text-slate-500">
                                {{ $prospect->contact_name ?? '-' }}
                                @if ($prospect->contact_phone)
                                    <div class="text-xs text-slate-400">{{ $prospect->contact_phone }}</div>
                                @endif
                            </td>
                            <td class="py-2"><span class="rounded-full {{ $stageColor }} px-2 py-0.5 text-xs">{{ \App\Models\Prospect::STATUSES[$prospect->status] }}</span></td>
                            <td class="py-2 text-right">{{ $prospect->estimated_value ? 'Rp '.number_format((float) $prospect->estimated_value, 0, ',', '.') : '-' }}</td>
                            <td class="py-2 text-slate-500">{{ $prospect->assignee->name ?? '-' }}</td>
                            <td class="py-2 text-right">
                                <a href="{{ route('sales.prospects.show', $prospect) }}" class="text-xs font-semibold text-indigo-600 hover:underline">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-empty-state icon="user-plus" title="Belum ada Prospect." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $prospects->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showModal', false)">
            <div class="max-h-[90vh] w-full max-w-xl overflow-y-auto rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="user-plus" class="h-5 w-5 text-indigo-500" /> Tambah Prospect
                </h3>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama Perusahaan</label>
                        <input type="text" wire:model="companyName" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('companyName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Nama Kontak (PIC)</label>
                            <input type="text" wire:model="contactName" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">No. Telepon</label>
                            <input type="text" wire:model="contactPhone" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                            <input type="email" wire:model="contactEmail" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('contactEmail') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Sumber Lead</label>
                            <input type="text" wire:model="source" placeholder="Referral, Website, Pameran, dsb." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Alamat</label>
                        <textarea wire:model="address" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Estimasi Nilai (Rp)</label>
                            <input type="number" step="0.01" wire:model="estimatedValue" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">PIC Marketing</label>
                            <select wire:model="assignedTo" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">-- belum ditentukan --</option>
                                @foreach ($marketingUsers as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
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
