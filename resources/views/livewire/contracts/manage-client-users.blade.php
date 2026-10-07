<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <a href="{{ route('contracts.customers') }}" class="mb-2 inline-flex items-center gap-1 text-sm text-slate-500 hover:text-indigo-600">
                <x-icon name="arrow-left" class="h-3.5 w-3.5" /> Kembali ke daftar customer
            </a>
            <x-page-header icon="users" color="sky" title="Akun Client Portal" :subtitle="'Customer: ' . $customer->name" />
        </div>
        <button wire:click="openCreate" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-500">
            <x-icon name="plus" class="h-4 w-4" /> Tambah Akun
        </button>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">Nama</th>
                        <th class="pb-2">Email Login</th>
                        <th class="pb-2">Status</th>
                        <th class="pb-2">Login Terakhir</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clientUsers as $clientUser)
                        <tr class="border-t border-slate-100">
                            <td class="py-2 font-medium">{{ $clientUser->name }}</td>
                            <td class="py-2 text-slate-500">{{ $clientUser->email }}</td>
                            <td class="py-2">
                                @if ($clientUser->is_active)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-200 px-2 py-0.5 text-xs text-slate-600">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-2 text-slate-500">{{ $clientUser->last_login_at?->translatedFormat('d M Y H:i') ?? 'Belum pernah login' }}</td>
                            <td class="py-2 text-right">
                                <button wire:click="toggleActive({{ $clientUser->id }})" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-slate-500 transition-colors hover:bg-slate-100">
                                    {{ $clientUser->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                                <button wire:click="openEdit({{ $clientUser->id }})" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 transition-colors hover:bg-indigo-50">
                                    <x-icon name="edit" class="h-3.5 w-3.5" /> Edit
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-empty-state icon="users" title="Belum ada akun Client Portal untuk customer ini." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showModal', false)">
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="{{ $editingId ? 'edit' : 'plus-circle' }}" class="h-5 w-5 text-indigo-500" />
                    {{ $editingId ? 'Edit Akun Client' : 'Tambah Akun Client' }}
                </h3>
                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama</label>
                        <input type="text" wire:model="name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Email (dipakai untuk login)</label>
                        <input type="email" wire:model="email" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">
                            Password {{ $editingId ? '(kosongkan jika tidak ingin mengubah)' : '' }}
                        </label>
                        <input type="password" wire:model="password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('password') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model="isActive"> Akun aktif
                    </label>
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
