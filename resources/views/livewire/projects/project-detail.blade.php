<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('projects.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; Kembali ke daftar proyek</a>
            <h2 class="mt-1 text-xl font-semibold text-slate-800">{{ $project->name }}</h2>
            <p class="text-sm text-slate-500">{{ $project->unit->name }} &middot; {{ $project->unit->region->name }} &middot; PIC: {{ $project->pic->name ?? '-' }}</p>
        </div>
        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium">{{ \App\Models\Project::STATUSES[$project->status] }}</span>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <div class="text-xs uppercase text-slate-400">Progress</div>
            <div class="mt-1 flex items-center gap-2">
                <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-2 rounded-full bg-indigo-500" style="width: {{ $project->progress_percent }}%"></div>
                </div>
                <span class="text-sm font-semibold">{{ $project->progress_percent }}%</span>
            </div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <div class="text-xs uppercase text-slate-400">Jam Rencana</div>
            <div class="mt-1 text-lg font-semibold">{{ number_format($project->planned_hours, 1) }} jam</div>
        </div>
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <div class="text-xs uppercase text-slate-400">Jam Aktual</div>
            <div class="mt-1 text-lg font-semibold">{{ number_format($project->actual_hours, 1) }} jam</div>
        </div>
    </div>

    <div class="flex gap-2 border-b border-slate-200">
        @foreach (['overview' => 'Timeline Activity', 'boq' => 'BOQ', 'reports' => 'Laporan'] as $tab => $label)
            <button wire:click="$set('activeTab', '{{ $tab }}')" class="border-b-2 px-4 py-2 text-sm {{ $activeTab === $tab ? 'border-indigo-600 font-semibold text-indigo-600' : 'border-transparent text-slate-500' }}">{{ $label }}</button>
        @endforeach
    </div>

    @if ($activeTab === 'overview')
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="mb-4 flex justify-end">
                @if ($canManage)
                    <button wire:click="openCreateActivity" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">+ Tambah Activity</button>
                @endif
            </div>

            <div class="space-y-3">
                @forelse ($project->activities as $activity)
                    <div class="rounded-lg border border-slate-100 p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-medium text-slate-800">{{ $activity->name }}</div>
                                <div class="text-xs text-slate-500">
                                    Rencana {{ number_format($activity->planned_hours, 1) }} jam &middot;
                                    Aktual {{ number_format($activity->actual_hours, 1) }} jam
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if ($canManage)
                                    <select wire:change="quickUpdateStatus({{ $activity->id }}, $event.target.value)" class="rounded-lg border border-slate-300 px-2 py-1 text-xs">
                                        @foreach (\App\Models\Activity::STATUSES as $key => $label)
                                            <option value="{{ $key }}" @selected($activity->status === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button wire:click="openEditActivity({{ $activity->id }})" class="text-xs text-indigo-600 hover:underline">Edit</button>
                                    <button wire:click="deleteActivity({{ $activity->id }})" wire:confirm="Hapus activity ini?" class="text-xs text-red-600 hover:underline">Hapus</button>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs">{{ \App\Models\Activity::STATUSES[$activity->status] }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100">
                            @php
                                $barWidth = match ($activity->status) {
                                    'selesai' => 100,
                                    'sedang_dikerjakan' => 50,
                                    default => 0,
                                };
                            @endphp
                            <div class="h-1.5 rounded-full bg-emerald-500" style="width: {{ $barWidth }}%"></div>
                        </div>
                        @if ($activity->assignments->isNotEmpty())
                            <div class="mt-2 text-xs text-slate-400">
                                Ditugaskan ke: {{ $activity->assignments->pluck('user.name')->unique()->join(', ') }}
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Belum ada activity untuk proyek ini.</p>
                @endforelse
            </div>
        </div>
    @endif

    @if ($activeTab === 'boq')
        <div class="space-y-4">
            @forelse ($project->purchaseOrders as $po)
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="font-medium text-slate-800">{{ $po->code }} &middot; {{ $po->vendor->name }}</div>
                        <a href="{{ route('purchasing.po.print', $po) }}" target="_blank" class="text-sm text-indigo-600 hover:underline">Cetak PO</a>
                    </div>
                    <table class="w-full text-left text-sm">
                        <thead class="text-xs uppercase text-slate-400">
                            <tr>
                                <th class="pb-2">Item</th>
                                <th class="pb-2">Qty</th>
                                <th class="pb-2">Satuan</th>
                                @if ($canViewHarga)
                                    <th class="pb-2 text-right">Subtotal</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($po->items as $line)
                                <tr class="border-t border-slate-100">
                                    <td class="py-2">{{ $line->item->name }}</td>
                                    <td class="py-2">{{ rtrim(rtrim(number_format($line->qty, 2), '0'), '.') }}</td>
                                    <td class="py-2 text-slate-500">{{ $line->item->unit_of_measure }}</td>
                                    @if ($canViewHarga)
                                        <td class="py-2 text-right">Rp {{ number_format($line->subtotal, 0, ',', '.') }}</td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @empty
                <div class="rounded-xl bg-white p-5 text-sm text-slate-400 shadow-sm">Belum ada BOQ (Purchase Order yang diterbitkan) untuk proyek ini.</div>
            @endforelse
        </div>
    @endif

    @if ($activeTab === 'reports')
        <div class="rounded-xl bg-white p-5 shadow-sm">
            @if ($canViewReports)
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase text-slate-400">
                        <tr>
                            <th class="pb-2">Tanggal</th>
                            <th class="pb-2">Activity</th>
                            <th class="pb-2">Teknisi</th>
                            <th class="pb-2">Tipe</th>
                            <th class="pb-2">Jam</th>
                            <th class="pb-2">Berkas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($reports as $report)
                            <tr class="border-t border-slate-100">
                                <td class="py-2">{{ $report->report_date->format('d M Y') }}</td>
                                <td class="py-2">{{ $report->activity->name }}</td>
                                <td class="py-2">{{ $report->user->name }}</td>
                                <td class="py-2">{{ \App\Models\Report::TYPES[$report->type] }}</td>
                                <td class="py-2">{{ $report->start_time }} - {{ $report->end_time }}</td>
                                <td class="py-2 space-x-2">
                                    @forelse ($report->files as $file)
                                        <a href="{{ route('reports.files.show', $file) }}" class="text-indigo-600 hover:underline">{{ \App\Models\ReportFile::CATEGORIES[$file->category] }}</a>
                                    @empty
                                        <span class="text-slate-300">-</span>
                                    @endforelse
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-4 text-center text-slate-400">Belum ada laporan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <p class="text-sm text-slate-400">Anda tidak memiliki izin untuk melihat laporan.</p>
            @endif
        </div>
    @endif

    @if ($showActivityModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showActivityModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-slate-800">{{ $editingActivityId ? 'Edit Activity' : 'Tambah Activity' }}</h3>
                <form wire:submit="saveActivity" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama Activity</label>
                        <input type="text" wire:model="activityName" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="mis. Site Survey">
                        @error('activityName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Status</label>
                        <select wire:model="activityStatus" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @foreach (\App\Models\Activity::STATUSES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Estimasi Jam (Rencana)</label>
                        <input type="number" step="0.5" min="0" wire:model="activityPlannedHours" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showActivityModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">Batal</button>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
