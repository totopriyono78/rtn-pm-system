<div class="max-w-2xl space-y-6">
    <x-page-header icon="edit" color="violet" title="Tanda Tangan Digital" subtitle="Tanda tangan default Anda dipakai otomatis saat menandatangani dokumen (mis. Penawaran ke Customer). Paraf digital internal — bukan tanda tangan elektronik bersertifikat pihak ketiga." />

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-700">Tanda Tangan Tersimpan</h3>
            <button wire:click="$set('showDrawModal', true)" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">
                <x-icon name="edit" class="h-3.5 w-3.5" /> Gambar Baru
            </button>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
            @forelse ($signatures as $sig)
                <div class="relative rounded-lg border p-3 {{ $sig->is_default ? 'border-indigo-400 bg-indigo-50/40' : 'border-slate-200' }}">
                    <img src="{{ route('signatures.show', $sig) }}" alt="Tanda tangan" class="h-16 w-full object-contain">
                    <div class="mt-2 flex items-center justify-between text-[11px]">
                        <span class="text-slate-400">{{ \App\Models\UserSignature::TYPES[$sig->type] }}</span>
                        @if ($sig->is_default)
                            <span class="rounded-full bg-indigo-100 px-1.5 py-0.5 font-medium text-indigo-700">Default</span>
                        @else
                            <button wire:click="setDefault({{ $sig->id }})" class="text-indigo-600 hover:underline">Jadikan Default</button>
                        @endif
                    </div>
                    <button wire:click="delete({{ $sig->id }})" wire:confirm="Hapus tanda tangan ini?" class="absolute right-1.5 top-1.5 rounded-full bg-white/80 p-1 text-red-500 hover:bg-red-50">
                        <x-icon name="trash" class="h-3.5 w-3.5" />
                    </button>
                </div>
            @empty
                <div class="col-span-full">
                    <x-empty-state icon="edit" title="Belum ada tanda tangan tersimpan." />
                </div>
            @endforelse
        </div>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <h3 class="mb-3 text-sm font-semibold text-slate-700">Atau Upload File Tanda Tangan</h3>
        <form wire:submit="saveUploadedSignature" class="flex items-end gap-3">
            <div class="flex-1">
                <input type="file" wire:model="uploadFile" accept="image/*"
                    class="block w-full cursor-pointer rounded-lg border border-slate-300 p-2 text-sm text-slate-500 file:mr-4 file:cursor-pointer file:rounded-lg file:border-0 file:bg-indigo-600 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-indigo-500">
                @error('uploadFile') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>
            <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Upload</button>
        </form>
    </div>

    @if ($showDrawModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showDrawModal', false)">
            <div class="w-full max-w-lg rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-slate-800">Gambar Tanda Tangan</h3>
                <div
                    x-data="{
                        drawing: false,
                        ctx: null,
                        init() {
                            this.ctx = this.$refs.canvas.getContext('2d');
                            this.ctx.lineWidth = 2.5;
                            this.ctx.lineCap = 'round';
                            this.ctx.strokeStyle = '#1e293b';
                        },
                        pos(e) {
                            const rect = this.$refs.canvas.getBoundingClientRect();
                            const t = e.touches ? e.touches[0] : e;
                            return { x: t.clientX - rect.left, y: t.clientY - rect.top };
                        },
                        start(e) { this.drawing = true; const p = this.pos(e); this.ctx.beginPath(); this.ctx.moveTo(p.x, p.y); },
                        move(e) { if (!this.drawing) return; const p = this.pos(e); this.ctx.lineTo(p.x, p.y); this.ctx.stroke(); },
                        end() { this.drawing = false; },
                        clear() { this.ctx.clearRect(0, 0, this.$refs.canvas.width, this.$refs.canvas.height); },
                        save() { $wire.saveDrawnSignature(this.$refs.canvas.toDataURL('image/png')); }
                    }"
                >
                    <canvas x-ref="canvas" width="440" height="180"
                        @mousedown="start($event)" @mousemove="move($event)" @mouseup="end" @mouseleave="end"
                        @touchstart.prevent="start($event)" @touchmove.prevent="move($event)" @touchend="end"
                        class="w-full touch-none rounded-lg border border-slate-300 bg-white"></canvas>
                    <p class="mt-2 text-xs text-slate-400">Gambar tanda tangan Anda di kotak di atas (mouse atau jari di layar sentuh).</p>
                    <div class="mt-4 flex justify-end gap-2">
                        <button type="button" @click="clear" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Hapus Coretan</button>
                        <button type="button" wire:click="$set('showDrawModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="button" @click="save" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                            <x-icon name="check" class="h-4 w-4" /> Simpan
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
