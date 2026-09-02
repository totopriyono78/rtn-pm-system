<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tampilkan Tabel Akun Demo di Halaman Login
    |--------------------------------------------------------------------------
    |
    | Bermanfaat untuk keperluan presentasi/demo agar penguji tidak perlu
    | menghafal kredensial. Default: tampil di semua environment KECUALI
    | production, supaya tidak otomatis terekspos saat sudah live untuk
    | pengguna sungguhan. Bisa dipaksa on/off eksplisit lewat env
    | SHOW_DEMO_ACCOUNTS=true|false (misalnya untuk sesi demo terjadwal
    | di server production).
    |
    */
    'show_accounts' => env('SHOW_DEMO_ACCOUNTS', env('APP_ENV', 'production') !== 'production'),

    /*
    |--------------------------------------------------------------------------
    | Password Bersama Akun Demo
    |--------------------------------------------------------------------------
    */
    'password' => env('DEMO_ACCOUNTS_PASSWORD', 'password'),

    /*
    |--------------------------------------------------------------------------
    | Daftar Akun Demo
    |--------------------------------------------------------------------------
    |
    | Sinkron dengan database/seeders/DemoDataSeeder.php. Kalau menambah/
    | mengubah akun demo di seeder, perbarui juga daftar ini.
    |
    */
    'accounts' => [
        ['role' => 'Administrator', 'email' => 'admin@rtn.co.id', 'note' => 'Akses penuh + kelola user'],
        ['role' => 'Direktur', 'email' => 'direktur@rtn.co.id', 'note' => 'Approve RFQ & PO, KPI'],
        ['role' => 'Project Manager', 'email' => 'pm.jbb@rtn.co.id', 'note' => 'Region JBB, tanpa lihat harga'],
        ['role' => 'Project Manager', 'email' => 'pm.jbt@rtn.co.id', 'note' => 'Region JBT, dengan lihat harga'],
        ['role' => 'Purchasing', 'email' => 'purchasing@rtn.co.id', 'note' => 'Vendor, RFQ & PO'],
        ['role' => 'Teknisi', 'email' => 'teknisi.jbb@rtn.co.id', 'note' => 'Laporan harian/akhir'],
        ['role' => 'Management', 'email' => 'management@rtn.co.id', 'note' => 'Role kosong (contoh)'],
    ],

];
