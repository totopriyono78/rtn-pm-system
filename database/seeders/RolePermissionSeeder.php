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
        'manage-kpi-settings' => 'Mengatur parameter perhitungan KPI (mode, target jam kerja, dsb) — khusus Administrator',
        'manage-customers' => 'Kelola master data Customer',
        'manage-contracts' => 'Kelola Contract, Release Order, dan Penawaran ke Customer (Project Controller)',
        'approve-quotation' => 'Menyetujui Penawaran ke Customer sebelum dianggap terkirim (Direktur)',
        'view-contract-value' => 'Melihat nilai kontrak, plafon kontrak payung, dan sisa plafon',
        'manage-attendance-overrides' => 'Mengesahkan presensi teknisi yang di luar radius site (PM/Administrator)',
        'manage-website' => 'Kelola konten Company Website (profil, produk, layanan, portofolio, pesan masuk)',
        'manage-pump-assets' => 'Kelola Master Unit Pompa (aset klien): data pompa terpasang & riwayat service',
        'view-weekly-recap' => 'Melihat rekap laporan mingguan, BAPP RO, dan BAL Bulanan per proyek (Admin Kantor)',
        'manage-delivery-gatepass' => 'Kelola surat jalan & gatepass, tracking ekspedisi (Admin Purchase)',
        'create-assignments' => 'Membuat usulan penugasan/jadwal teknisi, menunggu approval PM (Lead Technician)',
        'approve-assignments' => 'Menyetujui atau menolak usulan penugasan teknisi dari Lead Technician',
        'manage-invoices' => 'Membuat, mengirim, dan mencatat pelunasan invoice ke customer (Project Controller)',
        'request-cash-advance' => 'Mengajukan kasbon dan mengisi pertanggungjawaban pengeluaran (Teknisi/Lead Technician)',
        'manage-cash-advances' => 'Menyetujui, mencairkan, dan menutup kasbon teknisi (Project Manager)',
        'manage-payroll' => 'Mengelola komponen gaji karyawan dan menerbitkan Payroll/slip gaji bulanan — khusus Administrator',
        'manage-cash-bank' => 'Mengelola akun Kas/Bank dan mencatat transaksi (pelunasan invoice, pencairan kasbon, transaksi manual) — khusus Administrator',
        'manage-vendor-payments' => 'Mencatat pembayaran ke vendor atas Purchase Order yang sudah diterbitkan (Purchasing)',
        'view-financial-reports' => 'Melihat laporan keuangan ringkas (arus kas, piutang, hutang vendor, payroll) per periode — Direktur & Administrator',
        'manage-general-ledger' => 'Kelola Chart of Account dan mencatat/posting Jurnal Umum (Entry Voucher) — khusus Administrator',
        'manage-other-receivables' => 'Kelola Piutang Lain-lain (pinjaman karyawan, titipan vendor, dsb di luar Invoice) — khusus Administrator',
        'manage-fixed-assets' => 'Kelola register aset tetap perusahaan (kendaraan, peralatan) dan menjalankan depresiasi — khusus Administrator',
        'manage-budgets' => 'Membuat/mengubah anggaran tahunan per akun Chart of Account (draft) — khusus Administrator',
        'approve-budgets' => 'Menyetujui anggaran tahunan yang diajukan Administrator (Direktur)',
        'manage-prospects' => 'Kelola Prospect, pipeline CRM (prospek→qualified→proposal→negosiasi→won/lost), dan log aktivitas (Marketing)',
        'manage-sales-orders' => 'Kelola Sales Order & dokumen teknis, serta mengonfirmasinya jadi Contract/Project (Sales Support)',
        'view-sales-dashboard' => 'Melihat dashboard ringkasan pipeline penjualan & Sales Order',
        'manage-roles-permissions' => 'Mengelola pemetaan role ke permission/menu lewat halaman Kelola Role & Permission — khusus Administrator',
        'manage-cost-control' => 'Mengelola budget & actual cost per proyek secara real-time (Cost Control)',
    ];

    /**
     * Permission finansial yang menurut keputusan client (2026-10-07) HARUS
     * jadi role "Finance & Accounting" tersendiri, TIDAK digabung ke role
     * lain manapun -- termasuk dicabut dari Administrator (lihat
     * syncPermissions Administrator di bawah: array_diff dengan konstanta
     * ini). Kalau nanti perlu dikembalikan/disesuaikan, pakai halaman Kelola
     * Role & Permission (permission 'manage-roles-permissions'), tidak perlu
     * ubah kode ini lagi.
     */
    public const FINANCE_PERMISSIONS = [
        'manage-cash-bank',
        'manage-general-ledger',
        'manage-payroll',
        'manage-other-receivables',
        'manage-fixed-assets',
        'manage-budgets',
        'view-financial-reports',
    ];

    /**
     * Pengelompokan permission untuk tampilan matrix di halaman Kelola Role &
     * Permission (resources/views/livewire/admin/manage-role-permissions.blade.php).
     * Murni untuk keperluan tampilan -- tidak memengaruhi permission check di
     * aplikasi (yang tetap berbasis @can('permission-name') seperti biasa).
     */
    public const PERMISSION_GROUPS = [
        'Administrasi & Akses' => ['manage-users', 'manage-roles-permissions', 'manage-projects', 'manage-kpi-settings', 'manage-attendance-overrides'],
        'Lapangan & Teknisi' => ['view-reports', 'submit-report', 'view-kpi-team', 'create-assignments', 'approve-assignments', 'manage-pump-assets', 'request-cash-advance', 'manage-cash-advances'],
        'Kontrak & Penawaran' => ['view-all-project', 'manage-customers', 'manage-contracts', 'approve-quotation', 'view-contract-value', 'manage-invoices'],
        'Purchasing' => ['manage-purchasing', 'approve-purchasing', 'view-purchasing', 'view-harga', 'manage-material-tracking', 'manage-vendor-payments', 'manage-delivery-gatepass'],
        'Marketing & Sales' => ['manage-prospects', 'manage-sales-orders', 'view-sales-dashboard'],
        'Finance & Accounting' => ['manage-payroll', 'manage-cash-bank', 'view-financial-reports', 'manage-general-ledger', 'manage-other-receivables', 'manage-fixed-assets', 'manage-budgets', 'approve-budgets', 'manage-cost-control'],
        'Operasional & Dokumen' => ['view-weekly-recap'],
        'Company Website' => ['manage-website'],
    ];

    public function run(): void
    {
        foreach (self::PERMISSIONS as $name => $description) {
            Permission::findOrCreate($name, 'web');
        }

        $administrator = Role::findOrCreate('Administrator', 'web');
        // Update 2026-10-07: permission finansial (FINANCE_PERMISSIONS) DICABUT
        // dari Administrator sesuai keputusan client -- "role finance adalah
        // role tersendiri yg tidak digabung dengan role lainnya". Administrator
        // tetap dapat SEMUA permission lain (termasuk manage-roles-permissions,
        // jadi tetap bisa mengatur role/permission lewat halaman admin). Kalau
        // Administrator perlu akses finance lagi di kemudian hari, atur lewat
        // halaman Kelola Role & Permission, bukan ubah kode ini.
        $administrator->syncPermissions(array_diff(array_keys(self::PERMISSIONS), self::FINANCE_PERMISSIONS));

        $direktur = Role::findOrCreate('Direktur', 'web');
        $direktur->syncPermissions([
            'view-all-project',
            'view-reports',
            'view-kpi-team',
            'view-purchasing',
            'view-harga',
            'approve-purchasing',
            'approve-quotation',
            'view-contract-value',
            'view-financial-reports',
            'view-sales-dashboard',
            'approve-budgets',
        ]);

        $pm = Role::findOrCreate('Project Manager', 'web');
        $pm->syncPermissions([
            'manage-projects',
            'view-reports',
            'view-purchasing',
            'manage-attendance-overrides',
            'manage-pump-assets',
            'approve-assignments',
            'manage-cash-advances',
        ]);

        // Sisi komersial kontrak: menyusun Contract/Release Order/Penawaran ke
        // customer. Terpisah dari Project Manager (yang menangani eksekusi
        // activity & penugasan teknisi), tapi satu user boleh punya kedua role
        // sekaligus kalau perusahaan mau assign orang yang sama.
        $projectController = Role::findOrCreate('Project Controller', 'web');
        $projectController->syncPermissions([
            'view-all-project',
            'manage-customers',
            'manage-contracts',
            'view-contract-value',
            'view-purchasing',
            'view-harga',
            'manage-invoices',
        ]);

        $purchasing = Role::findOrCreate('Purchasing', 'web');
        $purchasing->syncPermissions([
            'view-all-project',
            'manage-purchasing',
            'view-purchasing',
            'view-harga',
            'manage-material-tracking',
            'manage-vendor-payments',
        ]);

        $teknisi = Role::findOrCreate('Teknisi', 'web');
        $teknisi->syncPermissions([
            'submit-report',
            'request-cash-advance',
        ]);

        // Lead Technician: teknisi senior yang membuat usulan jadwal/penugasan
        // untuk timnya (termasuk dirinya sendiri), tapi usulan itu baru aktif
        // setelah disetujui PM/Administrator (lihat Assignment::STATUSES).
        // Tetap diberi submit-report karena Lead Technician juga turun ke
        // lapangan dan mengisi laporan seperti Teknisi biasa.
        $leadTechnician = Role::findOrCreate('Lead Technician', 'web');
        $leadTechnician->syncPermissions([
            'create-assignments',
            'submit-report',
            'request-cash-advance',
        ]);

        $adminKantor = Role::findOrCreate('Admin Kantor', 'web');
        $adminKantor->syncPermissions([
            'view-weekly-recap',
            'view-reports',
        ]);

        $adminPurchase = Role::findOrCreate('Admin Purchase', 'web');
        $adminPurchase->syncPermissions([
            'manage-delivery-gatepass',
            'view-purchasing',
        ]);

        // Marketing (SRS 4.5 / role #11): kelola Prospect & pipeline CRM, log
        // aktivitas, dan dashboard ringkasan penjualan. Terpisah dari Sales
        // Support supaya satu orang boleh dirangkap kedua role kalau perlu,
        // tapi defaultnya dipisah per tahap (lead generation vs closing deal).
        $marketing = Role::findOrCreate('Marketing', 'web');
        $marketing->syncPermissions([
            'manage-prospects',
            'view-sales-dashboard',
        ]);

        // Sales Support (SRS 4.6 / role #12): kelola Sales Order & dokumen
        // teknis, dan mengonfirmasinya jadi Contract/Project begitu deal
        // dari Marketing (atau order ulang Customer lama) siap diproses.
        $salesSupport = Role::findOrCreate('Sales Support', 'web');
        $salesSupport->syncPermissions([
            'manage-sales-orders',
            'view-sales-dashboard',
        ]);

        // Finance & Accounting (SRS role #9) -- keputusan client 2026-10-07:
        // role tersendiri, tidak digabung ke role lain manapun.
        $financeAccounting = Role::findOrCreate('Finance & Accounting', 'web');
        $financeAccounting->syncPermissions(self::FINANCE_PERMISSIONS);

        // Cost Control (SRS 4.18) -- keputusan client 2026-10-07: role
        // tersendiri yang MENGELOLA (bukan cuma melihat) budget & actual cost
        // per proyek secara real-time. Perubahan budget/actual oleh role ini
        // TIDAK butuh approval pihak lain -- cukup kirim notifikasi ke PM
        // proyek terkait (lihat App\Livewire\CostControl\ManageProjectCost).
        $costControl = Role::findOrCreate('Cost Control', 'web');
        $costControl->syncPermissions(['manage-cost-control']);

        // Role "Management": dikonfigurasi sesuai kebutuhan perusahaan, tanpa default permission.
        Role::findOrCreate('Management', 'web');
    }
}
