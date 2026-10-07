<div class="space-y-6">
    <div class="flex items-center justify-between">
        <x-page-header icon="wallet" color="emerald" title="Invoice" subtitle="Penagihan ke customer berdasarkan BAPP RO / BAL Bulanan yang sudah disetujui." />
        @if ($canManage)
            <button wire:click="openCreate" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-500">
                <x-icon name="plus" class="h-4 w-4" /> Buat Invoice
            </button>
        @endif
    </div>

    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 px-4 py-2.5 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <div class="relative max-w-sm flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <x-icon name="search" class="h-4 w-4" />
                </span>
                <input wire:model.live.debounce.400ms="search" type="text" placeholder="Cari no. invoice / proyek..."
                    class="w-full rounded-lg border border-slate-300 py-2 pl-9 pr-3 text-sm">
            </div>
            <select wire:model.live="statusFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                @foreach (\App\Models\Invoice::STATUSES as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="projectFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Proyek</option>
                @foreach ($projects as $p)
                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">No. Invoice</th>
                        <th class="pb-2">Proyek / Customer</th>
                        <th class="pb-2">Periode</th>
                        <th class="pb-2">Tgl. Invoice</th>
                        <th class="pb-2">Jatuh Tempo</th>
                        <th class="pb-2 text-right">Total</th>
                        <th class="pb-2">Status</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr @class(['border-t border-slate-100 transition-colors hover:bg-slate-50/70', 'bg-red-50/60' => $invoice->is_overdue])>
                            <td class="py-2 font-medium">{{ $invoice->invoice_number }}</td>
                            <td class="py-2">
                                <div>{{ $invoice->project->name }}</div>
                                <div class="text-xs text-slate-400">{{ $invoice->project->customer?->name ?? '-' }}</div>
                            </td>
                            <td class="py-2 text-slate-500">{{ $invoice->period_label ?: '-' }}</td>
                            <td class="py-2 text-slate-500">{{ $invoice->invoice_date->format('d/m/Y') }}</td>
                            <td class="py-2 text-slate-500">
                                {{ optional($invoice->due_date)->format('d/m/Y') ?? '-' }}
                                @if ($invoice->is_overdue)
                                    <span class="ml-1 rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-medium text-red-700">Terlambat</span>
                                @endif
                            </td>
                            <td class="py-2 text-right font-medium">Rp {{ number_format((float) $invoice->total_amount, 0, ',', '.') }}</td>
                            <td class="py-2">
                                <span @class([
                                    'rounded-full px-2 py-0.5 text-xs',
                                    'bg-slate-100 text-slate-600' => $invoice->status === 'draft',
                                    'bg-amber-100 text-amber-700' => $invoice->status === 'sent',
                                    'bg-emerald-100 text-emerald-700' => $invoice->status === 'paid',
                                    'bg-rose-100 text-rose-700' => $invoice->status === 'cancelled',
                                ])>{{ \App\Models\Invoice::STATUSES[$invoice->status] }}</span>
                            </td>
                            <td class="py-2 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('invoices.print', $invoice) }}" target="_blank" title="Cetak" class="text-slate-400 hover:text-indigo-600">
                                        <x-icon name="printer" class="h-4 w-4" />
                                    </a>
                                    @if ($canManage)
                                        @if ($invoice->status === 'draft')
                                            <button wire:click="openEdit({{ $invoice->id }})" title="Ubah" class="text-slate-400 hover:text-indigo-600">
                                                <x-icon name="edit" class="h-4 w-4" />
                                            </button>
                                            <button wire:click="sendInvoice({{ $invoice->id }})" wire:confirm="Kirim invoice ini ke customer?" title="Kirim" class="text-slate-400 hover:text-indigo-600">
                                                <x-icon name="arrow-right" class="h-4 w-4" />
                                            </button>
                                            <button wire:click="cancelInvoice({{ $invoice->id }})" wire:confirm="Batalkan invoice ini?" title="Batalkan" class="text-slate-400 hover:text-rose-600">
                                                <x-icon name="x-circle" class="h-4 w-4" />
                                            </button>
                                        @elseif ($invoice->status === 'sent')
                                            <button wire:click="openMarkPaid({{ $invoice->id }})" title="Tandai Lunas" class="text-slate-400 hover:text-emerald-600">
                                                <x-icon name="check" class="h-4 w-4" />
                                            </button>
                                            <button wire:click="cancelInvoice({{ $invoice->id }})" wire:confirm="Batalkan invoice ini?" title="Batalkan" class="text-slate-400 hover:text-rose-600">
                                                <x-icon name="x-circle" class="h-4 w-4" />
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><x-empty-state icon="wallet" title="Belum ada invoice." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $invoices->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showModal', false)">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="plus-circle" class="h-5 w-5 text-indigo-500" /> {{ $editingId ? 'Ubah Invoice' : 'Buat Invoice' }}
                </h3>
                <form wire:submit="save" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Proyek</label>
                            <select wire:model.live="projectId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">-- pilih proyek --</option>
                                @foreach ($projects as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                            @error('projectId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Dokumen Pendukung</label>
                            <select wire:model="projectDocumentId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">-- tanpa dokumen pendukung --</option>
                                @foreach ($supportingDocuments as $doc)
                                    <option value="{{ $doc->id }}">{{ $doc->categoryLabel() }} (v{{ $doc->version }}) &mdash; {{ $doc->original_name }}</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-[11px] text-slate-400">BAPP RO atau BAL Bulanan terkait, bila ada.</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">No. Invoice</label>
                            <input type="text" wire:model="invoiceNumber" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('invoiceNumber') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Periode</label>
                            <input type="text" wire:model="periodLabel" placeholder="mis. Oktober 2026" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal Invoice</label>
                            <input type="date" wire:model="invoiceDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('invoiceDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Jatuh Tempo</label>
                            <input type="date" wire:model="dueDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('dueDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Subtotal (Rp)</label>
                            <input type="number" step="0.01" wire:model="subtotal" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('subtotal') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">PPN (%)</label>
                            <input type="number" step="0.01" wire:model="taxPercent" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('taxPercent') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-400">PPN dan Total akan dihitung otomatis saat disimpan.</p>

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

    @if ($showMarkPaidModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showMarkPaidModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="check" class="h-5 w-5 text-emerald-500" /> Tandai Lunas
                </h3>
                <form wire:submit="saveMarkPaid" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Masuk ke Akun Kas/Bank</label>
                        <select wire:model="cashBankAccountId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- pilih akun --</option>
                            @foreach ($cashBankAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }} ({{ \App\Models\CashBankAccount::TYPES[$account->type] }})</option>
                            @endforeach
                        </select>
                        @error('cashBankAccountId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        <p class="mt-1 text-[11px] text-slate-400">Akan tercatat otomatis sebagai transaksi masuk di Buku Kas/Bank.</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Referensi Pembayaran (opsional)</label>
                        <input type="text" wire:model="paymentReference" placeholder="mis. no. transfer / bukti bayar" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('paymentReference') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showMarkPaidModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                            <x-icon name="check" class="h-4 w-4" /> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
