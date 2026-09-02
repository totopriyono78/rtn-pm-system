<div class="space-y-6">
    <div class="flex items-center justify-between">
        <x-page-header icon="doc-text" color="violet" title="Request for Quotation" subtitle="Susun kebutuhan material/jasa per proyek, undang beberapa vendor untuk menawar, lalu bandingkan hasilnya." />
        @if ($canManage)
            <button wire:click="openCreate" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-500">
                <x-icon name="plus" class="h-4 w-4" /> Buat RFQ
            </button>
        @endif
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex items-center gap-2">
            <x-icon name="filter" class="h-4 w-4 text-slate-400" />
            <select wire:model.live="statusFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                @foreach (\App\Models\RequestForQuotation::STATUSES as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">Kode</th>
                        <th class="pb-2">Proyek</th>
                        <th class="pb-2">Dibuat oleh</th>
                        <th class="pb-2 text-right">Jumlah Item</th>
                        <th class="pb-2">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rfqs as $rfq)
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2 font-medium">
                                <a href="{{ route('purchasing.rfq.show', $rfq) }}" class="text-indigo-600 hover:underline">{{ $rfq->code }}</a>
                            </td>
                            <td class="py-2">{{ $rfq->project->name }}</td>
                            <td class="py-2 text-slate-500">{{ $rfq->creator->name ?? '-' }}</td>
                            <td class="py-2 text-right">{{ $rfq->items_count }}</td>
                            <td class="py-2"><span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs">{{ \App\Models\RequestForQuotation::STATUSES[$rfq->status] }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty-state icon="doc-text" title="Belum ada RFQ." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $rfqs->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showModal', false)">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-1 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="plus-circle" class="h-5 w-5 text-indigo-500" /> Buat Request for Quotation
                </h3>
                <p class="mb-4 text-xs text-slate-500">Susun daftar material/jasa yang dibutuhkan. Harga belum diisi di sini — harga akan masuk lewat penawaran tiap vendor pada halaman detail RFQ.</p>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Proyek</label>
                        <select wire:model="projectId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- pilih proyek --</option>
                            @foreach ($projects as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                        @error('projectId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">Material / Jasa Dibutuhkan</label>
                        <div class="space-y-2">
                            @foreach ($lines as $i => $line)
                                <div class="grid grid-cols-12 items-center gap-2">
                                    <div class="col-span-8">
                                        <select wire:model="lines.{{ $i }}.item_id" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                            <option value="">-- pilih item --</option>
                                            @foreach ($items as $item)
                                                <option value="{{ $item->id }}">{{ $item->code }} - {{ $item->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-span-2">
                                        <input type="number" step="0.01" min="0" wire:model="lines.{{ $i }}.qty" placeholder="Qty" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                    </div>
                                    <div class="col-span-2 text-right">
                                        <button type="button" wire:click="removeLine({{ $i }})" class="inline-flex items-center gap-1 rounded-lg px-1.5 py-1 text-xs font-medium text-red-600 transition-colors hover:bg-red-50">
                                            <x-icon name="trash" class="h-3.5 w-3.5" /> Hapus
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('lines') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        <button type="button" wire:click="addLine" class="mt-2 inline-flex items-center gap-1 text-sm text-indigo-600 hover:underline">
                            <x-icon name="plus" class="h-3.5 w-3.5" /> Tambah baris item
                        </button>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Catatan</label>
                        <textarea wire:model="notes" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                            <x-icon name="check" class="h-4 w-4" /> Simpan sebagai Draft
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
