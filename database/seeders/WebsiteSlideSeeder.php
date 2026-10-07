<?php

namespace Database\Seeders;

use App\Models\WebsiteSlide;
use Illuminate\Database\Seeder;

/**
 * 4 slide contoh untuk carousel Beranda (Company Website) -- gambar banner
 * (garis/ikon abstrak, warna brand biru+merah RTN) sudah disiapkan di
 * storage/app/public/website/slides/. SEMUA teks di sini adalah CONTOH:
 * boleh dipakai langsung, atau diedit/diganti lewat menu admin
 * Kelola Website > Slide Beranda sebelum website dipakai resmi.
 *
 * Jalankan manual (tidak otomatis ikut db:seed utama):
 *   php artisan db:seed --class=WebsiteSlideSeeder
 */
class WebsiteSlideSeeder extends Seeder
{
    public function run(): void
    {
        $slides = [
            [
                'title' => 'Partner Terpercaya Perawatan & Pengadaan Pompa Industri',
                'subtitle' => 'PT RTN melayani service, maintenance, hingga pengadaan pompa dan engine untuk kebutuhan industri Anda di seluruh Indonesia.',
                'image_path' => 'website/slides/slide-1-company-intro.jpg',
                'link_label' => 'Hubungi Kami',
                'link_url' => '/kontak',
                'sort_order' => 0,
            ],
            [
                'title' => 'Service & Maintenance Pompa, Ditangani Teknisi Berpengalaman',
                'subtitle' => 'Perawatan preventif, perbaikan, hingga overhaul pompa industri -- didukung tim teknisi lapangan dan sistem pelaporan pekerjaan yang transparan.',
                'image_path' => 'website/slides/slide-2-maintenance.jpg',
                'link_label' => 'Lihat Layanan Kami',
                'link_url' => '/layanan',
                'sort_order' => 1,
            ],
            [
                'title' => 'Pengadaan Pompa & Spare Part Sesuai Kebutuhan Proyek Anda',
                'subtitle' => 'Dari pompa baru, engine, hingga spare part original -- kami bantu proses pengadaan sampai ke lokasi proyek Anda.',
                'image_path' => 'website/slides/slide-3-sales.jpg',
                'link_label' => 'Lihat Produk Kami',
                'link_url' => '/produk',
                'sort_order' => 2,
            ],
            [
                'title' => 'Dipercaya Berbagai Klien Industri di Indonesia',
                'subtitle' => 'Puluhan proyek service, maintenance, dan pengadaan pompa telah kami selesaikan -- termasuk skema Kontrak Payung untuk klien dengan kebutuhan berkelanjutan.',
                'image_path' => 'website/slides/slide-4-trust.jpg',
                'link_label' => 'Lihat Portofolio Kami',
                'link_url' => '/portofolio',
                'sort_order' => 3,
            ],
        ];

        foreach ($slides as $s) {
            WebsiteSlide::updateOrCreate(['title' => $s['title']], [
                'subtitle' => $s['subtitle'],
                'image_path' => $s['image_path'],
                'link_label' => $s['link_label'],
                'link_url' => $s['link_url'],
                'is_published' => true,
                'sort_order' => $s['sort_order'],
            ]);
        }
    }
}
