<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Assignment;
use App\Models\Item;
use App\Models\Project;
use App\Models\Region;
use App\Models\Report;
use App\Models\RequestForQuotation;
use App\Models\RfqItem;
use App\Models\Unit;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorQuotation;
use App\Models\VendorQuotationItem;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        // ===== Users (satu akun contoh per role, sesuai SRS bab 3) =====
        $admin = User::create([
            'name' => 'Admin RTN',
            'email' => 'admin@rtn.co.id',
            'password' => 'password',
            'is_active' => true,
        ]);
        $admin->assignRole('Administrator');

        $direktur = User::create([
            'name' => 'Budi Santoso',
            'email' => 'direktur@rtn.co.id',
            'password' => 'password',
            'is_active' => true,
        ]);
        $direktur->assignRole('Direktur');

        $pmJbb = User::create([
            'name' => 'Andi Wijaya (PM)',
            'email' => 'pm.jbb@rtn.co.id',
            'password' => 'password',
            'is_active' => true,
        ]);
        $pmJbb->assignRole('Project Manager');

        // Contoh kasus dari SRS bab 3: dua PM dengan jabatan sama, clearance beda.
        // PM kedua ini diberi permission override VIEW_HARGA agar bisa melihat harga penawaran.
        $pmJbt = User::create([
            'name' => 'Siti Rahma (PM)',
            'email' => 'pm.jbt@rtn.co.id',
            'password' => 'password',
            'is_active' => true,
        ]);
        $pmJbt->assignRole('Project Manager');
        $pmJbt->givePermissionTo('view-harga');

        $purchasing = User::create([
            'name' => 'Dewi Purchasing',
            'email' => 'purchasing@rtn.co.id',
            'password' => 'password',
            'is_active' => true,
        ]);
        $purchasing->assignRole('Purchasing');

        $teknisi = User::create([
            'name' => 'Joko Teknisi',
            'email' => 'teknisi.jbb@rtn.co.id',
            'password' => 'password',
            'is_active' => true,
        ]);
        $teknisi->assignRole('Teknisi');

        $management = User::create([
            'name' => 'Rina Management',
            'email' => 'management@rtn.co.id',
            'password' => 'password',
            'is_active' => true,
        ]);
        $management->assignRole('Management');
        // Role "Management" sengaja tanpa permission default (lihat RolePermissionSeeder —
        // sesuai SRS bab 3: "Dikonfigurasi sesuai kebutuhan perusahaan, oleh Administrator").
        // Untuk akun demo ini, berikan contoh clearance individual (view-only, tanpa hak
        // approve) sebagaimana lazimnya kebutuhan tim Management memantau seluruh proyek —
        // pola yang sama dengan override VIEW_HARGA pada $pmJbt di atas.
        $management->givePermissionTo([
            'view-all-project',
            'view-reports',
            'view-kpi-team',
            'view-purchasing',
            'view-harga',
        ]);

        // ===== Regions & Units =====
        $jbb = Region::create(['code' => 'JBB', 'name' => 'Region Jawa Bagian Barat']);
        $jbt = Region::create(['code' => 'JBT', 'name' => 'Region Jawa Bagian Tengah']);

        $itJakarta = Unit::create(['region_id' => $jbb->id, 'code' => 'IT-JKT', 'name' => 'IT Jakarta']);
        $itBalongan = Unit::create(['region_id' => $jbb->id, 'code' => 'IT-BLG', 'name' => 'IT Balongan']);
        $itSemarang = Unit::create(['region_id' => $jbt->id, 'code' => 'IT-SMG', 'name' => 'IT Semarang']);

        // Batasi akses: PM JBB & Teknisi JBB hanya region JBB, PM JBT hanya region JBT.
        $pmJbb->regions()->attach($jbb->id);
        $teknisi->regions()->attach($jbb->id);
        $pmJbt->regions()->attach($jbt->id);

        // ===== Projects & Activities =====
        $project1 = Project::create([
            'unit_id' => $itJakarta->id,
            'pic_user_id' => $pmJbb->id,
            'name' => 'Performance Test Tangki 31T-101',
            'description' => 'Pengujian performa tangki penyimpanan 31T-101.',
            'budget' => 50000000,
            'start_date' => now()->subDays(10),
            'end_date' => now()->addDays(20),
            'status' => 'ongoing',
        ]);

        $act1 = Activity::create(['project_id' => $project1->id, 'name' => 'Site Survey', 'status' => 'selesai', 'planned_hours' => 16, 'order_no' => 1, 'start_date' => now()->subDays(10), 'end_date' => now()->subDays(8)]);
        $act2 = Activity::create(['project_id' => $project1->id, 'name' => 'Pelaksanaan Pekerjaan', 'status' => 'sedang_dikerjakan', 'planned_hours' => 80, 'order_no' => 2, 'start_date' => now()->subDays(7), 'end_date' => now()->addDays(5)]);
        Activity::create(['project_id' => $project1->id, 'name' => 'Commissioning', 'status' => 'belum_dimulai', 'planned_hours' => 24, 'order_no' => 3, 'start_date' => now()->addDays(6), 'end_date' => now()->addDays(12)]);
        Activity::create(['project_id' => $project1->id, 'name' => 'FAT', 'status' => 'belum_dimulai', 'planned_hours' => 8, 'order_no' => 4, 'start_date' => now()->addDays(13), 'end_date' => now()->addDays(20)]);

        $project2 = Project::create([
            'unit_id' => $itBalongan->id,
            'pic_user_id' => $pmJbb->id,
            'name' => 'Corrective Maintenance Pompa P-205',
            'description' => 'Perbaikan pompa P-205 yang mengalami penurunan performa.',
            'start_date' => now()->addDays(5),
            'end_date' => now()->addDays(35),
            'status' => 'planning',
        ]);
        Activity::create(['project_id' => $project2->id, 'name' => 'Site Survey', 'status' => 'belum_dimulai', 'planned_hours' => 8, 'order_no' => 1]);
        Activity::create(['project_id' => $project2->id, 'name' => 'Pelaksanaan Pekerjaan', 'status' => 'belum_dimulai', 'planned_hours' => 40, 'order_no' => 2]);

        $project3 = Project::create([
            'unit_id' => $itSemarang->id,
            'pic_user_id' => $pmJbt->id,
            'name' => 'Commissioning Unit Baru',
            'description' => 'Commissioning unit produksi baru di IT Semarang.',
            'start_date' => now()->subDays(3),
            'end_date' => now()->addDays(15),
            'status' => 'ongoing',
        ]);
        Activity::create(['project_id' => $project3->id, 'name' => 'FAT', 'status' => 'sedang_dikerjakan', 'planned_hours' => 24, 'order_no' => 1]);

        // ===== Assignment + contoh laporan & work log =====
        $assignment = Assignment::create([
            'activity_id' => $act2->id,
            'user_id' => $teknisi->id,
            'scheduled_date' => now(),
            'notes' => 'Lanjutan pelaksanaan pekerjaan hari ini.',
        ]);

        $report = Report::create([
            'activity_id' => $act2->id,
            'user_id' => $teknisi->id,
            'assignment_id' => $assignment->id,
            'type' => 'daily',
            'report_date' => now(),
            'start_time' => '08:00:00',
            'end_time' => '16:00:00',
            'notes' => 'Pekerjaan berjalan sesuai rencana.',
        ]);

        $report->workLog()->create([
            'user_id' => $teknisi->id,
            'activity_id' => $act2->id,
            'project_id' => $project1->id,
            'log_date' => $report->report_date,
            'start_time' => $report->start_time,
            'end_time' => $report->end_time,
            'duration_minutes' => $report->duration_minutes,
        ]);

        // ===== Master item =====
        $item1 = Item::create(['code' => 'MAT-045', 'name' => 'Pipa Stainless 2 inch', 'category' => 'material', 'unit_of_measure' => 'meter', 'unit_price' => 350000]);
        $item2 = Item::create(['code' => 'SP-012', 'name' => 'Bearing Pompa Sentrifugal', 'category' => 'sparepart', 'unit_of_measure' => 'pcs', 'unit_price' => 1250000]);
        Item::create(['code' => 'JAS-003', 'name' => 'Jasa Kalibrasi Instrumen', 'category' => 'jasa', 'unit_of_measure' => 'ls', 'unit_price' => 5000000]);
        Item::create(['code' => 'MAT-046', 'name' => 'Kabel Instrumen 2x1.5mm', 'category' => 'material', 'unit_of_measure' => 'meter', 'unit_price' => 45000]);
        Item::create(['code' => 'SP-013', 'name' => 'Seal Kit Valve', 'category' => 'sparepart', 'unit_of_measure' => 'set', 'unit_price' => 875000]);

        // ===== Vendor =====
        $vendorA = Vendor::create([
            'code' => 'VDR-0001',
            'name' => 'CV Sumber Teknik Mandiri',
            'contact_person' => 'Hendra Kusuma',
            'phone' => '021-5550101',
            'email' => 'sales@sumberteknik.co.id',
            'address' => 'Jl. Industri Raya No. 12, Cikarang',
            'npwp' => '01.234.567.8-901.000',
        ]);
        $vendorB = Vendor::create([
            'code' => 'VDR-0002',
            'name' => 'PT Multi Sarana Industri',
            'contact_person' => 'Lina Marlina',
            'phone' => '021-5550202',
            'email' => 'procurement@multisarana.co.id',
            'address' => 'Jl. Ahmad Yani No. 88, Bekasi',
            'npwp' => '02.345.678.9-012.000',
        ]);

        // ===== Contoh alur lengkap: RFQ -> penawaran 2 vendor -> negosiasi ->
        // pemenang per item (beda vendor) -> approval Direktur -> PO per vendor
        // -> Material Tracking otomatis. =====
        $rfq = RequestForQuotation::create([
            'project_id' => $project1->id,
            'code' => RequestForQuotation::generateCode(),
            'status' => 'draft',
            'created_by' => $purchasing->id,
            'notes' => 'Kebutuhan material tahap pelaksanaan pekerjaan.',
        ]);

        $rfqLine1 = RfqItem::create(['request_for_quotation_id' => $rfq->id, 'item_id' => $item1->id, 'qty' => 20]);
        $rfqLine2 = RfqItem::create(['request_for_quotation_id' => $rfq->id, 'item_id' => $item2->id, 'qty' => 2]);

        // Penawaran awal Vendor A.
        $vqA = VendorQuotation::create([
            'request_for_quotation_id' => $rfq->id,
            'vendor_id' => $vendorA->id,
            'reference_number' => 'STM/QUO/0091',
            'quoted_at' => now()->subDays(4),
        ]);
        $vqA1 = VendorQuotationItem::create(['vendor_quotation_id' => $vqA->id, 'rfq_item_id' => $rfqLine1->id, 'qty' => 20, 'unit_price' => 360000, 'subtotal' => 20 * 360000]);
        $vqA2 = VendorQuotationItem::create(['vendor_quotation_id' => $vqA->id, 'rfq_item_id' => $rfqLine2->id, 'qty' => 2, 'unit_price' => 1300000, 'subtotal' => 2 * 1300000]);

        // Penawaran awal Vendor B.
        $vqB = VendorQuotation::create([
            'request_for_quotation_id' => $rfq->id,
            'vendor_id' => $vendorB->id,
            'reference_number' => 'MSI-2026-0456',
            'quoted_at' => now()->subDays(3),
        ]);
        $vqB1 = VendorQuotationItem::create(['vendor_quotation_id' => $vqB->id, 'rfq_item_id' => $rfqLine1->id, 'qty' => 20, 'unit_price' => 340000, 'subtotal' => 20 * 340000]);
        $vqB2 = VendorQuotationItem::create(['vendor_quotation_id' => $vqB->id, 'rfq_item_id' => $rfqLine2->id, 'qty' => 2, 'unit_price' => 1400000, 'subtotal' => 2 * 1400000]);

        // Hasil negosiasi: Vendor A menurunkan harga bearing menjadi lebih murah dari Vendor B.
        $vqA2->update(['unit_price' => 1200000]);

        // Pemenang dipilih berbeda per item (contoh kasus yang diminta):
        // pipa stainless -> Vendor B (lebih murah), bearing -> Vendor A (setelah negosiasi).
        $rfqLine1->update(['awarded_vendor_quotation_item_id' => $vqB1->id]);
        $rfqLine2->update(['awarded_vendor_quotation_item_id' => $vqA2->id]);

        $rfq->submitForApproval($purchasing);
        $rfq->refresh();
        $rfq->approve($direktur);
    }
}
