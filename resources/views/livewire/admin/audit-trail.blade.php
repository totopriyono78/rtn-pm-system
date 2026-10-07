<div class="space-y-6">
    <x-page-header icon="history" color="slate" title="Audit Trail" subtitle="Riwayat transaksi finansial & approval -- siapa melakukan apa, kapan (SRS 4.21)." />

    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 px-4 py-2.5 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap items-end gap-3">
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Aksi</label>
                <select wire:model.live="actionFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Semua Aksi</option>
                    @foreach (\App\Models\AuditLog::ACTIONS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Modul</label>
                <select wire:model.live="moduleFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Semua Modul</option>
                    @foreach ($modules as $m)
                        <option value="{{ $m }}">{{ (new \App\Models\AuditLog(['auditable_type' => $m]))->moduleLabel() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">User</label>
                <select wire:model.live="userFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">Semua User</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Dari Tanggal</label>
                <input type="date" wire:model.live="dateFrom" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-slate-500">Sampai Tanggal</label>
                <input type="date" wire:model.live="dateTo" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
            </div>
            <button wire:click="resetFilters" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Reset</button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">Waktu</th>
                        <th class="pb-2">User</th>
                        <th class="pb-2">Aksi</th>
                        <th class="pb-2">Modul</th>
                        <th class="pb-2">Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr class="border-t border-slate-100 align-top transition-colors hover:bg-slate-50/70">
                            <td class="py-2 whitespace-nowrap text-slate-500">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-2 font-medium">{{ $log->user->name ?? '-' }}</td>
                            <td class="py-2">
                                <span @class([
                                    'rounded-full px-2 py-0.5 text-xs',
                                    'bg-emerald-100 text-emerald-700' => in_array($log->action, ['approved', 'disbursed', 'given', 'paid', 'finalized', 'posted', 'payment_recorded']),
                                    'bg-rose-100 text-rose-700' => in_array($log->action, ['rejected', 'cancelled']),
                                    'bg-sky-100 text-sky-700' => in_array($log->action, ['created', 'submitted', 'sent']),
                                    'bg-slate-100 text-slate-600' => in_array($log->action, ['disposed', 'customer_decision']),
                                ])>{{ $log->actionLabel() }}</span>
                            </td>
                            <td class="py-2 text-slate-500">{{ $log->moduleLabel() }} <span class="text-xs text-slate-400">#{{ $log->auditable_id }}</span></td>
                            <td class="py-2 text-slate-600">{{ $log->description }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty-state icon="history" title="Belum ada riwayat audit trail." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $logs->links() }}</div>
    </div>
</div>
