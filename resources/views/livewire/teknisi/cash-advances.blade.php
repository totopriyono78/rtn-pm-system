<div class="space-y-6">
    <div class="flex items-center justify-between">
        <x-page-header icon="wallet" color="amber" title="Kasbon & Expense" subtitle="Ajukan kasbon untuk keperluan lapangan, lalu laporkan pertanggungjawaban pengeluarannya." />
        <button wire:click="openCreate" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-500">
            <x-icon name="plus" class="h-4 w-4" /> Ajukan Kasbon
        </button>
    </div>

    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 px-4 py-2.5 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="space-y-4">
        @forelse ($cashAdvances as $ca)
            <div class="rounded-xl bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-slate-800">Rp {{ number_format((float) $ca->amount_requested, 0, ',', '.') }}</span>
                            <span @class([
                                'rounded-full px-2 py-0.5 text-xs',
                                'bg-slate-100 text-slate-600' => in_array($ca->status, ['diajukan', 'disetujui']),
                                'bg-rose-100 text-rose-700' => in_array($ca->status, ['ditolak', 'dibatalkan']),
                                'bg-amber-100 text-amber-700' => in_array($ca->status, ['dicairkan', 'dipertanggungjawabkan']),
                                'bg-emerald-100 text-emerald-700' => $ca->status === 'selesai',
                            ])>{{ \App\Models\CashAdvance::STATUSES[$ca->status] }}</span>
                        </div>
                        <p class="mt-1 text-sm text-slate-500">{{ $ca->purpose }}</p>
                        <p class="mt-1 text-xs text-slate-400">
                            {{ $ca->project->name ?? 'Tanpa proyek spesifik' }} &middot; diajukan {{ $ca->created_at->format('d/m/Y') }}
                        </p>
                        @if ($ca->status === 'ditolak' && $ca->rejection_reason)
                            <p class="mt-1 text-xs text-rose-600">Alasan ditolak: {{ $ca->rejection_reason }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @if ($ca->status === 'diajukan')
                            <button wire:click="cancel({{ $ca->id }})" wire:confirm="Batalkan pengajuan kasbon ini?" class="rounded-lg border border-slate-300 px-3 py-1.5 text-xs font-medium hover:bg-slate-50">Batalkan</button>
                        @endif
                        @if ($ca->status === 'dicairkan')
                            <button wire:click="openExpenseModal({{ $ca->id }})" class="inline-flex items-center gap-1 rounded-lg border border-indigo-300 px-3 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50">
                                <x-icon name="plus" class="h-3.5 w-3.5" /> Tambah Pengeluaran
                            </button>
                            <button wire:click="submitForReview({{ $ca->id }})" wire:confirm="Ajukan pertanggungjawaban kasbon ini? Pastikan semua rincian pengeluaran sudah dimasukkan." class="rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">Ajukan Pertanggungjawaban</button>
                        @endif
                    </div>
                </div>

                @if ($ca->expenses->isNotEmpty())
                    <div class="mt-4 overflow-x-auto rounded-lg border border-slate-100">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-400">
                                <tr>
                                    <th class="px-3 py-1.5">Tanggal</th>
                                    <th class="px-3 py-1.5">Kategori</th>
                                    <th class="px-3 py-1.5">Keterangan</th>
                                    <th class="px-3 py-1.5 text-right">Jumlah</th>
                                    <th class="px-3 py-1.5">Bukti</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ca->expenses as $exp)
                                    <tr class="border-t border-slate-100">
                                        <td class="px-3 py-1.5 text-slate-500">{{ $exp->expense_date->format('d/m/Y') }}</td>
                                        <td class="px-3 py-1.5">{{ \App\Models\ProjectBudgetLine::CATEGORIES[$exp->category] ?? $exp->category }}</td>
                                        <td class="px-3 py-1.5">{{ $exp->description }}</td>
                                        <td class="px-3 py-1.5 text-right">Rp {{ number_format((float) $exp->amount, 0, ',', '.') }}</td>
                                        <td class="px-3 py-1.5">
                                            @if ($exp->receipt_disk_path)
                                                <a href="{{ route('cash-advance-expenses.receipt', $exp) }}" class="text-indigo-600 hover:underline">Unduh</a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if (in_array($ca->status, ['dipertanggungjawabkan', 'selesai']))
                        <p class="mt-2 text-xs {{ $ca->balance > 0 ? 'text-amber-600' : ($ca->balance < 0 ? 'text-rose-600' : 'text-slate-400') }}">
                            @if ($ca->balance > 0)
                                Sisa yang perlu dikembalikan ke kantor: Rp {{ number_format($ca->balance, 0, ',', '.') }}
                            @elseif ($ca->balance < 0)
                                Kekurangan yang perlu diganti kantor: Rp {{ number_format(abs($ca->balance), 0, ',', '.') }}
                            @else
                                Pas, tidak ada selisih.
                            @endif
                        </p>
                    @endif
                @endif
            </div>
        @empty
            <x-empty-state icon="wallet" title="Belum ada pengajuan kasbon." />
        @endforelse
    </div>
    <div>{{ $cashAdvances->links() }}</div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="plus-circle" class="h-5 w-5 text-indigo-500" /> Ajukan Kasbon
                </h3>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Proyek (opsional)</label>
                        <select wire:model="projectId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- tanpa proyek spesifik --</option>
                            @foreach ($projects as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Jumlah yang Diajukan (Rp)</label>
                        <input type="number" step="0.01" wire:model="amountRequested" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('amountRequested') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Keperluan</label>
                        <textarea wire:model="purpose" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="mis. transportasi & akomodasi tim ke site selama 3 hari"></textarea>
                        @error('purpose') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                            <x-icon name="check" class="h-4 w-4" /> Ajukan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showExpenseModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showExpenseModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="plus-circle" class="h-5 w-5 text-indigo-500" /> Tambah Rincian Pengeluaran
                </h3>
                <form wire:submit="saveExpense" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Kategori</label>
                        <select wire:model="expenseCategory" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- pilih kategori --</option>
                            @foreach (\App\Models\ProjectBudgetLine::CATEGORIES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('expenseCategory') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Keterangan</label>
                        <input type="text" wire:model="expenseDescription" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="mis. tiket pesawat PP">
                        @error('expenseDescription') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Jumlah (Rp)</label>
                            <input type="number" step="0.01" wire:model="expenseAmount" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('expenseAmount') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal</label>
                            <input type="date" wire:model="expenseDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('expenseDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Bukti/Struk (opsional)</label>
                        <input type="file" wire:model="expenseReceipt"
                            class="block w-full cursor-pointer rounded-lg border border-slate-300 p-2 text-sm text-slate-500 file:mr-4 file:cursor-pointer file:rounded-lg file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-500">
                        @error('expenseReceipt') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showExpenseModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                            <x-icon name="check" class="h-4 w-4" /> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
