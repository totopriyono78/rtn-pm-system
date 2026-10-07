<div class="space-y-6">
    <x-page-header icon="truck" color="emerald" title="Purchase Order" subtitle="PO diterbitkan otomatis per vendor saat RFQ disetujui Direktur." />

    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <x-icon name="filter" class="h-4 w-4 text-slate-400" />
            <select wire:model.live="vendorFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Vendor</option>
                @foreach ($vendors as $v)
                    <option value="{{ $v->id }}">{{ $v->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="statusFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                @foreach (\App\Models\PurchaseOrder::STATUSES as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">No. PO</th>
                        <th class="pb-2">Vendor</th>
                        <th class="pb-2">Proyek</th>
                        @if ($canViewHarga)
                            <th class="pb-2 text-right">Total</th>
                        @endif
                        <th class="pb-2">Status</th>
                        <th class="pb-2">Pembayaran</th>
                        <th class="pb-2">Tanggal</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $po)
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2 font-medium">{{ $po->code }}</td>
                            <td class="py-2">
                                <a href="{{ route('purchasing.vendors.show', $po->vendor) }}" class="text-indigo-600 hover:underline">{{ $po->vendor->name }}</a>
                            </td>
                            <td class="py-2">{{ $po->project->name }}</td>
                            @if ($canViewHarga)
                                <td class="py-2 text-right">Rp {{ number_format($po->total, 0, ',', '.') }}</td>
                            @endif
                            <td class="py-2"><span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs">{{ \App\Models\PurchaseOrder::STATUSES[$po->status] }}</span></td>
                            <td class="py-2">
                                @if ($po->payment_status === 'lunas')
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700">Lunas</span>
                                @else
                                    <span class="rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-700">Belum Dibayar</span>
                                @endif
                            </td>
                            <td class="py-2 text-slate-500">{{ $po->created_at->format('d M Y') }}</td>
                            <td class="py-2 text-right">
                                <div class="inline-flex items-center gap-1">
                                    @if ($canPay && $po->status === 'issued' && $po->payment_status !== 'lunas')
                                        <button wire:click="openPay({{ $po->id }})" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-emerald-600 transition-colors hover:bg-emerald-50">
                                            <x-icon name="check" class="h-3.5 w-3.5" /> Tandai Dibayar
                                        </button>
                                    @endif
                                    <a href="{{ route('purchasing.po.print', $po) }}" target="_blank" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 transition-colors hover:bg-indigo-50">
                                        <x-icon name="printer" class="h-3.5 w-3.5" /> Cetak
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><x-empty-state icon="truck" title="Belum ada Purchase Order." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $orders->links() }}</div>
    </div>

    @if ($showPayModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showPayModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="check" class="h-5 w-5 text-emerald-500" /> Tandai Dibayar
                </h3>
                <form wire:submit="savePay" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Keluar dari Akun Kas/Bank</label>
                        <select wire:model="cashBankAccountId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- pilih akun --</option>
                            @foreach ($cashBankAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }} ({{ \App\Models\CashBankAccount::TYPES[$account->type] }})</option>
                            @endforeach
                        </select>
                        @error('cashBankAccountId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        <p class="mt-1 text-[11px] text-slate-400">Akan tercatat otomatis sebagai transaksi keluar (Pembayaran ke Vendor) di Buku Kas/Bank.</p>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Referensi Pembayaran (opsional)</label>
                        <input type="text" wire:model="paymentReference" placeholder="mis. no. transfer / bukti bayar" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('paymentReference') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showPayModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                            <x-icon name="check" class="h-4 w-4" /> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
