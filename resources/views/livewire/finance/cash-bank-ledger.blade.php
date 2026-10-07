<div class="space-y-6">
    <div class="flex items-center justify-between">
        <x-page-header icon="clipboard-list" color="indigo" title="Buku Kas/Bank" subtitle="Transaksi masuk/keluar per akun. Pelunasan Invoice & pencairan Kasbon tercatat otomatis; transaksi lain dicatat manual di sini." />
        <div class="flex gap-3">
            <a href="{{ route('admin.cash-bank-accounts') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                Kelola Akun
            </a>
            <button wire:click="openCreate" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-500">
                <x-icon name="plus" class="h-4 w-4" /> Catat Transaksi
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 px-4 py-2.5 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    {{-- ===== Ringkasan saldo per akun ===== --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($accounts as $account)
            <div class="rounded-xl bg-white p-4 shadow-sm">
                <p class="text-xs uppercase text-slate-400">{{ \App\Models\CashBankAccount::TYPES[$account->type] }}{{ $account->is_active ? '' : ' -- Nonaktif' }}</p>
                <p class="mt-1 font-medium text-slate-800">{{ $account->name }}</p>
                <p class="mt-2 text-xl font-bold text-slate-800">Rp {{ number_format($account->current_balance, 0, ',', '.') }}</p>
            </div>
        @empty
            <div class="col-span-full"><x-empty-state icon="wallet" title="Belum ada akun Kas/Bank." /></div>
        @endforelse
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <select wire:model.live="accountFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Akun</option>
                @foreach ($accounts as $account)
                    <option value="{{ $account->id }}">{{ $account->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="typeFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Tipe</option>
                @foreach (\App\Models\CashBankTransaction::TYPES as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">Tanggal</th>
                        <th class="pb-2">Akun</th>
                        <th class="pb-2">Kategori</th>
                        <th class="pb-2">Keterangan</th>
                        <th class="pb-2">Dicatat Oleh</th>
                        <th class="pb-2 text-right">Jumlah</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($transactions as $tx)
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2 text-slate-500">{{ $tx->transaction_date->format('d/m/Y') }}</td>
                            <td class="py-2">{{ $tx->account->name }}</td>
                            <td class="py-2 text-slate-500">
                                {{ $tx->categoryLabel() }}
                                @if ($tx->invoice)
                                    <span class="block text-xs text-slate-400">{{ $tx->invoice->invoice_number }}</span>
                                @elseif ($tx->cashAdvance)
                                    <span class="block text-xs text-slate-400">Kasbon #{{ $tx->cashAdvance->id }} -- {{ $tx->cashAdvance->requester->name }}</span>
                                @elseif ($tx->purchaseOrder)
                                    <span class="block text-xs text-slate-400">{{ $tx->purchaseOrder->code }} -- {{ $tx->purchaseOrder->vendor->name }}</span>
                                @endif
                            </td>
                            <td class="py-2 text-slate-500">{{ $tx->description ?: '-' }}</td>
                            <td class="py-2 text-slate-500">{{ $tx->creator?->name ?? '-' }}</td>
                            <td @class(['py-2 text-right font-medium', 'text-emerald-600' => $tx->type === 'in', 'text-rose-600' => $tx->type === 'out'])>
                                {{ $tx->type === 'in' ? '+' : '-' }} Rp {{ number_format((float) $tx->amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state icon="clipboard-list" title="Belum ada transaksi." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $transactions->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="plus-circle" class="h-5 w-5 text-indigo-500" /> Catat Transaksi Manual
                </h3>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Akun</label>
                        <select wire:model="cashBankAccountId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- pilih akun --</option>
                            @foreach ($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }}</option>
                            @endforeach
                        </select>
                        @error('cashBankAccountId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tipe</label>
                            <select wire:model.live="type" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                @foreach (\App\Models\CashBankTransaction::TYPES as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Kategori</label>
                            <select wire:model="category" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                @foreach (\App\Models\CashBankTransaction::CATEGORIES as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Jumlah (Rp)</label>
                            <input type="number" step="0.01" wire:model="amount" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('amount') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal</label>
                            <input type="date" wire:model="transactionDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('transactionDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Keterangan (opsional)</label>
                        <textarea wire:model="description" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('description') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <p class="text-[11px] text-slate-400">Catatan: transaksi tidak bisa diedit/dihapus setelah disimpan. Kalau salah catat, buat transaksi baru dengan arah berlawanan sebagai koreksi.</p>
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
