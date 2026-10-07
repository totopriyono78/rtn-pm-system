<div class="space-y-6">
    <x-page-header icon="chart-bar" color="violet" title="Cost Control" subtitle="Kelola budget & actual cost per proyek secara real-time." />

    @if (! $selectedProject)
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="relative mb-4 max-w-sm">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <x-icon name="search" class="h-4 w-4" />
                </span>
                <input wire:model.live.debounce.400ms="search" type="text" placeholder="Cari nama proyek..."
                    class="w-full rounded-lg border border-slate-300 py-2 pl-9 pr-3 text-sm">
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase text-slate-400">
                        <tr>
                            <th class="pb-2">Proyek</th>
                            <th class="pb-2">PIC / PM</th>
                            <th class="pb-2">Status</th>
                            <th class="pb-2 text-right">Budget</th>
                            <th class="pb-2 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($projects as $project)
                            <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                                <td class="py-2 font-medium text-slate-800">{{ $project->name }}</td>
                                <td class="py-2 text-slate-500">{{ $project->pic?->name ?? '-' }}</td>
                                <td class="py-2 text-slate-500">{{ \App\Models\Project::STATUSES[$project->status] ?? $project->status }}</td>
                                <td class="py-2 text-right text-slate-600">{{ $project->budget !== null ? 'Rp '.number_format((float) $project->budget, 0, ',', '.') : '-' }}</td>
                                <td class="py-2 text-right">
                                    <button wire:click="selectProject({{ $project->id }})" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 transition-colors hover:bg-indigo-50">
                                        <x-icon name="edit" class="h-3.5 w-3.5" /> Kelola Cost
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-sm text-slate-400">Tidak ada proyek ditemukan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $projects->links() }}
            </div>
        </div>
    @else
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <div class="mb-4 flex items-start justify-between">
                <div>
                    <button wire:click="backToList" class="mb-2 inline-flex items-center gap-1 text-xs font-medium text-slate-500 hover:text-slate-700">
                        <x-icon name="arrow-left" class="h-3.5 w-3.5" /> Kembali ke daftar proyek
                    </button>
                    <h3 class="text-lg font-semibold text-slate-800">{{ $selectedProject->name }}</h3>
                    <p class="text-sm text-slate-500">PIC/PM: {{ $selectedProject->pic?->name ?? '-' }}</p>
                </div>
            </div>

            @if ($summary)
                <div class="mb-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div class="rounded-lg bg-slate-50 p-3">
                        <div class="text-xs text-slate-400">Total Rencana (Budget)</div>
                        <div class="mt-1 text-sm font-semibold text-slate-700">Rp {{ number_format($summary['total_planned'], 0, ',', '.') }}</div>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-3">
                        <div class="text-xs text-slate-400">Total Aktual</div>
                        <div class="mt-1 text-sm font-semibold text-slate-700">Rp {{ number_format($summary['total_actual'], 0, ',', '.') }}</div>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-3">
                        <div class="text-xs text-slate-400">Selisih (Variance)</div>
                        <div class="mt-1 text-sm font-semibold {{ $summary['variance'] < 0 ? 'text-red-600' : 'text-green-600' }}">
                            Rp {{ number_format($summary['variance'], 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="rounded-lg bg-slate-50 p-3">
                        <div class="text-xs text-slate-400">Estimasi Profit</div>
                        <div class="mt-1 text-sm font-semibold text-slate-700">
                            {{ $summary['estimated_profit'] !== null ? 'Rp '.number_format($summary['estimated_profit'], 0, ',', '.') : 'Nilai proyek belum diisi' }}
                        </div>
                    </div>
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-xs uppercase text-slate-400">
                        <tr>
                            <th class="pb-2">Kategori</th>
                            <th class="pb-2 text-right">Budget (Rencana)</th>
                            <th class="pb-2 text-right">Actual (Realisasi)</th>
                            <th class="pb-2">Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $key => $label)
                            <tr class="border-t border-slate-100" wire:key="cost-line-{{ $key }}">
                                <td class="py-2 font-medium text-slate-700">{{ $label }}</td>
                                <td class="py-2">
                                    <input type="number" step="0.01" min="0" wire:model="lines.{{ $key }}.planned_amount" class="w-32 rounded-lg border border-slate-300 px-2 py-1 text-right text-sm">
                                    @error("lines.{$key}.planned_amount") <div class="text-xs text-red-600">{{ $message }}</div> @enderror
                                </td>
                                <td class="py-2">
                                    <input type="number" step="0.01" min="0" wire:model="lines.{{ $key }}.actual_amount" class="w-32 rounded-lg border border-slate-300 px-2 py-1 text-right text-sm">
                                    @error("lines.{$key}.actual_amount") <div class="text-xs text-red-600">{{ $message }}</div> @enderror
                                </td>
                                <td class="py-2">
                                    <input type="text" wire:model="lines.{{ $key }}.notes" placeholder="Opsional" class="w-full rounded-lg border border-slate-300 px-2 py-1 text-sm">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4 flex items-center justify-between">
                <p class="text-xs text-slate-400">Perubahan tersimpan langsung tanpa approval -- PM proyek ini akan menerima notifikasi otomatis.</p>
                <button wire:click="save" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-500">
                    <x-icon name="check" class="h-4 w-4" /> Simpan &amp; Beri Tahu PM
                </button>
            </div>
        </div>
    @endif
</div>
