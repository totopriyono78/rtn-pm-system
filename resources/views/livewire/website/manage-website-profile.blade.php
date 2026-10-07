<div class="space-y-6">
    <x-page-header icon="building" color="indigo" title="Profil Company Website" subtitle="Konten dasar yang tampil di seluruh halaman Company Website publik." />

    <form wire:submit="save" class="space-y-6">
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-slate-800">Identitas Perusahaan</h3>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Nama Perusahaan</label>
                    <input type="text" wire:model="companyName" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @error('companyName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Tagline</label>
                    <input type="text" wire:model="tagline" placeholder="mis. Solusi Terpercaya Perawatan & Pengadaan Pompa Industri" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Logo</label>
                    @if ($settings->logo_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($settings->logo_path) }}" class="mb-2 h-12 w-auto rounded border border-slate-200 bg-slate-50 p-1" alt="Logo saat ini">
                    @endif
                    <input type="file" wire:model="logoUpload" accept="image/*" class="block w-full text-sm">
                    @error('logoUpload') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Gambar Hero (Beranda)</label>
                    @if ($settings->hero_image_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($settings->hero_image_path) }}" class="mb-2 h-20 w-full rounded border border-slate-200 object-cover" alt="Hero saat ini">
                    @endif
                    <input type="file" wire:model="heroImageUpload" accept="image/*" class="block w-full text-sm">
                    @error('heroImageUpload') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-slate-800">Hero Beranda</h3>
            <div class="space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Headline</label>
                    <input type="text" wire:model="heroHeadline" placeholder="mis. Partner Terpercaya untuk Keandalan Pompa Industri Anda" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Sub-headline</label>
                    <textarea wire:model="heroSubheadline" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                </div>
            </div>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-slate-800">Tentang Kami</h3>
            <div class="space-y-4">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Deskripsi Perusahaan</label>
                    <textarea wire:model="about" rows="4" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Visi</label>
                        <textarea wire:model="vision" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Misi</label>
                        <textarea wire:model="mission" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Satu poin per baris"></textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-slate-800">Kontak & Media Sosial</h3>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Alamat</label>
                    <input type="text" wire:model="address" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                    <input type="email" wire:model="email" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @error('email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Telepon</label>
                    <input type="text" wire:model="phone" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">WhatsApp</label>
                    <input type="text" wire:model="whatsapp" placeholder="62812xxxxxxx" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">Instagram URL</label>
                    <input type="text" wire:model="instagramUrl" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @error('instagramUrl') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-slate-700">LinkedIn URL</label>
                    <input type="text" wire:model="linkedinUrl" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    @error('linkedinUrl') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2">
            <a href="{{ url('/') }}" target="_blank" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">
                <x-icon name="eye" class="h-4 w-4" /> Lihat Website
            </a>
            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                <x-icon name="check" class="h-4 w-4" /> Simpan Perubahan
            </button>
        </div>
    </form>
</div>
