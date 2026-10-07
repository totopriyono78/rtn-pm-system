<div class="space-y-6">
    <div class="flex items-center justify-between">
        <x-page-header icon="chart-bar" color="emerald" title="Budgeting (Anggaran Tahunan)" subtitle="Anggaran per akun Chart of Account, dibandingkan realisasi dari Jurnal GL yang sudah posted." />
        @if ($canManage)
            <button wire:click="openCreate" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                <x-icon name="plus" class="h-4 w-4" /> Buat Anggaran
            </button>
        @endif
    </div>

    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 px-4 py-2.5 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">Kode</th>
                        <th class="pb-2">Tahun</th>
                        <th class="pb-2 text-right">Total Rencana</th>
                        <th class="pb-2">Status</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($budgets as $budget)
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2 font-medium">{{ $budget->code }}</td>
                            <td class="py-2 text-slate-500">{{ $budget->period_year }}</td>
                            <td class="py-2 text-right">Rp {{ number_format((float) $budget->lines_sum_planned_amount, 0, ',', '.') }}</td>
                            <td class="py-2">
                                <span @class([
                                    'rounded-full px-2 py-0.5 text-xs',
                                    'bg-amber-100 text-amber-700' => $budget->status === 'draft',
                                    'bg-emerald-100 text-emerald-700' => $budget->status === 'disetujui',
                                    'bg-rose-100 text-rose-700' => $budget->status === 'dibatalkan',
                                ])>{{ \App\Models\Budget::STATUSES[$budget->status] }}</span>
                            </td>
                            <td class="py-2 text-right">
                                <div class="inline-flex items-center gap-1">
                                    <button wire:click="openDetail({{ $budget->id }})" class="rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-50">Lihat vs Aktual</button>
                                    @if ($canManage && $budget->status === 'draft')
                                        <button wire:click="openEdit({{ $budget->id }})" class="rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-50">Ubah</button>
                                        <button wire:click="cancelBudget({{ $budget->id }})" wire:confirm="Batalkan anggaran draft ini?" class="rounded-lg px-2 py-1 text-xs font-medium text-rose-600 hover:bg-rose-50">Batalkan</button>
                                    @endif
                                    @if ($canApprove && $budget->status === 'draft')
                                        <button wire:click="approveBudget({{ $budget->id }})" wire:confirm="Setujui anggaran ini? Setelah disetujui, tidak bisa diubah lagi." class="rounded-lg px-2 py-1 text-xs font-medium text-emerald-600 hover:bg-emerald-50">Setujui</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty-state icon="chart-bar" title="Belum ada anggaran." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $budgets->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showModal', false)">
            <div class="max-h-[90vh] w-full max-w-3xl overflow-y-auto rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="chart-bar" class="h-5 w-5 text-indigo-500" /> {{ $editingId ? 'Ubah Anggaran (Draft)' : 'Buat Anggaran Baru' }}
                </h3>
                <form wire:submit="save" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tahun Anggaran</label>
                            <input type="number" wire:model="periodYear" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('periodYear') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Catatan (opsional)</label>
                            <input type="text" wire:model="notes" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>

                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <label class="block text-sm font-medium text-slate-700">Baris Anggaran per Akun</label>
                            <button type="button" wire:click="addLine" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-50">
                                <x-icon name="plus" class="h-3.5 w-3.5" /> Tambah Baris
                            </button>
                        </div>
                        <div class="space-y-2">
                            @foreach ($lines as $i => $line)
                                <div class="grid grid-cols-12 gap-2 rounded-lg bg-slate-50 p-2">
                                    <div class="col-span-5">
                                        <select wire:model="lines.{{ $i }}.chart_of_account_id" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                            <option value="">-- akun --</option>
                                            @foreach ($accounts as $account)
                                                <option value="{{ $account->id }}">{{ $account->code }} -- {{ $account->name }}</option>
                                            @endforeach
                                        </select>
                                        @error("lines.{$i}.chart_of_account_id") <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-span-3">
                                        <input type="number" step="0.01" wire:model="lines.{{ $i }}.planned_amount" placeholder="Jumlah rencana" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                        @error("lines.{$i}.planned_amount") <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-span-3">
                                        <input type="text" wire:model="lines.{{ $i }}.notes" placeholder="Catatan" class="w-full rounded-lg border border-slate-300 px-2 py-1.5 text-sm">
                                    </div>
                                    <div class="col-span-1 flex items-center justify-end">
                                        <button type="button" wire:click="removeLine({{ $i }})" class="text-rose-500 hover:text-rose-700">
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('lines') <p class="mt-2 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center justify-end border-t border-slate-200 pt-3 text-sm">
                        <span>Total Rencana: <span class="font-semibold">Rp {{ number_format($this->totalPlanned, 0, ',', '.') }}</span></span>
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

    @if ($detail)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="closeDetail">
            <div class="max-h-[90vh] w-full max-w-4xl overflow-y-auto rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="chart-bar" class="h-5 w-5 text-emerald-500" /> {{ $detail->code }} -- Anggaran vs Aktual {{ $detail->period_year }}
                </h3>
                <div class="overflow-x-auto rounded-lg border border-slate-100">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-400">
                            <tr>
                                <th class="px-3 py-2">Akun</th>
                                <th class="px-3 py-2 text-right">Rencana</th>
                                <th class="px-3 py-2 text-right">Aktual (Jurnal Posted)</th>
                                <th class="px-3 py-2 text-right">Selisih</th>
                                <th class="px-3 py-2 text-right">% Terpakai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($detail->lines as $line)
                                <tr class="border-t border-slate-100">
                                    <td class="px-3 py-2">{{ $line->chartOfAccount->code }} -- {{ $line->chartOfAccount->name }}</td>
                                    <td class="px-3 py-2 text-right">Rp {{ number_format((float) $line->planned_amount, 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-right">Rp {{ number_format($line->actual, 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-right {{ $line->variance < 0 ? 'text-rose-600' : 'text-emerald-600' }}">Rp {{ number_format($line->variance, 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-right">
                                        @if ($line->usage_percent !== null)
                                            <span class="{{ $line->usage_percent > 100 ? 'text-rose-600 font-semibold' : 'text-slate-600' }}">{{ number_format($line->usage_percent, 1) }}%</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-xs text-slate-400">Aktual dihitung dari baris Jurnal Umum berstatus posted (manual maupun otomatis) sepanjang tahun {{ $detail->period_year }} untuk masing-masing akun.</p>
                <div class="mt-4 flex justify-end">
                    <button type="button" wire:click="closeDetail" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Tutup</button>
                </div>
            </div>
        </div>
    @endif
</div>
