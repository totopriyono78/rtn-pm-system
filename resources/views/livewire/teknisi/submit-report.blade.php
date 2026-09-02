<div class="max-w-2xl space-y-6">
    <div>
        <h2 class="text-xl font-semibold text-slate-800">Submit Laporan</h2>
        <p class="text-sm text-slate-500">Pilih penugasan, isi jam kerja, dan upload berkas pendukung (maks {{ $maxUploadMb }} MB per berkas).</p>
    </div>

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
            <label class="mb-1 block text-sm font-medium text-slate-700">Dokumen Laporan (PDF/DOCX/Video)</label>
            <input type="file" wire:model="documents" multiple class="block w-full text-sm">
            <div wire:loading wire:target="documents" class="text-xs text-slate-400">Mengunggah...</div>
            @error('documents.*') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Foto Dokumentasi Lapangan</label>
            <input type="file" wire:model="photos" multiple accept="image/*" class="block w-full text-sm">
            <div wire:loading wire:target="photos" class="text-xs text-slate-400">Mengunggah...</div>
            @error('photos.*') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-700">Drawing / As-built (opsional)</label>
            <input type="file" wire:model="drawings" multiple class="block w-full text-sm">
            <div wire:loading wire:target="drawings" class="text-xs text-slate-400">Mengunggah...</div>
            @error('drawings.*') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
        </div>

        <button type="submit" wire:loading.attr="disabled" wire:target="save"
            class="w-full rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500 disabled:opacity-50">
            Kirim Laporan
        </button>
    </form>
</div>
