<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

/**
 * Default Chart of Account untuk perusahaan jasa maintenance/service
 * seperti PT RTN -- titik awal yang wajar, SEPENUHNYA bisa diedit/
 * ditambah lewat halaman Kelola Chart of Account setelah seed ini
 * jalan. Idempotent (firstOrCreate by code), aman dijalankan berkali-
 * kali kalau user menambah akun baru lewat UI di antara run seeder.
 * Akun 1150 (Piutang Lain-lain) ditambahkan belakangan untuk modul
 * "AR murni"/Piutang Lain-lain (2026-10-06) -- additive, tidak
 * mengubah 16 akun yang sudah ada. Akun 1550 (Akumulasi Depresiasi Aset
 * Tetap, kontra-aset -- lihat catatan di ChartOfAccount::booted(), normal
 * balance tetap 'debit' ikut `type` tapi saldo wajarnya kredit, jadi
 * tampil negatif di Neraca Saldo -- ini memang benar/standar akuntansi
 * untuk akun kontra-aset) dan 5400 (Beban Depresiasi) ditambahkan untuk
 * modul Asset Management (2026-10-06).
 */
class ChartOfAccountSeeder extends Seeder
{
    public const ACCOUNTS = [
        // Aset
        ['code' => '1000', 'name' => 'Kas & Bank', 'type' => 'asset'],
        ['code' => '1100', 'name' => 'Piutang Usaha', 'type' => 'asset'],
        ['code' => '1150', 'name' => 'Piutang Lain-lain', 'type' => 'asset'],
        ['code' => '1200', 'name' => 'Persediaan Material', 'type' => 'asset'],
        ['code' => '1300', 'name' => 'Uang Muka / Kasbon Karyawan', 'type' => 'asset'],
        ['code' => '1500', 'name' => 'Aset Tetap', 'type' => 'asset'],
        ['code' => '1550', 'name' => 'Akumulasi Depresiasi Aset Tetap', 'type' => 'asset'],
        // Kewajiban
        ['code' => '2000', 'name' => 'Hutang Usaha (Vendor)', 'type' => 'liability'],
        ['code' => '2100', 'name' => 'Hutang Gaji', 'type' => 'liability'],
        ['code' => '2200', 'name' => 'Hutang Pajak (PPh21/PPN)', 'type' => 'liability'],
        // Modal
        ['code' => '3000', 'name' => 'Modal Pemilik', 'type' => 'equity'],
        ['code' => '3100', 'name' => 'Laba Ditahan', 'type' => 'equity'],
        // Pendapatan
        ['code' => '4000', 'name' => 'Pendapatan Jasa Maintenance', 'type' => 'revenue'],
        ['code' => '4100', 'name' => 'Pendapatan Lain-lain', 'type' => 'revenue'],
        // Beban
        ['code' => '5000', 'name' => 'Beban Gaji & Tunjangan', 'type' => 'expense'],
        ['code' => '5100', 'name' => 'Beban Operasional Proyek', 'type' => 'expense'],
        ['code' => '5200', 'name' => 'Beban Administrasi & Umum', 'type' => 'expense'],
        ['code' => '5300', 'name' => 'Beban Lain-lain', 'type' => 'expense'],
        ['code' => '5400', 'name' => 'Beban Depresiasi', 'type' => 'expense'],
    ];

    public function run(): void
    {
        foreach (self::ACCOUNTS as $account) {
            ChartOfAccount::firstOrCreate(
                ['code' => $account['code']],
                ['name' => $account['name'], 'type' => $account['type'], 'is_active' => true]
            );
        }
    }
}
