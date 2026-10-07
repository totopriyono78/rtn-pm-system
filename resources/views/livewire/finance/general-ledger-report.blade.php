<div class="space-y-6">
    <x-page-header icon="chart-bar" color="indigo" title="Buku Besar (General Ledger)" subtitle="Mutasi per akun dari jurnal yang sudah posted saja. Jurnal draft/dibatalkan tidak dihitung." />

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Akun</label>
                <select wire:model.live="chartOfAccountId" class="w-64 rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">-- pilih akun --</option>
                    @foreach ($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->code }} -- {{ $acc->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Dari Tanggal</label>
                <input type="date" wire:model.live="startDate" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Sampai Tanggal</label>
                <input type="date" wire:model.live="endDate" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
        </div>
    </div>

    @if ($account)
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-700">{{ $account->code }} -- {{ $account->name }} <span class="text-xs text-slate-400">({{ $account->typeLabel() }}, normal {{ $account->normal_balance === 'debit' ? 'Debit' : 'Kredit' }})</span></h3>
                <span class="text-sm text-slate-500">Saldo Awal: <span class="font-semibold text-slate-800">Rp {{ number_format($openingBalance, 0, ',', '.') }}</span></span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase text-slate-400">
                        <tr>
                            <th class="pb-2">Tanggal</th>
                            <th class="pb-2">No. Jurnal</th>
                            <th class="pb-2">Keterangan</th>
                            <th class="pb-2 text-right">Debit</th>
                            <th class="pb-2 text-right">Kredit</th>
                            <th class="pb-2 text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($lines as $line)
                            <tr class="border-t border-slate-100">
                                <td class="py-2 text-slate-500">{{ $line->journalEntry->entry_date->format('d/m/Y') }}</td>
                                <td class="py-2 font-medium">{{ $line->journalEntry->entry_number }}</td>
                                <td class="py-2 text-slate-500">{{ $line->description ?: $line->journalEntry->description ?: '-' }}</td>
                                <td class="py-2 text-right">{{ $line->debit > 0 ? 'Rp '.number_format($line->debit, 0, ',', '.') : '-' }}</td>
                                <td class="py-2 text-right">{{ $line->credit > 0 ? 'Rp '.number_format($line->credit, 0, ',', '.') : '-' }}</td>
                                <td class="py-2 text-right font-medium">Rp {{ number_format($line->running_balance, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-empty-state icon="chart-bar" title="Tidak ada mutasi posted pada periode ini." /></td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="border-t-2 border-slate-200 font-semibold">
                            <td colspan="5" class="py-2 text-right">Saldo Akhir</td>
                            <td class="py-2 text-right">Rp {{ number_format($closingBalance, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @else
        <x-empty-state icon="chart-bar" title="Pilih akun untuk melihat mutasinya." />
    @endif
</div>
