<div class="space-y-6">
    <div>
        <h1 class="text-xl font-semibold text-slate-800">Invoice</h1>
        <p class="text-sm text-slate-500">Seluruh invoice yang sudah dikirim PT RTN ke perusahaan Anda, lintas proyek.</p>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex items-center gap-2">
            <select wire:model.live="statusFilter" class="rounded-lg border border-slate-300 py-2 px-3 text-sm">
                <option value="">Semua Status</option>
                <option value="sent">Belum Dibayar</option>
                <option value="paid">Lunas</option>
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">No. Invoice</th>
                        <th class="pb-2">Proyek</th>
                        <th class="pb-2">Periode</th>
                        <th class="pb-2 text-right">Total</th>
                        <th class="pb-2">Status</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($invoices as $invoice)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 font-medium">{{ $invoice->invoice_number }}</td>
                            <td class="py-2 text-slate-500">{{ $invoice->project->name ?? '-' }}</td>
                            <td class="py-2 text-slate-500">{{ $invoice->period_label }}</td>
                            <td class="py-2 text-right">Rp {{ number_format((float) $invoice->total_amount, 0, ',', '.') }}</td>
                            <td class="py-2">
                                @if ($invoice->status === 'paid')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700">Lunas</span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-700">Belum Dibayar</span>
                                @endif
                            </td>
                            <td class="py-2 text-right">
                                <a href="{{ route('client.invoices.print', $invoice) }}" target="_blank" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-50">
                                    <x-icon name="printer" class="h-3.5 w-3.5" /> Cetak
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state icon="wallet" title="Belum ada invoice." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $invoices->links() }}</div>
    </div>
</div>
