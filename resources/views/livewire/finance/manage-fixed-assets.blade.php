<div class="space-y-6">
    <div class="flex items-center justify-between">
        <x-page-header icon="cube" color="violet" title="Asset Management" subtitle="Register aset tetap perusahaan (kendaraan, peralatan) dan depresiasi garis lurus." />
        <div class="flex items-center gap-2">
            <button wire:click="openDepreciation" class="inline-flex items-center gap-1.5 rounded-lg border border-violet-300 px-4 py-2 text-sm font-medium text-violet-600 hover:bg-violet-50">
                <x-icon name="refresh" class="h-4 w-4" /> Jalankan Depresiasi
            </button>
            <button wire:click="openCreate" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                <x-icon name="plus" class="h-4 w-4" /> Daftarkan Aset
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 px-4 py-2.5 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="text-xs uppercase text-slate-400">Total Harga Perolehan (Aset Aktif)</div>
            <div class="mt-1 text-xl font-semibold text-slate-800">Rp {{ number_format((float) $totalAcquisitionCost, 0, ',', '.') }}</div>
        </div>
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="text-xs uppercase text-slate-400">Total Nilai Buku Saat Ini (Aset Aktif)</div>
            <div class="mt-1 text-xl font-semibold text-slate-800">Rp {{ number_format((float) $totalBookValue, 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <select wire:model.live="statusFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                @foreach (\App\Models\FixedAsset::STATUSES as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
            <select wire:model.live="categoryFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Kategori</option>
                @foreach (\App\Models\FixedAsset::CATEGORIES as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">Kode</th>
                        <th class="pb-2">Nama Aset</th>
                        <th class="pb-2">Kategori</th>
                        <th class="pb-2 text-right">Harga Perolehan</th>
                        <th class="pb-2 text-right">Nilai Buku</th>
                        <th class="pb-2">Status</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assets as $asset)
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2 font-medium">{{ $asset->code }}</td>
                            <td class="py-2 text-slate-500">
                                {{ $asset->name }}
                                <span class="block text-xs text-slate-400">
                                    Perolehan {{ $asset->acquisition_date->format('d/m/Y') }}@if ($asset->unit) -- {{ $asset->unit->name }} @endif
                                </span>
                            </td>
                            <td class="py-2 text-slate-500">{{ $asset->categoryLabel() }}</td>
                            <td class="py-2 text-right">Rp {{ number_format((float) $asset->acquisition_cost, 0, ',', '.') }}</td>
                            <td class="py-2 text-right">
                                Rp {{ number_format($asset->book_value, 0, ',', '.') }}
                                @if ($asset->is_fully_depreciated)
                                    <span class="block text-xs text-slate-400">Fully depreciated</span>
                                @endif
                            </td>
                            <td class="py-2">
                                <span @class([
                                    'rounded-full px-2 py-0.5 text-xs',
                                    'bg-emerald-100 text-emerald-700' => $asset->status === 'aktif',
                                    'bg-slate-100 text-slate-600' => $asset->status === 'dijual',
                                    'bg-amber-100 text-amber-700' => $asset->status === 'rusak',
                                    'bg-rose-100 text-rose-700' => $asset->status === 'dihapuskan',
                                ])>{{ \App\Models\FixedAsset::STATUSES[$asset->status] }}</span>
                            </td>
                            <td class="py-2 text-right">
                                @if ($asset->status === 'aktif')
                                    <button wire:click="openDispose({{ $asset->id }})" class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-medium text-slate-600 hover:bg-slate-50">Lepas Aset</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-empty-state icon="cube" title="Belum ada aset tetap terdaftar." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $assets->links() }}</div>
    </div>

    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showCreateModal', false)">
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="cube" class="h-5 w-5 text-indigo-500" /> Daftarkan Aset Tetap
                </h3>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama Aset</label>
                        <input type="text" wire:model="name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Kategori</label>
                            <select wire:model="category" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                @foreach (\App\Models\FixedAsset::CATEGORIES as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Unit (opsional)</label>
                            <select wire:model="unitId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">-- tidak terkait unit --</option>
                                @foreach ($units as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tgl Perolehan</label>
                            <input type="date" wire:model="acquisitionDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('acquisitionDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Harga Perolehan</label>
                            <input type="number" step="0.01" wire:model="acquisitionCost" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('acquisitionCost') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Umur (tahun)</label>
                            <input type="number" wire:model="usefulLifeYears" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('usefulLifeYears') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Nilai Residu</label>
                            <input type="number" step="0.01" wire:model="salvageValue" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('salvageValue') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Lokasi (opsional)</label>
                            <input type="text" wire:model="location" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Catatan (opsional)</label>
                        <textarea wire:model="notes" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-3">
                        <label class="flex items-center gap-2 text-sm font-medium text-slate-700">
                            <input type="checkbox" wire:model.live="recordPurchase" class="rounded border-slate-300">
                            Ini pembelian baru (catat uang keluar dari Kas/Bank + jurnal GL)
                        </label>
                        <p class="mt-1 text-xs text-slate-500">Biarkan tidak dicentang kalau aset ini sudah dimiliki perusahaan sebelumnya -- murni didaftarkan untuk pelacakan/depresiasi, tanpa transaksi kas.</p>
                        @if ($recordPurchase)
                            <div class="mt-3">
                                <label class="mb-1 block text-sm font-medium text-slate-700">Akun Kas/Bank</label>
                                <select wire:model="cashBankAccountId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                    <option value="">-- pilih akun --</option>
                                    @foreach ($cashBankAccounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                                    @endforeach
                                </select>
                                @error('cashBankAccountId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </div>
                        @endif
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showCreateModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                            <x-icon name="check" class="h-4 w-4" /> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showDisposeModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showDisposeModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="x-circle" class="h-5 w-5 text-rose-500" /> Lepas Aset
                </h3>
                <form wire:submit="saveDispose" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Status</label>
                        <select wire:model="disposeStatus" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="dijual">Dijual</option>
                            <option value="rusak">Rusak/Tidak Terpakai</option>
                            <option value="dihapuskan">Dihapuskan</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Catatan (opsional)</label>
                        <textarea wire:model="disposalNotes" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                    </div>
                    <p class="text-xs text-slate-500">Tidak ada jurnal GL otomatis untuk pelepasan -- kalau ada laba/rugi penjualan yang perlu dicatat, gunakan Jurnal Umum (Entry Voucher) manual.</p>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showDisposeModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">
                            <x-icon name="check" class="h-4 w-4" /> Lepas Aset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showDepreciationModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showDepreciationModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="refresh" class="h-5 w-5 text-violet-500" /> Jalankan Depresiasi
                </h3>
                <p class="mb-4 text-sm text-slate-500">Menghitung & membukukan depresiasi garis lurus untuk semua aset aktif yang belum didepresiasi di periode ini. Satu jurnal GL teragregasi dibuat untuk seluruh aset.</p>
                <form wire:submit="runDepreciation" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Periode</label>
                        <input type="month" wire:model="depreciationPeriod" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('depreciationPeriod') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showDepreciationModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-violet-600 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-500">
                            <x-icon name="check" class="h-4 w-4" /> Jalankan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
