<div class="space-y-6">
    <div class="flex items-center justify-between">
        <x-page-header icon="sliders" color="indigo" title="Slide Beranda (Company Website)" subtitle="Konten slide yang bergeser otomatis di bagian atas halaman Beranda -- bisa berisi teks dan/atau gambar." />
        <button wire:click="openCreate" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-500">
            <x-icon name="plus" class="h-4 w-4" /> Tambah Slide
        </button>
    </div>

    @if (session('success'))
        <div class="flex items-center gap-2 rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
            <x-icon name="check" class="h-4 w-4" /> {{ session('success') }}
        </div>
    @endif

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <p class="mb-4 text-xs text-slate-500">Slide ditampilkan berurutan sesuai "Urutan Tampil" (angka kecil tampil lebih dulu). Minimal satu slide harus "Tayang" agar carousel muncul di Beranda -- kalau tidak ada slide yang tayang, Beranda otomatis menampilkan hero standar.</p>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">Gambar</th>
                        <th class="pb-2">Judul</th>
                        <th class="pb-2">Tombol/Link</th>
                        <th class="pb-2">Urutan</th>
                        <th class="pb-2">Status</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($slides as $slide)
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2">
                                @if ($slide->image_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($slide->image_path) }}" class="h-10 w-16 rounded-lg object-cover" alt="{{ $slide->title }}">
                                @else
                                    <div class="flex h-10 w-16 items-center justify-center rounded-lg bg-slate-100 text-slate-400"><x-icon name="cube" class="h-5 w-5" /></div>
                                @endif
                            </td>
                            <td class="py-2">
                                <div class="font-medium">{{ $slide->title ?: '—' }}</div>
                                @if ($slide->subtitle)
                                    <div class="max-w-xs truncate text-xs text-slate-500">{{ $slide->subtitle }}</div>
                                @endif
                            </td>
                            <td class="py-2 text-slate-500">{{ $slide->link_label ?: '—' }}</td>
                            <td class="py-2 text-slate-500">{{ $slide->sort_order }}</td>
                            <td class="py-2">
                                @if ($slide->is_published)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-700"><span class="h-1.5 w-1.5 rounded-full bg-green-500"></span> Tayang</span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-200 px-2 py-0.5 text-xs text-slate-600"><span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span> Draft</span>
                                @endif
                            </td>
                            <td class="py-2 text-right">
                                <button wire:click="openEdit({{ $slide->id }})" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 transition-colors hover:bg-indigo-50">
                                    <x-icon name="edit" class="h-3.5 w-3.5" /> Edit
                                </button>
                                <button wire:click="delete({{ $slide->id }})" wire:confirm="Hapus slide ini?" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-red-600 transition-colors hover:bg-red-50">
                                    <x-icon name="trash" class="h-3.5 w-3.5" /> Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state icon="sliders" title="Belum ada slide. Beranda akan menampilkan hero standar." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/40 p-4" wire:click.self="$set('showModal', false)">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="{{ $editingId ? 'edit' : 'plus-circle' }}" class="h-5 w-5 text-indigo-500" />
                    {{ $editingId ? 'Edit Slide' : 'Tambah Slide' }}
                </h3>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Judul (opsional)</label>
                        <input type="text" wire:model="title" placeholder="mis. Preventive Maintenance Terjadwal" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('title') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Subjudul / Teks Informasi (opsional)</label>
                        <textarea wire:model="subtitle" rows="2" placeholder="Kalimat singkat pendukung judul" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('subtitle') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">Label Tombol (opsional)</label>
                            <input type="text" wire:model="linkLabel" placeholder="mis. Lihat Layanan" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">URL Tombol (opsional)</label>
                            <input type="text" wire:model="linkUrl" placeholder="/layanan atau https://..." class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Gambar {{ $editingId ? '(kosongkan jika tidak diganti)' : '' }}</label>
                        <input type="file" wire:model="imageUpload" accept="image/*" class="block w-full text-sm">
                        <p class="mt-1 text-xs text-slate-400">Disarankan gambar landscape (mis. 1600x900px) agar tidak terpotong di layar lebar.</p>
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
