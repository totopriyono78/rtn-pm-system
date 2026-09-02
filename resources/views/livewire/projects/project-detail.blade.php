<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('projects.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:underline">
                <x-icon name="arrow-left" class="h-4 w-4" /> Kembali ke daftar proyek
            </a>
            <div class="mt-2">
                <x-page-header icon="briefcase" color="indigo" :title="$project->name" :subtitle="$project->unit->name . ' · ' . $project->unit->region->name . ' · PIC: ' . ($project->pic->name ?? '-')" />
            </div>
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

    @if ($canViewHarga)
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <div class="mb-3 flex items-center justify-between">
                <div class="flex items-center gap-1.5 text-xs uppercase text-slate-400">
                    <x-icon name="wallet" class="h-3.5 w-3.5" /> Budget Proyek
                </div>
                @if ($project->budget !== null && $project->budget_usage_percent !== null)
                    @php
                        $pct = $project->budget_usage_percent;
                        $badgeColor = $pct > 100 ? 'bg-red-50 text-red-700' : ($pct >= 80 ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700');
                    @endphp
                    <span class="rounded-full {{ $badgeColor }} px-2 py-0.5 text-xs font-semibold">{{ $pct }}% terpakai</span>
                @endif
            </div>

            @if ($project->budget === null)
                <p class="text-sm text-slate-400">Proyek ini tidak diberi batas budget (tidak dibatasi).</p>
            @else
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                    <div>
                        <div class="text-xs text-slate-400">Total Budget</div>
                        <div class="mt-0.5 text-base font-semibold text-slate-800">Rp {{ number_format($project->budget, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-400">Actual Penggunaan</div>
                        <div class="mt-0.5 text-base font-semibold text-slate-800">Rp {{ number_format($project->used_budget, 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-400">Sisa Budget</div>
                        <div class="mt-0.5 text-base font-semibold {{ $project->remaining_budget < 0 ? 'text-red-600' : 'text-slate-800' }}">Rp {{ number_format($project->remaining_budget, 0, ',', '.') }}</div>
                    </div>
                </div>
                <div class="mt-3 h-2 w-full overflow-hidden rounded-full bg-slate-100">
                    @php $barColor = $pct > 100 ? 'bg-red-500' : ($pct >= 80 ? 'bg-amber-500' : 'bg-emerald-500'); @endphp
                    <div class="h-2 rounded-full {{ $barColor }}" style="width: {{ min(100, $pct) }}%"></div>
                </div>
                @if ($pct > 100)
                    <p class="mt-2 text-xs text-red-600">Total pembelian sudah melebihi budget proyek.</p>
                @endif
            @endif
        </div>
    @endif

    @if ($project->documents->isNotEmpty())
        <div class="rounded-xl bg-white p-4 shadow-sm">
            <div class="mb-2 flex items-center gap-1.5 text-xs uppercase text-slate-400">
                <x-icon name="doc-text" class="h-3.5 w-3.5" /> Dokumen Proyek
            </div>
            <ul class="flex flex-wrap gap-2">
                @foreach ($project->documents as $doc)
                    <li>
                        <a href="{{ route('projects.documents.show', $doc) }}" target="_blank"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-200 px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-50">
                            <x-icon name="doc-text" class="h-3.5 w-3.5 text-slate-400" />
                            {{ $doc->original_name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex gap-2 border-b border-slate-200">
        @php
            $tabIcons = ['overview' => 'clipboard-list', 'gantt' => 'chart-bar', 'boq' => 'truck', 'reports' => 'doc-text'];
        @endphp
        @foreach (['overview' => 'Timeline Activity', 'gantt' => 'Gantt Chart', 'boq' => 'BOQ', 'reports' => 'Laporan'] as $tab => $label)
            <button wire:click="$set('activeTab', '{{ $tab }}')" class="flex items-center gap-1.5 border-b-2 px-4 py-2 text-sm transition-colors {{ $activeTab === $tab ? 'border-indigo-600 font-semibold text-indigo-600' : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                <x-icon :name="$tabIcons[$tab]" class="h-4 w-4" /> {{ $label }}
            </button>
        @endforeach
    </div>

    @if ($activeTab === 'overview')
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="mb-4 flex justify-end">
                @if ($canManage)
                    <button wire:click="openCreateActivity" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-500">
                        <x-icon name="plus" class="h-4 w-4" /> Tambah Activity
                    </button>
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
                                    @if ($activity->start_date && $activity->end_date)
                                        &middot; {{ $activity->start_date->format('d M Y') }} - {{ $activity->end_date->format('d M Y') }}
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if ($canManage)
                                    <select wire:change="quickUpdateStatus({{ $activity->id }}, $event.target.value)" class="rounded-lg border border-slate-300 px-2 py-1 text-xs">
                                        @foreach (\App\Models\Activity::STATUSES as $key => $label)
                                            <option value="{{ $key }}" @selected($activity->status === $key)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button wire:click="openAssignTeknisi({{ $activity->id }})" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-emerald-600 transition-colors hover:bg-emerald-50">
                                        <x-icon name="user-plus" class="h-3.5 w-3.5" /> Tugaskan
                                    </button>
                                    <button wire:click="openEditActivity({{ $activity->id }})" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 transition-colors hover:bg-indigo-50">
                                        <x-icon name="edit" class="h-3.5 w-3.5" /> Edit
                                    </button>
                                    <button wire:click="deleteActivity({{ $activity->id }})" wire:confirm="Hapus activity ini?" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-red-600 transition-colors hover:bg-red-50">
                                        <x-icon name="trash" class="h-3.5 w-3.5" /> Hapus
                                    </button>
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
                            <div class="mt-2.5 flex flex-wrap gap-1.5">
                                @foreach ($activity->assignments->sortByDesc('scheduled_date') as $assignment)
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 py-1 pl-2.5 pr-1.5 text-xs text-emerald-700">
                                        {{ $assignment->user->name }}
                                        <span class="text-emerald-500">&middot; {{ $assignment->scheduled_date->format('d M') }}</span>
                                        @if ($canManage)
                                            <button wire:click="removeAssignment({{ $assignment->id }})" wire:confirm="Batalkan penugasan ini?" class="ml-0.5 flex h-4 w-4 items-center justify-center rounded-full text-emerald-400 hover:bg-emerald-100 hover:text-emerald-700" title="Batalkan penugasan">
                                                <x-icon name="x-mark" class="h-3 w-3" />
                                            </button>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <x-empty-state icon="clipboard-list" title="Belum ada activity untuk proyek ini." />
                @endforelse
            </div>
        </div>
    @endif

    @if ($activeTab === 'gantt')
        <div class="rounded-xl bg-white p-5 shadow-sm">
            @if (! $gantt['hasRange'])
                <p class="text-sm text-slate-400">Belum ada activity dengan Tanggal Mulai &amp; Tanggal Selesai (atau proyek ini belum punya tanggal mulai/selesai). Isi tanggalnya lewat tombol Edit di tab Timeline Activity untuk menampilkan Gantt Chart.</p>
            @else
                <div class="mb-4 flex flex-wrap items-center gap-4 text-xs text-slate-500">
                    <span class="font-medium text-slate-600">{{ $gantt['rangeStart']->format('d M Y') }} &ndash; {{ $gantt['rangeEnd']->format('d M Y') }}</span>
                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-slate-400"></span> Belum Dimulai</span>
                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-indigo-500"></span> Sedang Dikerjakan</span>
                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span> Selesai</span>
                    @if ($gantt['todayPercent'] !== null)
                        <span class="flex items-center gap-1.5"><span class="h-2.5 w-0.5 rounded-full bg-red-400"></span> Hari ini</span>
                    @endif
                    @if ($gantt['undatedCount'] > 0)
                        <span class="text-amber-600">{{ $gantt['undatedCount'] }} activity belum diisi tanggal (tidak tampil di bawah ini)</span>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <div class="min-w-[720px]">
                        <div class="mb-1 flex">
                            <div class="w-48 shrink-0"></div>
                            <div class="flex flex-1">
                                @foreach ($gantt['months'] as $month)
                                    <div class="border-l border-slate-200 px-2 py-1 text-center text-xs font-medium text-slate-500" style="width: {{ $month['percent'] }}%">
                                        {{ $month['label'] }}
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            @foreach ($gantt['bars'] as $bar)
                                <div class="flex items-center">
                                    <div class="w-48 shrink-0 truncate pr-3 text-sm text-slate-700" title="{{ $bar['activity']->name }}">{{ $bar['activity']->name }}</div>
                                    <div class="relative h-6 flex-1 rounded bg-slate-50">
                                        @if ($gantt['todayPercent'] !== null)
                                            <div class="absolute inset-y-0 z-10 w-px bg-red-400" style="left: {{ $gantt['todayPercent'] }}%"></div>
                                        @endif
                                        <div class="group absolute inset-y-0 rounded {{ $bar['barClass'] }}" style="left: {{ $bar['left'] }}%; width: {{ max($bar['width'], 0.6) }}%">
                                            <div class="pointer-events-none absolute bottom-full left-0 z-20 mb-1 hidden whitespace-nowrap rounded-lg bg-slate-800 px-2.5 py-1.5 text-xs text-white group-hover:block">
                                                <div class="font-medium">{{ $bar['activity']->name }}</div>
                                                <div class="text-slate-300">{{ \App\Models\Activity::STATUSES[$bar['activity']->status] }} &middot; {{ $bar['activity']->start_date->format('d M Y') }} - {{ $bar['activity']->end_date->format('d M Y') }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    @if ($activeTab === 'boq')
        <div class="space-y-4">
            @forelse ($project->purchaseOrders as $po)
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <div class="mb-3 flex items-center justify-between">
                        <div class="font-medium text-slate-800">{{ $po->code }} &middot; {{ $po->vendor->name }}</div>
                        <a href="{{ route('purchasing.po.print', $po) }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg px-2 py-1 text-sm font-medium text-indigo-600 transition-colors hover:bg-indigo-50">
                            <x-icon name="printer" class="h-4 w-4" /> Cetak PO
                        </a>
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
                <div class="rounded-xl bg-white p-5 shadow-sm">
                    <x-empty-state icon="truck" title="Belum ada BOQ (Purchase Order yang diterbitkan) untuk proyek ini." />
                </div>
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
                            <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                                <td class="py-2">{{ $report->report_date->format('d M Y') }}</td>
                                <td class="py-2">{{ $report->activity->name }}</td>
                                <td class="py-2">{{ $report->user->name }}</td>
                                <td class="py-2">{{ \App\Models\Report::TYPES[$report->type] }}</td>
                                <td class="py-2">{{ $report->start_time }} - {{ $report->end_time }}</td>
                                <td class="py-2 space-x-2">
                                    @forelse ($report->files as $file)
                                        <a href="{{ route('reports.files.show', $file) }}" class="inline-flex items-center gap-1 text-indigo-600 hover:underline">
                                            <x-icon name="download" class="h-3.5 w-3.5" /> {{ \App\Models\ReportFile::CATEGORIES[$file->category] }}
                                        </a>
                                    @empty
                                        <span class="text-slate-300">-</span>
                                    @endforelse
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-empty-state icon="doc-text" title="Belum ada laporan." /></td></tr>
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
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="{{ $editingActivityId ? 'edit' : 'plus-circle' }}" class="h-5 w-5 text-indigo-500" />
                    {{ $editingActivityId ? 'Edit Activity' : 'Tambah Activity' }}
                </h3>
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
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal Mulai</label>
                            <input type="date" wire:model="activityStartDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('activityStartDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal Selesai</label>
                            <input type="date" wire:model="activityEndDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('activityEndDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <p class="text-xs text-slate-400">Tanggal mulai/selesai dipakai untuk menampilkan activity ini di tab Gantt Chart.</p>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showActivityModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                            <x-icon name="check" class="h-4 w-4" /> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showAssignModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showAssignModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="user-plus" class="h-5 w-5 text-emerald-500" /> Tugaskan Teknisi
                </h3>
                <form wire:submit="saveAssignment" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Teknisi</label>
                        <select wire:model="assignUserId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- Pilih Teknisi --</option>
                            @foreach ($teknisiOptions as $teknisi)
                                <option value="{{ $teknisi->id }}">{{ $teknisi->name }}</option>
                            @endforeach
                        </select>
                        @error('assignUserId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        @if ($teknisiOptions->isEmpty())
                            <p class="mt-1 text-xs text-amber-600">Belum ada akun dengan role Teknisi. Tambahkan lewat menu Kelola User terlebih dahulu.</p>
                        @endif
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal Penugasan</label>
                        <input type="date" wire:model="assignScheduledDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('assignScheduledDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Catatan (opsional)</label>
                        <textarea wire:model="assignNotes" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="mis. Bawa alat ukur tambahan"></textarea>
                        @error('assignNotes') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showAssignModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                            <x-icon name="user-plus" class="h-4 w-4" /> Tugaskan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
