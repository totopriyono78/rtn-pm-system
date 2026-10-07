<?php

namespace Database\Seeders;

use App\Models\WebsitePortfolioItem;
use App\Models\WebsiteProduct;
use App\Models\WebsiteService;
use App\Models\WebsiteSetting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Konten contoh/placeholder untuk Company Website -- supaya desain & struktur
 * halaman bisa langsung dilihat. SEMUA data di sini (alamat, telepon, email,
 * deskripsi) adalah CONTOH dan wajib diganti lewat menu CMS (Company Website)
 * sebelum website ini dipakai sebagai situs resmi perusahaan.
 */
class WebsiteContentSeeder extends Seeder
{
    public function run(): void
    {
        WebsiteSetting::updateOrCreate(['id' => 1], [
            'company_name' => 'PT RTN',
            'tagline' => 'Solusi Terpercaya untuk Service, Maintenance, dan Pengadaan Pompa Industri',
            'about' => 'PT RTN adalah perusahaan yang bergerak di bidang jasa pemeliharaan (service & maintenance) dan pengadaan unit pompa industri. Kami melayani berbagai sektor industri dengan dukungan tim teknisi berpengalaman, proses kerja terstruktur, dan komitmen terhadap keandalan peralatan pelanggan.',
            'vision' => 'Menjadi mitra terpercaya dalam penyediaan solusi pompa industri yang andal, efisien, dan berkelanjutan di Indonesia.',
            'mission' => "Memberikan layanan service & maintenance pompa dengan standar mutu dan keselamatan kerja tertinggi.\nMenyediakan unit pompa baru yang sesuai kebutuhan teknis dan operasional pelanggan.\nMembangun hubungan jangka panjang dengan pelanggan melalui layanan purna jual yang responsif.\nMengembangkan kompetensi tim secara berkelanjutan mengikuti perkembangan teknologi pompa.",
            'address' => 'Jl. Industri Raya No. 10, Bekasi, Jawa Barat 17530 (contoh -- ganti dengan alamat asli)',
            'phone' => '(021) 1234-5678',
            'whatsapp' => '6281234567890',
            'email' => 'info@rtn.co.id',
            'hero_headline' => 'Partner Terpercaya untuk Keandalan Pompa Industri Anda',
            'hero_subheadline' => 'Dari assessment, perawatan rutin, hingga pengadaan unit baru -- PT RTN mendukung kelangsungan operasional pompa di fasilitas Anda.',
        ]);

        $products = [
            ['name' => 'Pompa Sentrifugal Horizontal', 'category' => 'Pompa Sentrifugal', 'short' => 'Untuk aplikasi transfer fluida umum dengan kapasitas dan head bervariasi.'],
            ['name' => 'Pompa Submersible', 'category' => 'Pompa Submersible', 'short' => 'Dirancang untuk operasi terendam, cocok untuk drainase dan sumur dalam.'],
            ['name' => 'Pompa Multistage', 'category' => 'Pompa Multistage', 'short' => 'Head tinggi untuk aplikasi boiler feed dan sistem tekanan tinggi.'],
            ['name' => 'Pompa Vertical Turbine', 'category' => 'Pompa Vertical Turbine', 'short' => 'Solusi pengambilan air dari sumur dalam atau sumber air permukaan.'],
            ['name' => 'Booster Pump Set', 'category' => 'Booster Pump Set', 'short' => 'Paket siap pasang untuk menjaga tekanan distribusi air stabil.'],
            ['name' => 'Pompa Berpenggerak Diesel', 'category' => 'Pompa Diesel Engine', 'short' => 'Solusi mandiri listrik untuk lokasi tanpa akses jaringan PLN.'],
        ];
        foreach ($products as $i => $p) {
            WebsiteProduct::updateOrCreate(['slug' => Str::slug($p['name'])], [
                'name' => $p['name'],
                'category' => $p['category'],
                'short_description' => $p['short'],
                'description' => $p['short'].' Spesifikasi teknis lengkap (kapasitas, head, material, daya motor) tersedia atas permintaan -- hubungi tim kami melalui halaman Kontak.',
                'is_published' => true,
                'sort_order' => $i,
            ]);
        }

        $services = [
            ['division' => 'service_maintenance', 'name' => 'Assessment & Inspeksi Pompa', 'icon' => 'search', 'short' => 'Pemeriksaan kondisi pompa untuk menentukan kebutuhan perawatan atau perbaikan.'],
            ['division' => 'service_maintenance', 'name' => 'Preventive Maintenance', 'icon' => 'refresh', 'short' => 'Perawatan terjadwal untuk mencegah kerusakan dan memperpanjang usia pakai pompa.'],
            ['division' => 'service_maintenance', 'name' => 'Corrective Maintenance & Overhaul', 'icon' => 'shield', 'short' => 'Perbaikan dan overhaul menyeluruh untuk pompa yang mengalami kerusakan.'],
            ['division' => 'service_maintenance', 'name' => 'Commissioning', 'icon' => 'check', 'short' => 'Pengujian dan serah terima pompa yang siap dioperasikan.'],
            ['division' => 'sales_pompa', 'name' => 'Survey & Konsultasi Kebutuhan', 'icon' => 'users', 'short' => 'Analisa kebutuhan teknis untuk menentukan spesifikasi pompa yang tepat.'],
            ['division' => 'sales_pompa', 'name' => 'Pengadaan Unit Pompa Baru', 'icon' => 'truck', 'short' => 'Pengadaan unit pompa baru sesuai spesifikasi dan standar industri.'],
            ['division' => 'sales_pompa', 'name' => 'Instalasi & Commissioning', 'icon' => 'package', 'short' => 'Pemasangan dan pengujian unit baru hingga siap beroperasi penuh.'],
            ['division' => 'sales_pompa', 'name' => 'Layanan After-Sales & Garansi', 'icon' => 'clock', 'short' => 'Dukungan purna jual dan garansi untuk menjaga performa unit yang terpasang.'],
        ];
        foreach ($services as $i => $s) {
            WebsiteService::updateOrCreate(['slug' => Str::slug($s['name'])], [
                'name' => $s['name'],
                'division' => $s['division'],
                'icon' => $s['icon'],
                'short_description' => $s['short'],
                'description' => $s['short'],
                'is_published' => true,
                'sort_order' => $i,
            ]);
        }

        $portfolio = [
            ['title' => 'Pemeliharaan Rutin Sistem Pompa -- Fasilitas Pengolahan Migas', 'category' => 'Migas', 'year' => 2025],
            ['title' => 'Pengadaan & Instalasi Pompa Submersible -- Perkebunan Kelapa Sawit', 'category' => 'Perkebunan', 'year' => 2024],
            ['title' => 'Overhaul Pompa Sentrifugal -- Fasilitas Pergudangan & Logistik', 'category' => 'Pergudangan & Logistik', 'year' => 2024],
            ['title' => 'Commissioning Booster Pump Set -- Pembangkit Listrik', 'category' => 'Energi & Pembangkit', 'year' => 2023],
        ];
        foreach ($portfolio as $i => $p) {
            WebsitePortfolioItem::updateOrCreate(['slug' => Str::slug($p['title'])], [
                'title' => $p['title'],
                'client_name' => null,
                'category' => $p['category'],
                'year' => $p['year'],
                'description' => 'Proyek contoh untuk menggambarkan jenis pekerjaan yang dilayani. Nama klien dirahasiakan sesuai kebijakan kerahasiaan -- ganti dengan proyek nyata lewat menu CMS.',
                'is_published' => true,
                'sort_order' => $i,
            ]);
        }
    }
}
