<div class="space-y-6">
    <x-page-header icon="user-plus" color="violet" :title="$prospect->company_name" :subtitle="$prospect->code.' · '.\App\Models\Prospect::STATUSES[$prospect->status]" />

    @if (session('success'))
        <div class="rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif

    @if ($prospect->isOpen())
        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h3 class="mb-3 text-sm font-semibold text-slate-700">Tahap Pipeline</h3>
            <div class="flex flex-wrap items-center gap-2">
                @foreach (\App\Models\Prospect::PIPELINE_STAGES as $stage)
                    <button
                        @if ($stage !== $prospect->status) wire:click="moveToStage('{{ $stage }}')" @endif
                        @class([
                            'rounded-full px-3 py-1.5 text-xs font-semibold transition-colors',
                            'bg-indigo-600 text-white' => $stage === $prospect->status,
                            'bg-slate-100 text-slate-500 hover:bg-slate-200' => $stage !== $prospect->status,
                        ])
                    >{{ \App\Models\Prospect::STATUSES[$stage] }}</button>
                    @if (!$loop->last)
                        <x-icon name="arrow-right" class="h-3.5 w-3.5 text-slate-300" />
                    @endif
                @endforeach
            </div>
            <div class="mt-4 flex gap-2 border-t border-slate-100 pt-4">
                <button wire:click="openWinModal" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-500">
                    <x-icon name="check" class="h-3.5 w-3.5" /> Tandai Won
                </button>
                <button wire:click="openLoseModal" class="inline-flex items-center gap-1.5 rounded-lg bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-100">
                    <x-icon name="x-circle" class="h-3.5 w-3.5" /> Tandai Lost
                </button>
            </div>
        </div>
    @elseif ($prospect->status === 'won')
        <div class="rounded-xl bg-emerald-50 p-5 text-sm text-emerald-800">
            <p class="font-medium">Prospect ini sudah menang dan dikonversi menjadi Customer
                <a href="#" class="underline">{{ $prospect->customer->name ?? '-' }}</a> pada {{ optional($prospect->won_at)->format('d M Y H:i') }}.
            </p>
            @can('manage-sales-orders')
                <a href="{{ route('sales.orders.index') }}" class="mt-2 inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3 py-2 text-xs font-semibold text-white hover:bg-emerald-500">
                    <x-icon name="clipboard-list" class="h-3.5 w-3.5" /> Buat Sales Order
                </a>
            @endcan
        </div>
    @else
        <div class="rounded-xl bg-red-50 p-5 text-sm text-red-700">
            <p class="font-medium">Prospect ini ditandai Lost pada {{ optional($prospect->lost_at)->format('d M Y H:i') }}.</p>
            @if ($prospect->lost_reason)
                <p class="mt-1 text-red-600">Alasan: {{ $prospect->lost_reason }}</p>
            @endif
        </div>
    @endif

    <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
        <div class="rounded-xl bg-white p-5 shadow-sm md:col-span-2">
            <h3 class="mb-3 text-sm font-semibold text-slate-700">Informasi Kontak</h3>
            <dl class="grid grid-cols-2 gap-3 text-sm">
                <div><dt class="text-xs text-slate-400">Nama Kontak (PIC)</dt><dd>{{ $prospect->contact_name ?? '-' }}</dd></div>
                <div><dt class="text-xs text-slate-400">No. Telepon</dt><dd>{{ $prospect->contact_phone ?? '-' }}</dd></div>
                <div><dt class="text-xs text-slate-400">Email</dt><dd>{{ $prospect->contact_email ?? '-' }}</dd></div>
                <div><dt class="text-xs text-slate-400">Sumber Lead</dt><dd>{{ $prospect->source ?? '-' }}</dd></div>
                <div class="col-span-2"><dt class="text-xs text-slate-400">Alamat</dt><dd class="whitespace-pre-line">{{ $prospect->address ?? '-' }}</dd></div>
            </dl>
            @if ($prospect->notes)
                <div class="mt-3 border-t border-slate-100 pt-3">
                    <dt class="text-xs text-slate-400">Catatan</dt>
                    <dd class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $prospect->notes }}</dd>
                </div>
            @endif
        </div>

        <div class="rounded-xl bg-white p-5 shadow-sm">
            <h3 class="mb-2 text-sm font-semibold text-slate-700">Estimasi Nilai</h3>
            <div class="text-2xl font-semibold text-slate-800">{{ $prospect->estimated_value ? 'Rp '.number_format((float) $prospect->estimated_value, 0, ',', '.') : '-' }}</div>
            <div class="mt-4 flex items-center justify-between border-t border-slate-100 pt-4 text-sm">
                <span class="text-slate-500">PIC Marketing</span>
                <span class="font-medium">{{ $prospect->assignee->name ?? '-' }}</span>
            </div>
            <button wire:click="openAssignModal" class="mt-2 text-xs font-semibold text-indigo-600 hover:underline">Ubah PIC</button>
        </div>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-slate-700">Log Aktivitas</h3>
            <button wire:click="openActivityModal" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500">
                <x-icon name="plus" class="h-3.5 w-3.5" /> Catat Aktivitas
            </button>
        </div>
        <div class="space-y-3">
            @forelse ($prospect->activities as $activity)
                <div class="flex gap-3 border-l-2 border-slate-100 pl-3">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 text-xs text-slate-400">
                            <span class="rounded-full bg-slate-100 px-2 py-0.5 font-semibold text-slate-600">{{ $activity->typeLabel() }}</span>
                            <span>{{ $activity->activity_date->format('d M Y H:i') }}</span>
                            <span>&middot; {{ $activity->user->name ?? 'Sistem' }}</span>
                        </div>
                        @if ($activity->type === 'stage_change')
                            <p class="mt-1 text-sm text-slate-700">
                                Pindah tahap: <span class="font-medium">{{ \App\Models\Prospect::STATUSES[$activity->from_status] ?? $activity->from_status }}</span>
                                &rarr; <span class="font-medium">{{ \App\Models\Prospect::STATUSES[$activity->to_status] ?? $activity->to_status }}</span>
                            </p>
                            @if ($activity->notes)
                                <p class="mt-0.5 text-xs text-slate-500">{{ $activity->notes }}</p>
                            @endif
                        @else
                            <p class="mt-1 whitespace-pre-line text-sm text-slate-700">{{ $activity->notes }}</p>
                        @endif
                    </div>
                </div>
            @empty
                <x-empty-state icon="clock" title="Belum ada aktivitas tercatat." />
            @endforelse
        </div>
    </div>

    @if ($showAssignModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showAssignModal', false)">
            <div class="w-full max-w-sm rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-slate-800">Ubah PIC Marketing</h3>
                <select wire:model="assignedTo" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="">-- belum ditentukan --</option>
                    @foreach ($marketingUsers as $u)
                        <option value="{{ $u->id }}">{{ $u->name }}</option>
                    @endforeach
                </select>
                <div class="mt-4 flex justify-end gap-2">
                    <button wire:click="$set('showAssignModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                    <button wire:click="saveAssign" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Simpan</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showActivityModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showActivityModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-slate-800">Catat Aktivitas</h3>
                <form wire:submit="saveActivity" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Jenis</label>
                        <select wire:model="activityType" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="call">Telepon</option>
                            <option value="meeting">Pertemuan</option>
                            <option value="email">Email</option>
                            <option value="note">Catatan</option>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Tanggal &amp; Waktu</label>
                        <input type="datetime-local" wire:model="activityDate" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('activityDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Catatan</label>
                        <textarea wire:model="activityNotes" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('activityNotes') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showActivityModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showWinModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showWinModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="check" class="h-5 w-5 text-emerald-500" /> Tandai Won
                </h3>
                <p class="mb-3 text-sm text-slate-500">Prospect akan dikonversi menjadi Customer baru, kecuali Anda memilih Customer yang sudah ada (mis. order ulang dari Customer lama).</p>
                <form wire:submit="win" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Hubungkan ke Customer yang sudah ada? (opsional)</label>
                        <select wire:model="winExistingCustomerId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- buat Customer baru dari data prospect ini --</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Catatan (opsional)</label>
                        <textarea wire:model="winNote" rows="2" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showWinModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-500">
                            <x-icon name="check" class="h-4 w-4" /> Konfirmasi Won
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    @if ($showLoseModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showLoseModal', false)">
            <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="x-circle" class="h-5 w-5 text-red-500" /> Tandai Lost
                </h3>
                <form wire:submit="lose" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Alasan</label>
                        <textarea wire:model="lostReason" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('lostReason') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" wire:click="$set('showLoseModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-50">Batal</button>
                        <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-500">Tandai Lost</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
