<div class="max-w-2xl space-y-6">
    <x-page-header icon="doc-plus" color="emerald" title="Submit Laporan" subtitle="Pilih penugasan, isi jam kerja, dan upload berkas pendukung (maks {{ $maxUploadMb }} MB per berkas)." />

    <form wire:submit="save" class="space-y-5 rounded-xl bg-white p-6 shadow-sm">
        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Penugasan</label>
            <select wire:model="assignmentId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">-- pilih penugasan --</option>
                @foreach ($assignments as $a)
                    <option value="{{ $a->id }}">{{ $a->scheduled_date->format('d M Y') }} - {{ $a->activity->name }} ({{ $a->activity->project->name }})</option>
                @endforeach
            </select>
            @error('assignmentId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            <label class="mt-1.5 flex items-center gap-2 text-xs text-slate-500">
                <input type="checkbox" wire:model.live="showCompletedActivities" class="rounded border-slate-300">
                Tampilkan juga penugasan yang activity-nya sudah Selesai
            </label>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Jenis Laporan</label>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 text-sm">
                    <input type="radio" value="daily" wire:model="type"> Daily Report
                </label>
                <label class="flex items-center gap-2 text-sm">
                    <input type="radio" value="final" wire:model="type"> Final Report
                </label>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-3">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal</label>
                <input type="date" wire:model="reportDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('reportDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Jam Mulai</label>
                <input type="time" wire:model="startTime" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('startTime') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">Jam Selesai</label>
                <input type="time" wire:model="endTime" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @error('endTime') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Catatan</label>
            <textarea wire:model="notes" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
        </div>

        <div>
            <label class="mb-1 flex items-center gap-1.5 text-sm font-medium text-slate-700">
                <x-icon name="doc-text" class="h-4 w-4 text-slate-400" /> Dokumen Laporan (PDF/DOCX/Video)
            </label>
            <input type="file" wire:model="documents" multiple class="block w-full text-sm">
            <div wire:loading wire:target="documents" class="text-xs text-slate-400">Mengunggah...</div>
            @error('documents.*') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="mb-1 flex items-center gap-1.5 text-sm font-medium text-slate-700">
                <x-icon name="package" class="h-4 w-4 text-slate-400" /> Foto Dokumentasi Lapangan
            </label>
            <input type="file" wire:model="photos" multiple accept="image/*" class="block w-full text-sm">
            <div wire:loading wire:target="photos" class="text-xs text-slate-400">Mengunggah...</div>
            @error('photos.*') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="mb-1 flex items-center gap-1.5 text-sm font-medium text-slate-700">
                <x-icon name="cube" class="h-4 w-4 text-slate-400" /> Drawing / As-built (opsional)
            </label>
            <input type="file" wire:model="drawings" multiple class="block w-full text-sm">
            <div wire:loading wire:target="drawings" class="text-xs text-slate-400">Mengunggah...</div>
            @error('drawings.*') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>

        <button type="submit" wire:loading.attr="disabled" wire:target="save"
            class="flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-500 disabled:opacity-50">
            <x-icon name="check" class="h-4 w-4" /> Kirim Laporan
        </button>
    </form>
</div>
