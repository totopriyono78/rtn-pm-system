<?php

namespace App\Livewire\Admin;

use Database\Seeders\RolePermissionSeeder;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Halaman "Kelola Role & Permission" -- dibangun 2026-10-07 sebagai jawaban
 * atas pertanyaan user: apakah role->menu bisa dibuat configurable (bukan
 * hardcode di seeder), supaya misalnya akses menu Accounting bisa dicabut
 * dari Administrator kapan saja tanpa minta developer ubah kode lagi.
 *
 * Sistem SUDAH berbasis permission granular sejak awal (setiap menu di
 * sidebar-nav.blade.php dan setiap fitur dicek lewat @can('nama-permission'),
 * bukan hardcode nama role) -- yang belum ada cuma UI untuk mengubah
 * pemetaan role->permission itu sendiri (sebelumnya cuma lewat
 * RolePermissionSeeder.php). Halaman ini mengisi gap itu: admin bisa
 * centang/hapus sendiri lewat matrix di bawah, tersimpan langsung ke DB
 * (tabel role_has_permissions milik package spatie/laravel-permission),
 * tanpa perlu re-deploy kode.
 */
#[Layout('layouts.app')]
class ManageRolePermissions extends Component
{
    public function render()
    {
        $roles = Role::orderBy('name')->get();

        $permissionNames = Permission::orderBy('name')->pluck('name')->all();

        // Grouping untuk tampilan (RolePermissionSeeder::PERMISSION_GROUPS).
        // Permission yang belum masuk grup manapun (mis. ditambah manual lewat
        // tinker/migration lain) tetap ditampilkan di grup "Lainnya" supaya
        // tidak hilang dari matrix.
        $grouped = [];
        $assigned = [];
        foreach (RolePermissionSeeder::PERMISSION_GROUPS as $groupLabel => $names) {
            $inGroup = array_values(array_intersect($names, $permissionNames));
            if (empty($inGroup)) {
                continue;
            }
            $grouped[$groupLabel] = $inGroup;
            $assigned = array_merge($assigned, $inGroup);
        }
        $ungrouped = array_values(array_diff($permissionNames, $assigned));
        if (! empty($ungrouped)) {
            $grouped['Lainnya'] = $ungrouped;
        }

        // Matrix roleId => [permissionName => bool] supaya view tinggal baca,
        // tidak query ulang per cell.
        $matrix = [];
        foreach ($roles as $role) {
            $matrix[$role->id] = $role->permissions->pluck('name')->flip()->map(fn () => true)->all();
        }

        return view('livewire.admin.manage-role-permissions', [
            'roles' => $roles,
            'groupedPermissions' => $grouped,
            'descriptions' => RolePermissionSeeder::PERMISSIONS,
            'matrix' => $matrix,
        ]);
    }

    public function togglePermission(int $roleId, string $permissionName): void
    {
        $role = Role::findOrFail($roleId);
        $has = $role->hasPermissionTo($permissionName);

        if ($has) {
            // Safeguard: jangan sampai TIDAK ADA role manapun yang pegang
            // 'manage-roles-permissions' -- kalau itu terjadi, tidak akan ada
            // yang bisa buka/ubah halaman ini lagi lewat UI (perlu tinker
            // manual di server untuk pulihkan).
            if ($permissionName === 'manage-roles-permissions') {
                $holders = Role::permission($permissionName)->count();
                if ($holders <= 1) {
                    session()->flash('error', 'Tidak bisa mencabut permission ini dari role terakhir yang memilikinya -- nanti tidak ada role yang bisa membuka halaman Kelola Role & Permission ini lagi.');

                    return;
                }
            }

            $role->revokePermissionTo($permissionName);
        } else {
            $role->givePermissionTo($permissionName);
        }
    }
}
