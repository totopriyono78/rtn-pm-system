<div class="space-y-6">
    <div>
        <a href="{{ route('client.dashboard') }}" class="mb-2 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-indigo-600">
            <x-icon name="arrow-left" class="h-3.5 w-3.5" /> Kembali ke daftar proyek
        </a>
        <div class="flex items-start justify-between gap-4">
            <h1 class="text-xl font-semibold text-slate-800">{{ $project->name }}</h1>
            <span class="shrink-0 rounded-full px-2.5 py-1 text-xs font-medium
                @class([
                    'bg-sky-100 text-sky-700' => $project->status === 'planning',
                    'bg-amber-100 text-amber-700' => $project->status === 'ongoing',
                    'bg-emerald-100 text-emerald-700' => $project->status === 'completed',
                    'bg-slate-200 text-slate-600' => $project->status === 'on_hold',
                ])">
                {{ \App\Models\Project::STATUSES[$project->status] ?? $project->status }}
            </span>
        </div>
        @if ($project->description)
            <p class="mt-1 text-sm text-slate-500">{{ $project->description }}</p>
        @endif
    </div>

    <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="text-xs text-slate-400">Progres</div>
            <div class="mt-1 text-lg font-semibold text-slate-800">{{ $project->progress_percent }}%</div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="text-xs text-slate-400">Periode</div>
            <div class="mt-1 text-sm font-medium text-slate-800">
                {{ $project->start_date?->translatedFormat('d M Y') ?? '-' }}
                &ndash;
                {{ $project->end_date?->translatedFormat('d M Y') ?? 'Belum ditentukan' }}
            </div>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4">
            <div class="text-xs text-slate-400">PIC PT RTN</div>
            <div class="mt-1 text-sm font-medium text-slate-800">{{ $project->pic->name ?? '-' }}</div>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-5">
        <h2 class="mb-4 flex items-center gap-2 text-sm font-semibold text-slate-700">
            <x-icon name="doc-text" class="h-4 w-4 text-indigo-500" /> Dokumen Proyek
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">Nama Dokumen</th>
                        <th class="pb-2">Kategori</th>
                        <th class="pb-2">Tanggal</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($project->documents as $document)
                        <tr class="border-t border-slate-100">
                            <td class="py-2">{{ $document->original_name }}</td>
                            <td class="py-2 text-slate-500">{{ $document->categoryLabel() }}</td>
                            <td class="py-2 text-slate-500">{{ $document->created_at->translatedFormat('d M Y') }}</td>
                            <td class="py-2 text-right">
                                <a href="{{ route('client.documents.download', $document) }}" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-50">
                                    <x-icon name="download" class="h-3.5 w-3.5" /> Unduh
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><x-empty-state icon="doc-text" title="Belum ada dokumen yang dibagikan untuk proyek ini." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-5">
        <h2 class="mb-4 flex items-center gap-2 text-sm font-semibold text-slate-700">
            <x-icon name="wallet" class="h-4 w-4 text-indigo-500" /> Invoice Proyek Ini
        </h2>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">No. Invoice</th>
                        <th class="pb-2">Periode</th>
                        <th class="pb-2 text-right">Total</th>
                        <th class="pb-2">Status</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($project->invoices->whereIn('status', ['sent', 'paid']) as $invoice)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 font-medium">{{ $invoice->invoice_number }}</td>
                            <td class="py-2 text-slate-500">{{ $invoice->period_label }}</td>
                            <td class="py-2 text-right">Rp {{ number_format((float) $invoice->total_amount, 0, ',', '.') }}</td>
                            <td class="py-2">
                                @if ($invoice->status === 'paid')
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs text-emerald-700">Lunas</span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-0.5 text-xs text-amber-700">Belum Dibayar</span>
                                @endif
                            </td>
                            <td class="py-2 text-right">
                                <a href="{{ route('client.invoices.print', $invoice) }}" target="_blank" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-50">
                                    <x-icon name="printer" class="h-3.5 w-3.5" /> Cetak
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty-state icon="wallet" title="Belum ada invoice untuk proyek ini." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
