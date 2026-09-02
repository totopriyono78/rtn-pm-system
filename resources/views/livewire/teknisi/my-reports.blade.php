<div class="space-y-6">
    <div>
        <h2 class="text-xl font-semibold text-slate-800">Riwayat Laporan Saya</h2>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="text-xs uppercase text-slate-400">
                <tr>
                    <th class="pb-2">Tanggal</th>
                    <th class="pb-2">Activity</th>
                    <th class="pb-2">Tipe</th>
                    <th class="pb-2">Jam</th>
                    <th class="pb-2">Durasi</th>
                    <th class="pb-2">Berkas</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reports as $report)
                    <tr class="border-t border-slate-100">
                        <td class="py-2">{{ $report->report_date->format('d M Y') }}</td>
                        <td class="py-2">
                            <div class="font-medium">{{ $report->activity->name }}</div>
                            <div class="text-xs text-slate-500">{{ $report->activity->project->name }}</div>
                        </td>
                        <td class="py-2">{{ \App\Models\Report::TYPES[$report->type] }}</td>
                        <td class="py-2">{{ $report->start_time }} - {{ $report->end_time }}</td>
                        <td class="py-2">{{ number_format($report->duration_minutes / 60, 1) }} jam</td>
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
        <div class="mt-4">{{ $reports->links() }}</div>
    </div>
</div>
