<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-slate-800">Proyek</h2>
            <p class="text-sm text-slate-500">Daftar proyek sesuai akses region Anda.</p>
        </div>
        @if ($canManage)
            <button wire:click="openCreate" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">+ Tambah Proyek</button>
        @endif
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex flex-wrap gap-3">
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Cari nama proyek..."
                class="w-full max-w-sm rounded-lg border border-slate-300 px-3 py-2 text-sm">
            <select wire:model.live="statusFilter" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">Semua Status</option>
                @foreach (\App\Models\Project::STATUSES as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <table class="w-full text-left text-sm">
            <thead class="text-xs uppercase text-slate-400">
                <tr>
                    <th class="pb-2">Proyek</th>
                    <th class="pb-2">Lokasi</th>
                    <th class="pb-2">PIC</th>
                    <th class="pb-2">Status</th>
                    <th class="pb-2">Progress</th>
                    <th class="pb-2 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($projects as $p)
                    <tr class="border-t border-slate-100">
                        <td class="py-2 font-medium"><a href="{{ route('projects.show', $p) }}" class="text-indigo-600 hover:underline">{{ $p->name }}</a></td>
                        <td class="py-2 text-slate-500">{{ $p->unit->name }} &middot; {{ $p->unit->region->code }}</td>
                        <td class="py-2 text-slate-500">{{ $p->pic->name ?? '-' }}</td>
                        <td class="py-2"><span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs">{{ \App\Models\Project::STATUSES[$p->status] }}</span></td>
                        <td class="py-2">
                            <div class="h-2 w-28 overflow-hidden rounded-full bg-slate-100">
                                <div class="h-2 rounded-full bg-indigo-500" style="width: {{ $p->progress_percent }}%"></div>
                            </div>
                        </td>
                        <td class="py-2 text-right">
                            @if ($canManage)
                                <button wire:click="openEdit({{ $p->id }})" class="text-indigo-600 hover:underline">Edit</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-4 text-center text-slate-400">Belum ada proyek.</td></tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">{{ $projects->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showModal', false)">
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-slate-800">{{ $editingId ? 'Edit Proyek' : 'Tambah Proyek' }}</h3>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama Proyek</label>
                        <input type="text" wire:model="name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Lokasi (Unit)</label>
                        <select wire:model="unitId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- pilih unit --</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}">{{ $unit->region->code }} - {{ $unit->name }}</option>
                            @endforeach
                        </select>
                        @error('unitId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">PIC</label>
                        <select wire:model="picUserId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- tanpa PIC --</option>
                            @foreach ($pics as $pic)
                                <option value="{{ $pic->id }}">{{ $pic->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal Mulai</label>
                            <input type="date" wire:model="startDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal Selesai</label>
                            <input type="date" wire:model="endDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @error('endDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Status</label>
                        <select wire:model="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            @foreach (\App\Models\Project::STATUSES as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Deskripsi</label>
                        <textarea wire:model="description" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">Batal</button>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
