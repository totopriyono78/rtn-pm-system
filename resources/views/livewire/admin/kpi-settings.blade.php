<div class="space-y-6">
    <x-page-header icon="sliders" color="violet" title="Pengaturan KPI" subtitle="Khusus Administrator — atur cara Dashboard KPI menghitung & menandai jam kerja karyawan." />

    <form wire:submit="save" class="space-y-6">

        {{-- ===== Mode pengukuran ===== --}}
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h3 class="mb-1 text-sm font-semibold text-slate-700">Mode Pengukuran</h3>
            <p class="mb-4 text-xs text-slate-500">Menentukan basis pembanding untuk menandai karyawan yang jam kerjanya kurang.</p>

            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                @foreach ($modes as $key => $label)
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border p-4 transition-colors {{ $mode === $key ? 'border-indigo-500 bg-indigo-50/50' : 'border-slate-200 hover:bg-slate-50' }}">
                        <input type="radio" wire:model.live="mode" value="{{ $key }}" class="mt-1 text-indigo-600 focus:ring-indigo-500/30">
                        <span>
                            <span class="block text-sm font-medium text-slate-800">{{ $label }}</span>
                            <span class="block text-xs text-slate-500">
                                @if ($key === 'average')
                                    Karyawan dibandingkan terhadap rata-rata jam kerja tim saat ini (dinamis, ikut naik/turun mengikuti performa tim).
                                @else
                                    Karyawan dibandingkan terhadap angka baku yang ditentukan perusahaan di bawah ini (tidak berubah walau performa tim berubah).
                                @endif
                            </span>
                        </span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- ===== Target jam kerja ===== --}}
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h3 class="mb-1 text-sm font-semibold text-slate-700">Target Jam Kerja Minimal</h3>
            <p class="mb-4 text-xs text-slate-500">
                @if ($mode === 'target')
                    Karyawan dengan jam kerja di bawah angka ini akan ditandai "kurang" di Dashboard KPI.
                @else
                    Hanya dipakai saat mode "Target Tetap" dipilih. Saat ini tidak aktif (mode masih Rata-rata Tim).
                @endif
            </p>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 {{ $mode !== 'target' ? 'opacity-40' : '' }}">
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Per Hari (jam)</label>
                    <input type="number" step="0.5" min="0" max="24" wire:model="minHoursDay" @disabled($mode !== 'target')
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm disabled:bg-slate-50">
                    @error('minHoursDay') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Per Minggu (jam)</label>
                    <input type="number" step="0.5" min="0" max="168" wire:model="minHoursWeek" @disabled($mode !== 'target')
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm disabled:bg-slate-50">
                    @error('minHoursWeek') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-slate-700">Per Bulan (jam)</label>
                    <input type="number" step="0.5" min="0" max="744" wire:model="minHoursMonth" @disabled($mode !== 'target')
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm disabled:bg-slate-50">
                    @error('minHoursMonth') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>

        {{-- ===== Ambang rata-rata (hanya mode average) ===== --}}
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h3 class="mb-1 text-sm font-semibold text-slate-700">Ambang Toleransi Rata-rata</h3>
            <p class="mb-4 text-xs text-slate-500">
                @if ($mode === 'average')
                    Karyawan baru ditandai "di bawah rata-rata" kalau jam kerjanya kurang dari sekian persen rata-rata tim. 100% = langsung ditandai begitu di bawah rata-rata persis; 80% = diberi toleransi, baru ditandai kalau di bawah 80% rata-rata.
                @else
                    Hanya dipakai saat mode "Rata-rata Tim" dipilih. Saat ini tidak aktif (mode masih Target Tetap).
                @endif
            </p>

            <div class="max-w-xs {{ $mode !== 'average' ? 'opacity-40' : '' }}">
                <label class="mb-1.5 block text-sm font-medium text-slate-700">Ambang (%)</label>
                <div class="relative">
                    <input type="number" step="1" min="1" max="100" wire:model="averageMarginPercent" @disabled($mode !== 'average')
                        class="w-full rounded-lg border border-slate-300 px-3 py-2 pr-9 text-sm disabled:bg-slate-50">
                    <span class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-sm text-slate-400">%</span>
                </div>
                @error('averageMarginPercent') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- ===== Opsi lain ===== --}}
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h3 class="mb-4 text-sm font-semibold text-slate-700">Opsi Lain</h3>

            <div class="space-y-4">
                <label class="flex items-start gap-3">
                    <input type="checkbox" wire:model="includeZeroHourEmployees" class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/30">
                    <span>
                        <span class="block text-sm font-medium text-slate-700">Sertakan karyawan yang belum lapor sama sekali (0 jam)</span>
                        <span class="block text-xs text-slate-500">Kalau dimatikan (default), karyawan yang belum pernah lapor pada periode berjalan tidak muncul di daftar & tidak ikut menghitung rata-rata tim.</span>
                    </span>
                </label>

                <label class="flex items-start gap-3">
                    <input type="checkbox" wire:model="showThresholdBadges" class="mt-0.5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500/30">
                    <span>
                        <span class="block text-sm font-medium text-slate-700">Tampilkan penanda "di bawah target/rata-rata" di Dashboard KPI</span>
                        <span class="block text-xs text-slate-500">Matikan kalau hanya ingin menampilkan angka jam kerja tanpa penyorotan otomatis.</span>
                    </span>
                </label>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <p class="text-xs text-slate-400">
                @if ($setting->updatedBy)
                    Terakhir diubah oleh {{ $setting->updatedBy->name }}, {{ $setting->updated_at->translatedFormat('d M Y H:i') }}.
                @else
                    Belum pernah diubah — masih memakai nilai default.
                @endif
            </p>
            <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-indigo-500">
                <x-icon name="check" class="h-4 w-4" /> Simpan Pengaturan
            </button>
        </div>
    </form>
</div>
