<div class="space-y-6">
    <div class="flex items-center justify-between">
        <x-page-header icon="users" color="violet" title="Kelola User" subtitle="Tambah, edit, nonaktifkan akun, dan atur clearance per individu." />
        <button wire:click="openCreate" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-indigo-500">
            <x-icon name="plus" class="h-4 w-4" /> Tambah User
        </button>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <div class="relative mb-4 max-w-sm">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <x-icon name="search" class="h-4 w-4" />
            </span>
            <input wire:model.live.debounce.400ms="search" type="text" placeholder="Cari nama / email..."
                class="w-full rounded-lg border border-slate-300 py-2 pl-9 pr-3 text-sm">
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="text-xs uppercase text-slate-400">
                    <tr>
                        <th class="pb-2">Nama</th>
                        <th class="pb-2">Email</th>
                        <th class="pb-2">Role</th>
                        <th class="pb-2">Region</th>
                        <th class="pb-2">Status</th>
                        <th class="pb-2 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr class="border-t border-slate-100 transition-colors hover:bg-slate-50/70">
                            <td class="py-2">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-xs font-semibold text-indigo-600">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <span class="font-medium text-slate-800">{{ $user->name }}</span>
                                </div>
                            </td>
                            <td class="py-2 text-slate-500">{{ $user->email }}</td>
                            <td class="py-2">{{ $user->roles->pluck('name')->join(', ') ?: '-' }}</td>
                            <td class="py-2 text-slate-500">{{ $user->regions->pluck('code')->join(', ') ?: 'Semua' }}</td>
                            <td class="py-2">
                                @if ($user->is_active)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-700">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-slate-200 px-2 py-0.5 text-xs text-slate-600">
                                        <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span> Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-2 text-right">
                                <button wire:click="openEdit({{ $user->id }})" class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-indigo-600 transition-colors hover:bg-indigo-50">
                                    <x-icon name="edit" class="h-3.5 w-3.5" /> Edit
                                </button>
                                <button wire:click="toggleActive({{ $user->id }})" wire:confirm="Yakin ingin mengubah status akun ini?"
                                    class="inline-flex items-center gap-1 rounded-lg px-2 py-1 text-xs font-medium text-slate-500 transition-colors hover:bg-slate-100">
                                    <x-icon name="{{ $user->is_active ? 'x-circle' : 'check' }}" class="h-3.5 w-3.5" />
                                    {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state icon="users" title="Belum ada user." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $users->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showModal', false)">
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 flex items-center gap-2 text-lg font-semibold text-slate-800">
                    <x-icon name="{{ $editingId ? 'edit' : 'user-plus' }}" class="h-5 w-5 text-indigo-500" />
                    {{ $editingId ? 'Edit User' : 'Tambah User' }}
                </h3>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Nama</label>
                        <input type="text" wire:model="name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                        <input type="email" wire:model="email" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('email') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">
                            Password {{ $editingId ? '(kosongkan jika tidak diubah)' : '' }}
                        </label>
                        <input type="password" wire:model="password" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                        @error('password') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Role</label>
                        <select wire:model="role" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                            <option value="">-- pilih role --</option>
                            @foreach ($roles as $r)
                                <option value="{{ $r->name }}">{{ $r->name }}</option>
                            @endforeach
                        </select>
                        @error('role') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Akses Region</label>
                        <p class="mb-1 text-xs text-slate-400">Kosongkan semua jika user boleh melihat semua region (mis. Direktur / Administrator).</p>
                        <div class="flex flex-wrap gap-3">
                            @foreach ($regions as $region)
                                <label class="flex items-center gap-1 text-sm">
                                    <input type="checkbox" value="{{ $region->id }}" wire:model="selectedRegions">
                                    {{ $region->code }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="mb-1 block text-sm font-medium text-slate-700">Permission Tambahan (Override)</label>
                        <p class="mb-1 text-xs text-slate-400">Centang permission ekstra di luar default role, untuk clearance khusus per individu.</p>
                        <div class="grid grid-cols-1 gap-1 sm:grid-cols-2">
                            @foreach ($allPermissions as $perm)
                                <label class="flex items-center gap-1 text-sm">
                                    <input type="checkbox" value="{{ $perm->name }}" wire:model="extraPermissions">
                                    {{ $perm->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" wire:model="is_active">
                        Akun aktif
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
