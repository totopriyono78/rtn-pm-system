<div class="space-y-6">
    <div class="flex items-center justify-between">
        <x-page-header icon="doc-text" color="indigo" title="Jurnal Umum (Entry Voucher)" subtitle="Pencatatan debit/kredit manual. Draft bisa diubah bebas, posting bersifat final -- koreksi lewat jurnal pembalik baru." />
        @if ($canManage)
            <button wire:click="openCreate" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-500">
                <x-icon name="plus" class="h-4 w-4" /> Buat Jurnal
            </button>
        @endif
    </div>

    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 px-4 py-2.5 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <select wire:model.live="statusFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                @foreach (\App\Models\JournalEntry::STATUSES as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="sourceFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Sumber</option>
                @foreach (\App\Models\JournalEntry::SOURCES as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">No. Jurnal</th>
                        <th class="pb-2">Tanggal</th>
                        <th class="pb-2">Deskripsi</th>
                        <th class="pb-2 text-right">Debit</th>
                        <th class="pb-2 text-right">Kredit</th>
                        <th class="pb-2">Status</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($entries as $entry)
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2 font-medium">{{ $entry->entry_number }}</td>
                            <td class="py-2 text-slate-500">{{ $entry->entry_date->format('d/m/Y') }}</td>
                            <td class="py-2 text-slate-500">{{ $entry->description ?: '-' }}
                                @if ($entry->reference)
                                    <span class="block text-xs text-slate-400">Ref: {{ $entry->reference }}</span>
                                @endif
                                @if ($entry->source === 'otomatis')
                                    <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-sky-100 px-2 py-0.5 text-xs font-medium text-sky-700">
                                        <x-icon name="refresh" class="h-3 w-3" /> Otomatis
                                        @if ($entry->invoice)
                                            -- Invoice {{ $entry->invoice->invoice_number }}
                                        @elseif ($entry->purchaseOrder)
                                            -- PO {{ $entry->purchaseOrder->code }} ({{ $entry->purchaseOrder->vendor->name }})
                                        @elseif ($entry->cashAdvance)
                                            -- Kasbon {{ $entry->cashAdvance->requester->name }}
                                        @elseif ($entry->payrollRun)
                                            -- Payroll {{ $entry->payrollRun->period_label }}
                                        @endif
                                    </span>
                                @endif
                            </td>
                            <td class="py-2 text-right">Rp {{ number_format($entry->total_debit, 0, ',', '.') }}</td>
                            <td class="py-2 text-right">Rp {{ number_format($entry->total_credit, 0, ',', '.') }}</td>
                            <td class="py-2">
                                @php
                                    $statusColor = match ($entry->status) {
                                        'posted' => 'bg-emerald-100 text-emerald-700',
                                        'dibatalkan' => 'bg-rose-100 text-rose-700',
                                        default => 'bg-amber-100 text-amber-700',
                                    };
                                @endphp
                                <span class="rounded-full {{ $statusColor }} px-2 py-0.5 text-xs">{{ \App\Models\JournalEntry::STATUSES[$entry->status] }}</span>
                            </td>
                            <td class="py-2 text-right">
                                @if ($canManage && $entry->status === 'draft')
                                    <div class="inline-flex items-center gap-1">
                                        <button wire:click="openEdit({{ $entry->id }})" class="rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-50">Ubah</button>
                                        <button wire:click="postEntry({{ $entry->id }})" wire:confirm="Posting jurnal ini? Setelah posting, jurnal tidak bisa diubah lagi." class="rounded-lg px-2 py-1 text-xs font-medium text-emerald-600 hover:bg-emerald-50">Posting</button>
                                        <button wire:click="cancelEntry({{ $entry->id }})" wire:confirm="Batalkan jurnal draft ini?" class="rounded-lg px-2 py-1 text-xs font-medium text-rose-600 hover:bg-rose-50">Batalkan</button>
                                    </div>
                                @elseif ($entry->source === 'otomatis')
                                    <span class="text-xs text-slate-400">Dibuat sistem</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-empty-state icon="doc-text" title="Belum ada jurnal." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $entries->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showModal', false)">
            <div class="w-full max-w-3xl rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="doc-text" class="h-5 w-5 text-indigo-500" /> {{ $editingId ? 'Ubah Jurnal (Draft)' : 'Buat Jurnal Baru' }}
                </h3>
                <form wire:submit="save" class="space-y-4">
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal</label>
                            <input type="date" wire:model="entryDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('entryDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Referensi (opsional)</label>
                            <input type="text" wire:model="reference" placeholder="mis. no. bukti" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Deskripsi</label>
                            <input type="text" wire:model="description" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <label class="block text-sm font-medium text-slate-700">Baris Jurnal</label>
                            <button type="button" wire:click="addLine" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-50">
                                <x-icon name="plus" class="h-3.5 w-3.5" /> Tambah Baris
                            </button>
                        </div>
                        <div class="space-y-2">
                            @foreach ($lines as $i => $line)
                                <div class="grid grid-cols-12 gap-2 rounded-lg bg-slate-50 p-2">
                                    <div class="col-span-4">
                                        <select wire:model="lines.{{ $i }}.chart_of_account_id" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                            <option value="">-- akun --</option>
                                            @foreach ($accounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->code }} -- {{ $account->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-span-3">
                                        <input type="number" step="0.01" wire:model="lines.{{ $i }}.debit" placeholder="Debit" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                    </div>
                                    <div class="col-span-3">
                                        <input type="number" step="0.01" wire:model="lines.{{ $i }}.credit" placeholder="Kredit" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                    </div>
                                    <div class="col-span-1">
                                        <input type="text" wire:model="lines.{{ $i }}.description" placeholder="Ket." class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                    </div>
                                    <div class="col-span-1 flex items-center justify-end">
                                        <button type="button" wire:click="removeLine({{ $i }})" class="text-rose-500 hover:text-rose-700">
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </div>
                                    @error("lines.{$i}.chart_of_account_id") <div class="col-span-12 text-xs text-red-600">{{ $message }}</div> @enderror
                                    @error("lines.{$i}.debit") <div class="col-span-12 text-xs text-red-600">{{ $message }}</div> @enderror
                                </div>
                            @endforeach
                        </div>
                        @error('lines') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end gap-6 border-t border-slate-200 pt-3 text-sm">
                        <span>Total Debit: <span class="font-semibold">Rp {{ number_format($this->totalDebit, 0, ',', '.') }}</span></span>
                        <span>Total Kredit: <span class="font-semibold">Rp {{ number_format($this->totalCredit, 0, ',', '.') }}</span></span>
                        @if (abs($this->totalDebit - $this->totalCredit) < 0.005 && $this->totalDebit > 0)
                            <span class="font-semibold text-emerald-600">Balance &check;</span>
                        @else
                            <span class="font-semibold text-rose-600">Belum Balance</span>
                        @endif
                    </div>

                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                            <x-icon name="check" class="h-4 w-4" /> Simpan Draft
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
