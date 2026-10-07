<div class="space-y-6">
    <div class="flex items-center justify-between">
        <x-page-header icon="doc-text" color="indigo" title="Portofolio (Company Website)" subtitle="Daftar proyek unggulan yang tampil di halaman Portofolio." />
        <button wire:click="openCreate" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-500">
            <x-icon name="plus" class="h-4 w-4" /> Tambah Portofolio
        </button>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="relative mb-4 max-w-sm">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <x-icon name="search" class="h-4 w-4" />
            </span>
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Cari judul proyek..." class="w-full rounded-lg border border-slate-300 py-2 pl-9 pr-3 text-sm">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">Gambar</th>
                        <th class="pb-2">Judul</th>
                        <th class="pb-2">Kategori</th>
                        <th class="pb-2">Tahun</th>
                        <th class="pb-2">Status</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2">
                                @if ($item->image_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($item->image_path) }}" class="h-10 w-10 rounded-lg object-cover" alt="{{ $item->title }}">
                                @else
                                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-slate-100 text-slate-400"><x-icon name="doc-text" class="h-5 w-5" /></div>
                                @endif
                            </td>
                            <td class="py-2 font-medium">{{ $item->title }}</td>
                            <td class="py-2 text-slate-500">{{ $item->category ?: '—' }}</td>
                            <td class="py-2 text-slate-500">{{ $item->year ?: '—' }}</td>
                            <td class="py-2">
                                @if ($item->is_published)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-700"><span class="h-1.5 w-1.5 rounded-full bg-green-500"></span> Tayang</span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-200 px-2 py-0.5 text-xs text-slate-600"><span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span> Draft</span>
                                @endif
                            </td>
                            <td class="py-2 text-right">
                                <button wire:click="openEdit({{ $item->id }})" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 transition-colors hover:bg-indigo-50">
                                    <x-icon name="edit" class="h-3.5 w-3.5" /> Edit
                                </button>
                                <button wire:click="delete({{ $item->id }})" wire:confirm="Hapus portofolio ini?" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-red-600 transition-colors hover:bg-red-50">
                                    <x-icon name="trash" class="h-3.5 w-3.5" /> Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state icon="doc-text" title="Belum ada portofolio." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $items->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/40 p-4" wire:click.self="$set('showModal', false)">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="{{ $editingId ? 'edit' : 'plus-circle' }}" class="h-5 w-5 text-indigo-500" />
                    {{ $editingId ? 'Edit Portofolio' : 'Tambah Portofolio' }}
                </h3>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Judul Proyek</label>
                        <input type="text" wire:model="title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('title') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Nama Klien (opsional)</label>
                            <input type="text" wire:model="clientName" placeholder="Boleh dikosongkan/disamarkan" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Kategori</label>
                            <input type="text" wire:model="category" placeholder="mis. Migas, Perkebunan" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Tahun</label>
                        <input type="number" wire:model="year" min="2000" max="2100" class="w-full max-w-[140px] rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('year') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Deskripsi</label>
                        <textarea wire:model="description" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Gambar</label>
                        <input type="file" wire:model="imageUpload" accept="image/*" class="block w-full text-sm">
                        @error('imageUpload') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Urutan Tampil</label>
                            <input type="number" min="0" wire:model="sortOrder" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <label class="mt-6 flex items-center gap-2 text-sm">
                            <input type="checkbox" wire:model="isPublished"> Tayang di website
                        </label>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                            <x-icon name="check" class="h-4 w-4" /> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
