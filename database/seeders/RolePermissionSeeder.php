<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Daftar permission granular sesuai SRS bab 4.1 (dapat diperluas dari halaman Admin).
     */
    public const PERMISSIONS = [
        'manage-users' => 'Kelola user, role, dan clearance per individu',
        'view-all-project' => 'Melihat proyek lintas region (VIEW_ALL_PROJECT)',
        'manage-projects' => 'Membuat/mengubah proyek dan activity',
        'view-reports' => 'Melihat laporan teknisi',
        'submit-report' => 'Mengisi & upload laporan harian/akhir',
        'view-kpi-team' => 'Melihat KPI dan jam kerja seluruh tim (VIEW_KPI_TEAM)',
        'manage-purchasing' => 'Kelola master item, vendor, RFQ, dan penawaran vendor',
        'approve-purchasing' => 'Menyetujui hasil pemilihan vendor & menerbitkan PO (APPROVE_PENAWARAN)',
        'view-purchasing' => 'Melihat data pengadaan / status material',
        'view-harga' => 'Melihat harga di penawaran dan BOQ (VIEW_HARGA)',
        'manage-material-tracking' => 'Mengubah status tracking material',
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $name => $description) {
            Permission::findOrCreate($name, 'web');
        }

        $administrator = Role::findOrCreate('Administrator', 'web');
        $administrator->syncPermissions(array_keys(self::PERMISSIONS));

        $direktur = Role::findOrCreate('Direktur', 'web');
        $direktur->syncPermissions([
            'view-all-project',
            'view-reports',
            'view-kpi-team',
            'view-purchasing',
            'view-harga',
            'approve-purchasing',
        ]);

        $pm = Role::findOrCreate('Project Manager', 'web');
        $pm->syncPermissions([
            'manage-projects',
            'view-reports',
            'view-purchasing',
        ]);

        $purchasing = Role::findOrCreate('Purchasing', 'web');
        $purchasing->syncPermissions([
            'manage-purchasing',
            'view-purchasing',
            'view-harga',
            'manage-material-tracking',
        ]);

        $teknisi = Role::findOrCreate('Teknisi', 'web');
        $teknisi->syncPermissions([
            'submit-report',
        ]);

        // Role "Management": dikonfigurasi sesuai kebutuhan perusahaan, tanpa default permission.
        Role::findOrCreate('Management', 'web');
    }
}
