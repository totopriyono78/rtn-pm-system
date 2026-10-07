<div class="space-y-6">
    <x-page-header icon="doc-text" color="sky" :title="$contract->contract_number" :subtitle="$contract->customer->name.' · '.\App\Models\Contract::TYPES[$contract->contract_type]" />

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded-xl bg-white p-5 shadow-sm md:col-span-2">
            <h3 class="mb-3 text-sm font-semibold text-slate-700">Informasi Kontrak</h3>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-xs text-slate-400">Tanggal Kontrak</dt><dd>{{ $contract->contract_date->format('d M Y') }}</dd></div>
                <div><dt class="text-xs text-slate-400">Status</dt><dd><span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs">{{ \App\Models\Contract::STATUSES[$contract->status] }}</span></dd></div>
                <div><dt class="text-xs text-slate-400">Tanggal Mulai</dt><dd>{{ optional($contract->start_date)->format('d M Y') ?? '-' }}</dd></div>
                <div><dt class="text-xs text-slate-400">Tanggal Selesai</dt><dd>{{ optional($contract->end_date)->format('d M Y') ?? '-' }}</dd></div>
                <div><dt class="text-xs text-slate-400">Unit Penanggung Jawab</dt><dd>{{ $contract->unit->name ?? '-' }}</dd></div>
                <div>
                    <dt class="text-xs text-slate-400">Dokumen</dt>
                    <dd>
                        @if ($contract->document_path)
                            <button wire:click="downloadDocument({{ $contract->id }})" class="text-indigo-600 hover:underline">Unduh dokumen</button>
                        @else
                            -
                        @endif
                    </dd>
                </div>
            </dl>
            @if ($contract->scope_description)
                <div class="mt-3 border-t border-slate-100 pt-3">
                    <dt class="text-xs text-slate-400">Ruang Lingkup</dt>
                    <dd class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $contract->scope_description }}</dd>
                </div>
            @endif
            @if ($contract->sites->isNotEmpty())
                <div class="mt-3 border-t border-slate-100 pt-3">
                    <dt class="mb-1.5 text-xs text-slate-400">Site / Lokasi</dt>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($contract->sites as $site)
                            <span class="inline-flex items-center gap-1 rounded-full bg-sky-50 px-2 py-0.5 text-xs text-sky-700">
                                <x-icon name="map-pin" class="h-3 w-3" /> {{ $site->name }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm">
            @if ($contract->contract_type === 'spesifik')
                <h3 class="mb-2 text-sm font-semibold text-slate-700">Nilai Kontrak (Pasti)</h3>
                <div class="text-2xl font-semibold text-slate-800">Rp {{ number_format((float) $contract->fixed_value, 0, ',', '.') }}</div>
                <div class="mt-4 border-t border-slate-100 pt-4">
                    @if ($contract->directProject)
                        <p class="mb-2 text-xs text-slate-500">Project sudah dibuat dari kontrak ini.</p>
                        <a href="{{ route('projects.show', $contract->directProject) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-500">
                            <x-icon name="briefcase" class="h-3.5 w-3.5" /> Buka Project
                        </a>
                    @elseif ($canManage)
                        <button wire:click="openCreateProject" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-2 text-xs font-semibold text-white hover:bg-indigo-500">
                            <x-icon name="plus" class="h-3.5 w-3.5" /> Buat Project dari Kontrak Ini
                        </button>
                    @endif
                </div>
            @else
                <h3 class="mb-2 text-sm font-semibold text-slate-700">Plafon Kontrak Payung</h3>
                <div class="text-2xl font-semibold text-slate-800">Rp {{ number_format((float) $contract->max_value, 0, ',', '.') }}</div>
                <dl class="mt-3 space-y-1.5 text-sm">
                    <div class="flex justify-between"><dt class="text-slate-500">Sudah Terpakai</dt><dd class="font-medium">Rp {{ number_format($contract->used_value, 0, ',', '.') }}</dd></div>
                    <div class="flex justify-between"><dt class="text-slate-500">Sisa Plafon</dt><dd class="font-medium {{ $contract->remaining_value < 0 ? 'text-red-600' : 'text-emerald-700' }}">Rp {{ number_format($contract->remaining_value, 0, ',', '.') }}</dd></div>
                </dl>
                @php $usagePct = (float) $contract->max_value > 0 ? min(100, round(($contract->used_value / (float) $contract->max_value) * 100, 1)) : 0; @endphp
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-2 rounded-full {{ $usagePct >= 100 ? 'bg-red-500' : ($usagePct >= 80 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ $usagePct }}%"></div>
                </div>
            @endif
        </div>
    </div>

    @if ($contract->isPayung())
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700">Release Order</h3>
                @if ($canManage)
                    <button wire:click="openCreateRo" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">
                        <x-icon name="plus" class="h-3.5 w-3.5" /> Tambah Release Order
                    </button>
                @endif
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase text-slate-400">
                        <tr>
                            <th class="pb-2">No. RO</th>
                            <th class="pb-2">Tanggal</th>
                            <th class="pb-2">Status</th>
                            <th class="pb-2 text-right">Revisi Penawaran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($contract->releaseOrders as $ro)
                            <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                                <td class="py-2 font-medium">
                                    <a href="{{ route('contracts.release-orders.show', $ro) }}" class="text-indigo-600 hover:underline">{{ $ro->ro_number }}</a>
                                </td>
                                <td class="py-2 text-slate-500">{{ $ro->ro_date->format('d/m/Y') }}</td>
                                <td class="py-2"><span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs">{{ \App\Models\ReleaseOrder::STATUSES[$ro->status] }}</span></td>
                                <td class="py-2 text-right text-slate-400">{{ $ro->quotations->count() }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><x-empty-state icon="doc-text" title="Belum ada Release Order dari customer." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if ($showRoModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showRoModal', false)">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="plus-circle" class="h-5 w-5 text-indigo-500" /> Tambah Release Order
                </h3>
                <form wire:submit="saveRo" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nomor RO (dari Customer)</label>
                        <input type="text" wire:model="roNumber" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('roNumber') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal RO</label>
                        <input type="date" wire:model="roDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('roDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Catatan</label>
                        <textarea wire:model="roNotes" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showRoModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                            <x-icon name="check" class="h-4 w-4" /> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showProjectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showProjectModal', false)">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="plus-circle" class="h-5 w-5 text-indigo-500" /> Buat Project dari Kontrak Spesifik
                </h3>
                <form wire:submit="saveProject" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama Project</label>
                        <input type="text" wire:model="projectName" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('projectName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Project Manager</label>
                        <select wire:model="projectPicUserId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- pilih nanti --</option>
                            @foreach ($pics as $pic)
                                <option value="{{ $pic->id }}">{{ $pic->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal Mulai</label>
                            <input type="date" wire:model="projectStartDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal Selesai</label>
                            <input type="date" wire:model="projectEndDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('projectEndDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <p class="text-xs text-slate-500">Nilai Project otomatis diambil dari nilai kontrak (Rp {{ number_format((float) $contract->fixed_value, 0, ',', '.') }}). Budget breakdown &amp; activity bisa diatur setelah project dibuat.</p>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showProjectModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                            <x-icon name="check" class="h-4 w-4" /> Buat Project
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
