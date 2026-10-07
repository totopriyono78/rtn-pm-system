<div class="space-y-6">
    <div class="flex items-center justify-between">
        <x-page-header icon="wallet" color="sky" title="Piutang Lain-lain" subtitle="Piutang di luar Invoice -- pinjaman karyawan, titipan vendor, atau piutang lain yang harus dikembalikan." />
        <button wire:click="openCreate" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-500">
            <x-icon name="plus" class="h-4 w-4" /> Ajukan Piutang
        </button>
    </div>

    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 px-4 py-2.5 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-center gap-3">
            <select wire:model.live="statusFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                @foreach (\App\Models\OtherReceivable::STATUSES as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">Kode</th>
                        <th class="pb-2">Debitur</th>
                        <th class="pb-2">Keperluan</th>
                        <th class="pb-2 text-right">Jumlah</th>
                        <th class="pb-2 text-right">Sisa</th>
                        <th class="pb-2">Status</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($receivables as $r)
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2 font-medium">{{ $r->code }}</td>
                            <td class="py-2 text-slate-500">
                                {{ $r->debtor_name }}
                                <span class="block text-xs text-slate-400">{{ $r->debtorTypeLabel() }}@if ($r->project) -- {{ $r->project->name }} @endif</span>
                            </td>
                            <td class="py-2 text-slate-500">{{ \Illuminate\Support\Str::limit($r->description, 40) }}</td>
                            <td class="py-2 text-right font-medium">Rp {{ number_format((float) $r->amount, 0, ',', '.') }}</td>
                            <td class="py-2 text-right">
                                @if (in_array($r->status, ['diberikan', 'lunas']))
                                    Rp {{ number_format($r->outstanding, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="py-2">
                                <span @class([
                                    'rounded-full px-2 py-0.5 text-xs',
                                    'bg-amber-100 text-amber-700' => in_array($r->status, ['diajukan', 'disetujui']),
                                    'bg-rose-100 text-rose-700' => in_array($r->status, ['ditolak', 'dibatalkan']),
                                    'bg-sky-100 text-sky-700' => $r->status === 'diberikan',
                                    'bg-emerald-100 text-emerald-700' => $r->status === 'lunas',
                                ])>{{ \App\Models\OtherReceivable::STATUSES[$r->status] }}</span>
                            </td>
                            <td class="py-2 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    @if ($r->status === 'diajukan')
                                        <button wire:click="approve({{ $r->id }})" title="Setujui" class="text-slate-400 hover:text-emerald-600">
                                            <x-icon name="check" class="h-4 w-4" />
                                        </button>
                                        <button wire:click="openReject({{ $r->id }})" title="Tolak" class="text-slate-400 hover:text-rose-600">
                                            <x-icon name="x-circle" class="h-4 w-4" />
                                        </button>
                                        <button wire:click="cancel({{ $r->id }})" wire:confirm="Batalkan pengajuan ini?" title="Batalkan" class="text-slate-400 hover:text-slate-600">
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    @elseif ($r->status === 'disetujui')
                                        <button wire:click="openGive({{ $r->id }})" class="rounded-lg bg-indigo-600 px-3 py-1 text-xs font-semibold text-white hover:bg-indigo-500">Tandai Diberikan</button>
                                    @elseif ($r->status === 'diberikan')
                                        <button wire:click="openPayment({{ $r->id }})" class="rounded-lg border border-indigo-300 px-3 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-50">Catat Pembayaran</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-empty-state icon="wallet" title="Belum ada pengajuan piutang lain-lain." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $receivables->links() }}</div>
    </div>

    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showCreateModal', false)">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="wallet" class="h-5 w-5 text-indigo-500" /> Ajukan Piutang Lain-lain
                </h3>
                <form wire:submit="save" class="space-y-4">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Jenis Debitur</label>
                            <select wire:model="debtorType" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                @foreach (\App\Models\OtherReceivable::DEBTOR_TYPES as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Nama Debitur</label>
                            <input type="text" wire:model="debtorName" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('debtorName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    @if ($debtorType === 'karyawan')
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Karyawan (opsional, kalau terdaftar sebagai user)</label>
                            <select wire:model="userId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                                <option value="">-- tidak terhubung ke user --</option>
                                @foreach ($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Proyek Terkait (opsional)</label>
                        <select wire:model="projectId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- tidak terkait proyek --</option>
                            @foreach ($projects as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Keperluan</label>
                        <textarea wire:model="description" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('description') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Jumlah</label>
                            <input type="number" step="0.01" wire:model="amount" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('amount') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Jatuh Tempo (opsional)</label>
                            <input type="date" wire:model="dueDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
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

    @if ($showRejectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showRejectModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="x-circle" class="h-5 w-5 text-rose-500" /> Tolak Pengajuan
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

    @if ($showGiveModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showGiveModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="wallet" class="h-5 w-5 text-indigo-500" /> Tandai Diberikan
                </h3>
                <p class="mb-4 text-sm text-slate-500">Pilih akun Kas/Bank tempat uang fisik dikeluarkan. Tercatat otomatis di Buku Kas/Bank dan Jurnal GL.</p>
                <form wire:submit="saveGive" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Akun Kas/Bank</label>
                        <select wire:model="giveCashBankAccountId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- pilih akun --</option>
                            @foreach ($cashBankAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }} ({{ \App\Models\CashBankAccount::TYPES[$account->type] }})</option>
                            @endforeach
                        </select>
                        @error('giveCashBankAccountId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showGiveModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                            <x-icon name="check" class="h-4 w-4" /> Tandai Diberikan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showPaymentModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showPaymentModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="wallet" class="h-5 w-5 text-emerald-500" /> Catat Pembayaran
                </h3>
                <p class="mb-4 text-sm text-slate-500">Bisa dicicil -- status otomatis jadi Lunas begitu total pembayaran mencapai jumlah piutang.</p>
                <form wire:submit="savePayment" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Akun Kas/Bank (uang masuk)</label>
                        <select wire:model="paymentCashBankAccountId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- pilih akun --</option>
                            @foreach ($cashBankAccounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }} ({{ \App\Models\CashBankAccount::TYPES[$account->type] }})</option>
                            @endforeach
                        </select>
                        @error('paymentCashBankAccountId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Jumlah</label>
                            <input type="number" step="0.01" wire:model="paymentAmount" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('paymentAmount') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal</label>
                            <input type="date" wire:model="paymentDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('paymentDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Catatan (opsional)</label>
                        <input type="text" wire:model="paymentNotes" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showPaymentModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                            <x-icon name="check" class="h-4 w-4" /> Simpan Pembayaran
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
