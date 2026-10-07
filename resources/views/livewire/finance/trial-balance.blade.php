<div class="space-y-6">
    <x-page-header icon="clipboard-list" color="indigo" title="Neraca Saldo (Trial Balance)" subtitle="Saldo per akun dari jurnal yang sudah posted, per tanggal yang dipilih." />

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="flex items-end gap-3">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Per Tanggal</label>
                <input type="date" wire:model.live="asOfDate" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
        </div>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">Kode</th>
                        <th class="pb-2">Nama Akun</th>
                        <th class="pb-2">Tipe</th>
                        <th class="pb-2 text-right">Debit</th>
                        <th class="pb-2 text-right">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($accounts as $account)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 font-medium">{{ $account->code }}</td>
                            <td class="py-2">{{ $account->name }}</td>
                            <td class="py-2 text-slate-500">{{ $account->typeLabel() }}</td>
                            <td class="py-2 text-right">{{ $account->display_debit > 0 ? 'Rp '.number_format($account->display_debit, 0, ',', '.') : '-' }}</td>
                            <td class="py-2 text-right">{{ $account->display_credit > 0 ? 'Rp '.number_format($account->display_credit, 0, ',', '.') : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty-state icon="clipboard-list" title="Belum ada akun." /></td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="border-t-2 border-slate-200 font-semibold">
                        <td colspan="3" class="py-2 text-right">Total</td>
                        <td class="py-2 text-right">Rp {{ number_format($totalDebit, 0, ',', '.') }}</td>
                        <td class="py-2 text-right">Rp {{ number_format($totalCredit, 0, ',', '.') }}</td>
                    </tr>
                    @if (abs($totalDebit - $totalCredit) >= 0.01)
                        <tr>
                            <td colspan="5" class="py-2 text-right text-xs font-semibold text-rose-600">Selisih Rp {{ number_format(abs($totalDebit - $totalCredit), 0, ',', '.') }} -- seharusnya tidak terjadi kalau semua jurnal posted sudah balance.</td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="5" class="py-2 text-right text-xs font-semibold text-emerald-600">Balance &check;</td>
                        </tr>
                    @endif
                </tfoot>
            </table>
        </div>
    </div>
</div>
