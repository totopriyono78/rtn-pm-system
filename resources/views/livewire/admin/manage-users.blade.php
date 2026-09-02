<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-slate-800">Kelola User</h2>
            <p class="text-sm text-slate-500">Tambah, edit, nonaktifkan akun, dan atur clearance per individu.</p>
        </div>
        <button wire:click="openCreate" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
            + Tambah User
        </button>
    </div>

    <div class="rounded-xl bg-white p-5 shadow-sm">
        <input wire:model.live.debounce.400ms="search" type="text" placeholder="Cari nama / email..."
            class="mb-4 w-full max-w-sm rounded-lg border border-slate-300 px-3 py-2 text-sm">

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
                @foreach ($users as $user)
                    <tr class="border-t border-slate-100">
                        <td class="py-2 font-medium text-slate-800">{{ $user->name }}</td>
                        <td class="py-2 text-slate-500">{{ $user->email }}</td>
                        <td class="py-2">{{ $user->roles->pluck('name')->join(', ') ?: '-' }}</td>
                        <td class="py-2 text-slate-500">{{ $user->regions->pluck('code')->join(', ') ?: 'Semua' }}</td>
                        <td class="py-2">
                            @if ($user->is_active)
                                <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs text-green-700">Aktif</span>
                            @else
                                <span class="rounded-full bg-slate-200 px-2 py-0.5 text-xs text-slate-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="py-2 text-right space-x-2">
                            <button wire:click="openEdit({{ $user->id }})" class="text-indigo-600 hover:underline">Edit</button>
                            <button wire:click="toggleActive({{ $user->id }})" wire:confirm="Yakin ingin mengubah status akun ini?" class="text-slate-500 hover:underline">
                                {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">{{ $users->links() }}</div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4" wire:click.self="$set('showModal', false)">
            <div class="max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl bg-white p-6 shadow-xl">
                <h3 class="mb-4 text-lg font-semibold text-slate-800">{{ $editingId ? 'Edit User' : 'Tambah User' }}</h3>

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
                        <button type="button" wire:click="$set('showModal', false)" class="rounded-lg border border-slate-300 px-4 py-2 text-sm">Batal</button>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
