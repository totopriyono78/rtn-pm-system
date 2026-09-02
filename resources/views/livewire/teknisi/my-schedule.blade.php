<div class="space-y-6">
    <div>
        <h2 class="text-xl font-semibold text-slate-800">Jadwal Saya</h2>
        <p class="text-sm text-slate-500">Daftar penugasan yang di-assign kepada Anda.</p>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <table class="w-full text-left text-sm">
            <thead class="text-xs uppercase text-slate-400">
                <tr>
                    <th class="pb-2">Tanggal</th>
                    <th class="pb-2">Activity</th>
                    <th class="pb-2">Proyek</th>
                    <th class="pb-2">Lokasi</th>
                    <th class="pb-2">Catatan</th>
                    <th class="pb-2 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($assignments as $a)
                    <tr class="border-t border-slate-100">
                        <td class="py-2">{{ $a->scheduled_date->format('d M Y') }}</td>
                        <td class="py-2 font-medium">{{ $a->activity->name }}</td>
                        <td class="py-2">{{ $a->activity->project->name }}</td>
                        <td class="py-2 text-slate-500">{{ $a->activity->project->unit->region->code }} - {{ $a->activity->project->unit->name }}</td>
                        <td class="py-2 text-slate-500">{{ $a->notes ?: '-' }}</td>
                        <td class="py-2 text-right">
                            <a href="{{ route('teknisi.report.create') }}" class="text-indigo-600 hover:underline">Isi Laporan</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-4 text-center text-slate-400">Belum ada penugasan.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="mt-4">{{ $assignments->links() }}</div>
    </div>
</div>
