<div class="space-y-6">
    <x-page-header icon="wallet" color="amber" title="Kasbon & Expense" subtitle="Approval, pencairan, dan verifikasi pertanggungjawaban kasbon teknisi." />

    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 px-4 py-2.5 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <select wire:model.live="statusFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                @foreach (\App\Models\CashAdvance::STATUSES as $key => $label)
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
                        <th class="pb-2">Pemohon</th>
                        <th class="pb-2">Proyek</th>
                        <th class="pb-2">Keperluan</th>
                        <th class="pb-2 text-right">Jumlah</th>
                        <th class="pb-2">Status</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cashAdvances as $ca)
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2 font-medium">{{ $ca->requester->name }}</td>
                            <td class="py-2 text-slate-500">{{ $ca->project->name ?? '-' }}</td>
                            <td class="py-2 text-slate-500">{{ \Illuminate\Support\Str::limit($ca->purpose, 40) }}</td>
                            <td class="py-2 text-right font-medium">Rp {{ number_format((float) $ca->amount_requested, 0, ',', '.') }}</td>
                            <td class="py-2">
                                <span @class([
                                    'rounded-full px-2 py-0.5 text-xs',
                                    'bg-slate-100 text-slate-600' => in_array($ca->status, ['diajukan', 'disetujui']),
                                    'bg-rose-100 text-rose-700' => in_array($ca->status, ['ditolak', 'dibatalkan']),
                                    'bg-amber-100 text-amber-700' => in_array($ca->status, ['dicairkan', 'dipertanggungjawabkan']),
                                    'bg-emerald-100 text-emerald-700' => $ca->status === 'selesai',
                                ])>{{ \App\Models\CashAdvance::STATUSES[$ca->status] }}</span>
                            </td>
                            <td class="py-2 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($ca->status === 'diajukan')
                                        <button wire:click="approve({{ $ca->id }})" title="Setujui" class="text-slate-400 hover:text-emerald-600">
                                            <x-icon name="check" class="h-4 w-4" />
                                        </button>
                                        <button wire:click="openReject({{ $ca->id }})" title="Tolak" class="text-slate-400 hover:text-rose-600">
                                            <x-icon name="x-circle" class="h-4 w-4" />
                                        </button>
                                    @elseif ($ca->status === 'disetujui')
                                        <button wire:click="openDisburse({{ $ca->id }})" class="rounded-lg bg-indigo-600 px-3 py-1 text-xs font-semibold text-white hover:bg-indigo-500">Tandai Dicairkan</button>
                                    @elseif ($ca->status === 'dipertanggungjawabkan')
                                        <button wire:click="openReview({{ $ca->id }})" class="rounded-lg border border-indigo-300 px-3 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-50">Review</button>
                                    @elseif (in_array($ca->status, ['dicairkan', 'selesai']))
                                        <button wire:click="openReview({{ $ca->id }})" title="Lihat rincian" class="text-slate-400 hover:text-indigo-600">
                                            <x-icon name="eye" class="h-4 w-4" />
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state icon="wallet" title="Belum ada pengajuan kasbon." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $cashAdvances->links() }}</div>
    </div>

    @if ($showRejectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showRejectModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="x-circle" class="h-5 w-5 text-rose-500" /> Tolak Kasbon
                </h3>
                <form wire:submit="saveReject" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Alasan Penolakan</label>
                        <textarea wire:model="rejectionReason" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('rejectionReason') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showRejectModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-rose-600 px-4 py-2 text-sm font-semibold text-white hover:bg-rose-500">
                            <x-icon name="check" class="h-4 w-4" /> Tolak
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showDisburseModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showDisburseModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="wallet" class="h-5 w-5 text-indigo-500" /> Tandai Dicairkan
                </h3>
                <p class="mb-4 text-sm text-slate-500">Pilih akun Kas/Bank tempat uang fisik dikeluarkan. Akan tercatat otomatis sebagai transaksi keluar di Buku Kas/Bank.</p>
                <form wire:submit="saveDisburse" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Akun Kas/Bank</label>
                        <select wire:model="cashBankAccountId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- pilih akun --</option>
                            @foreach ($cashBankAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }} ({{ \App\Models\CashBankAccount::TYPES[$account->type] }})</option>
                            @endforeach
                        </select>
                        @error('cashBankAccountId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showDisburseModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                            <x-icon name="check" class="h-4 w-4" /> Tandai Dicairkan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($reviewing)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="closeReview">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="wallet" class="h-5 w-5 text-amber-500" /> Rincian Pertanggungjawaban Kasbon
                </h3>
                <div class="mb-4 grid grid-cols-2 gap-3 text-sm">
                    <div><span class="text-slate-400">Pemohon</span><div class="font-medium">{{ $reviewing->requester->name }}</div></div>
                    <div><span class="text-slate-400">Jumlah Kasbon</span><div class="font-medium">Rp {{ number_format((float) $reviewing->amount_requested, 0, ',', '.') }}</div></div>
                </div>
                <div class="overflow-x-auto rounded-lg border border-slate-100">
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
                            @forelse ($reviewing->expenses as $exp)
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
                            @empty
                                <tr><td colspan="5" class="px-3 py-3 text-center text-slate-400">Belum ada rincian pengeluaran.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <p class="mt-3 text-sm {{ $reviewing->balance > 0 ? 'text-amber-600' : ($reviewing->balance < 0 ? 'text-rose-600' : 'text-slate-500') }}">
                    @if ($reviewing->balance > 0)
                        Sisa yang perlu dikembalikan teknisi ke kantor: <strong>Rp {{ number_format($reviewing->balance, 0, ',', '.') }}</strong>
                    @elseif ($reviewing->balance < 0)
                        Kekurangan yang perlu diganti kantor ke teknisi: <strong>Rp {{ number_format(abs($reviewing->balance), 0, ',', '.') }}</strong>
                    @else
                        Pas, tidak ada selisih.
                    @endif
                </p>
                <div class="mt-6 flex justify-end gap-2">
                    <button type="button" wire:click="closeReview" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Tutup</button>
                    @if ($reviewing->status === 'dipertanggungjawabkan')
                        <button wire:click="closeCashAdvance({{ $reviewing->id }})" wire:confirm="Tutup kasbon ini? Pastikan rincian pengeluaran & bukti sudah diverifikasi." class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                            <x-icon name="check" class="h-4 w-4" /> Tutup Kasbon
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
