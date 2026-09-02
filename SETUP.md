# RTN PM System — Panduan Instalasi

Sistem Project Management PT RTN, dibangun dengan **Laravel 13 + Livewire 3 (Blade)** dan **PostgreSQL**, sesuai dokumen SRS (`RTN_PMS_Requirement_Vendor_4.pdf`).

## Prasyarat

- PHP >= 8.3 (dengan ekstensi: `pdo_pgsql`, `mbstring`, `fileinfo`, `gd` atau `intervention`, `zip`)
- Composer 2.x
- Node.js 18+ dan npm
- PostgreSQL 13+ (bisa juga pakai XAMPP/Laragon yang sudah menyertakan PHP, atau install PostgreSQL terpisah dari https://www.postgresql.org/download/windows/)

> Catatan: source code ini dibuat di lingkungan sandbox tanpa akses ke Packagist/npm registry publik, sehingga `composer install` **belum sempat dijalankan/diuji langsung** di sana. Semua kode sudah dicek sintaksnya (php -l) dan direview manual, tetapi jalankan langkah di bawah ini di komputer Anda dan beri tahu saya jika ada error saat instalasi — akan saya perbaiki secepatnya.

## Langkah Instalasi

1. **Install dependency PHP**
   ```bash
   composer install
   ```

2. **Salin file environment**
   ```bash
   copy .env.example .env      # Windows
   # atau: cp .env.example .env   (Git Bash/WSL)
   ```

3. **Buat database PostgreSQL**

   Buat database baru bernama `rtn_pms` (bisa lewat pgAdmin atau `psql`):
   ```sql
   CREATE DATABASE rtn_pms;
   ```

   Sesuaikan kredensial di `.env`:
   ```
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=rtn_pms
   DB_USERNAME=postgres
   DB_PASSWORD=isi_password_postgres_anda
   ```

4. **Generate application key**
   ```bash
   php artisan key:generate
   ```

5. **Jalankan migrasi + seeder data demo**
   ```bash
   php artisan migrate --seed
   ```
   Perintah ini membuat seluruh tabel dan mengisi data contoh: role & permission, 7 akun demo (lihat bawah), 2 region, 3 unit/IT, 3 proyek beserta activity, 1 laporan + work log contoh, 5 master item, 2 vendor, dan 1 alur RFQ lengkap (2 penawaran vendor dibandingkan, hasil negosiasi, pemenang berbeda per item, sudah di-approve Direktur) beserta 2 Purchase Order dan material tracking-nya.

6. **Install dependency frontend & build asset**
   ```bash
   npm install
   npm run build
   ```
   (untuk mode pengembangan dengan hot-reload, gunakan `npm run dev` di terminal terpisah)

7. **Naikkan batas upload file** (agar sesuai kebutuhan SRS: minimal 50 MB)

   Edit `php.ini` yang dipakai PHP CLI/server Anda:
   ```
   upload_max_filesize = 60M
   post_max_size = 70M
   max_execution_time = 120
   ```

8. **Jalankan server**
   ```bash
   php artisan serve
   ```
   Buka http://localhost:8000 di browser.

## Akun Demo

Semua akun memakai password: **`password`**

| Role | Email | Catatan |
|---|---|---|
| Administrator | admin@rtn.co.id | Akses penuh + kelola user |
| Direktur | direktur@rtn.co.id | Read-only semua modul + KPI dashboard, approve RFQ & penerbitan PO |
| Project Manager | pm.jbb@rtn.co.id | Region JBB, **tanpa** akses lihat harga |
| Project Manager | pm.jbt@rtn.co.id | Region JBT, **dengan** akses lihat harga (contoh permission override sesuai SRS bab 3) |
| Purchasing | purchasing@rtn.co.id | Kelola master item, vendor, RFQ & penawaran vendor, material tracking |
| Teknisi | teknisi.jbb@rtn.co.id | Region JBB, isi laporan harian/akhir |
| Management | management@rtn.co.id | Role kosong, contoh role yang bisa dikonfigurasi Admin |

**Segera ganti password semua akun setelah instalasi**, terutama sebelum dipakai di server production.

## Struktur Modul yang Sudah Diimplementasikan

- **Autentikasi & User Management** — login berbasis session, halaman Admin > Kelola User (tambah/edit/nonaktifkan akun, assign role, permission override per individu, pembatasan region).
- **Project Management** — hierarki Region → Unit → Proyek → Activity, CRUD proyek, progress bar & status activity.
- **Modul Teknisi** — jadwal penugasan, submit laporan (daily/final) dengan upload PDF/DOCX/foto/video, tersimpan otomatis per proyek (folder Daily Report/Final Report/Foto/Drawing).
- **KPI & Work Log** — dashboard Direktur: jam kerja hari ini (bar chart), akumulasi harian/mingguan/bulanan, drill-down per karyawan, rencana vs aktual per proyek.
- **Purchasing** — master item & vendor; alur pengadaan: Request for Quotation (RFQ) berisi daftar kebutuhan material/jasa per proyek → Purchasing input penawaran dari beberapa vendor untuk dibandingkan → harga bisa diperbarui untuk mencatat hasil negosiasi → pilih vendor pemenang per baris item (bisa berbeda vendor untuk item berbeda) → ajukan ke Direktur → setelah approve, sistem otomatis menerbitkan satu Purchase Order per vendor terpilih beserta cetakan resmi (PDF via print browser) → Material Tracking dibuat otomatis per baris PO, status Ordered → Shipping → Arrived → Installed. Halaman Vendor menampilkan riwayat RFQ yang diikuti dan PO yang pernah diterbitkan ke vendor tersebut. Visibilitas harga dikontrol permission `view-harga`.

## Catatan Teknis

- Berkas laporan disimpan di disk privat (`storage/app/private/project-files/...`), **tidak** bisa diakses langsung lewat URL publik — hanya lewat route `/reports/files/{id}` yang sudah dicek permission & kepemilikannya.
- Permission granular: `manage-users`, `view-all-project`, `manage-projects`, `view-reports`, `submit-report`, `view-kpi-team`, `manage-purchasing`, `approve-purchasing`, `view-purchasing`, `view-harga`, `manage-material-tracking`. Bisa ditambah lagi dari `database/seeders/RolePermissionSeeder.php` lalu jalankan ulang seeder-nya.
- Kop surat dokumen cetak (RFQ/PO) diatur lewat variabel `COMPANY_*` di `.env` (lihat `.env.example`) dan `config/company.php`.
- Override permission per individu bersifat **tambahan** (additive) di atas permission default role — sesuai contoh kasus di SRS (dua PM dengan clearance berbeda).
- Jika ingin reset seluruh data: `php artisan migrate:fresh --seed` (hati-hati, ini menghapus semua data).

## Jika Ada Error Saat Instalasi

Kirimkan pesan error yang muncul (dari `composer install`, `npm run build`, atau `php artisan migrate`) — kemungkinan besar hanya penyesuaian kecil versi dependency yang perlu diperbaiki di `composer.json`/`package.json`.
