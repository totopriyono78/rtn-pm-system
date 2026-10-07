<div class="space-y-6">
    <x-page-header icon="shield" color="violet" title="Kelola Role & Permission" subtitle="Atur sendiri menu/fitur apa saja yang bisa diakses tiap role -- tersimpan langsung, tanpa perlu ubah kode." />

    <div class="rounded-xl bg-amber-50 p-4 text-sm text-amber-800">
        <x-icon name="alert-circle" class="mr-1 inline h-4 w-4" />
        Centang = role tersebut punya akses ke menu/fitur itu. Perubahan tersimpan otomatis begitu dicentang/dihapus, berlaku untuk semua user yang punya role tersebut.
    </div>

    <div class="overflow-x-auto rounded-xl bg-white shadow-sm">
        <table class="w-full border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-slate-200">
                    <th class="sticky left-0 z-10 min-w-[260px] bg-white py-3 pl-5 pr-3 text-xs font-semibold uppercase tracking-wider text-slate-400">
                        Menu / Fitur (Permission)
                    </th>
                    @foreach ($roles as $role)
                        <th class="min-w-[110px] px-2 py-3 text-center text-xs font-semibold text-slate-600">
                            <span class="block">{{ $role->name }}</span>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($groupedPermissions as $groupLabel => $permissionNames)
                    <tr class="bg-slate-50">
                        <td colspan="{{ $roles->count() + 1 }}" class="sticky left-0 bg-slate-50 py-1.5 pl-5 text-xs font-semibold uppercase tracking-wider text-slate-500">
                            {{ $groupLabel }}
                        </td>
                    </tr>
                    @foreach ($permissionNames as $permissionName)
                        <tr class="border-b border-slate-100 transition-colors hover:bg-slate-50/70" wire:key="perm-row-{{ $permissionName }}">
                            <td class="sticky left-0 z-10 bg-white py-2 pl-5 pr-3">
                                <div class="font-medium text-slate-700">{{ $permissionName }}</div>
                                <div class="text-xs text-slate-400">{{ $descriptions[$permissionName] ?? '' }}</div>
                            </td>
                            @foreach ($roles as $role)
                                <td class="px-2 py-2 text-center" wire:key="cell-{{ $role->id }}-{{ $permissionName }}">
                                    <input type="checkbox"
                                        wire:click="togglePermission({{ $role->id }}, '{{ $permissionName }}')"
                                        @checked(($matrix[$role->id][$permissionName] ?? false))
                                        class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
        </table>
    </div>
</div>
