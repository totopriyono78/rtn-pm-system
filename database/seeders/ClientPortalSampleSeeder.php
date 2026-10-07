<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ClientUser;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\Region;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Data contoh untuk mencoba Client Portal (SRS 4.3) dari sudut pandang
 * client: 1 Customer, 1 akun ClientUser, 1 Contract spesifik, 1 Project
 * dengan progress & dokumen, serta 2 Invoice (sent & paid) -- supaya saat
 * login di /portal/login, dashboard client tidak kosong.
 *
 * Dijalankan terpisah (TIDAK didaftarkan di DatabaseSeeder), sama seperti
 * pola ChartOfAccountSeeder/WebsiteContentSeeder -- jalankan manual:
 *   php artisan db:seed --class=ClientPortalSampleSeeder
 *
 * Idempotent: aman dijalankan ulang, tidak membuat data duplikat.
 */
class ClientPortalSampleSeeder extends Seeder
{
    public function run(): void
    {
        // ===== Unit internal penanggung jawab (pakai yang sudah ada kalau ada) =====
        $region = Region::query()->first() ?? Region::create([
            'code' => 'JBB',
            'name' => 'Region Jawa Bagian Barat',
        ]);

        $unit = Unit::query()->first() ?? Unit::create([
            'region_id' => $region->id,
            'code' => 'IT-JKT',
            'name' => 'IT Jakarta',
        ]);

        // ===== PIC proyek (pakai PM yang sudah ada kalau ada) =====
        $pic = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'Project Manager'))->first()
            ?? User::query()->first();

        // ===== Customer =====
        $customer = Customer::query()->firstOrCreate(
            ['code' => 'CUST-DEMO1'],
            [
                'name' => 'PT Sumber Air Sejahtera',
                'address' => 'Jl. Raya Industri No. 45, Cikarang, Jawa Barat',
                'npwp' => '03.456.789.0-123.000',
                'pic_name' => 'Hendra Kurniawan',
                'pic_phone' => '0812-3456-7890',
                'pic_email' => 'hendra.kurniawan@sumberairsejahtera.co.id',
                'is_active' => true,
            ]
        );

        // ===== Akun login Client Portal =====
        $clientUser = ClientUser::query()->updateOrCreate(
            ['email' => 'client.demo@sumberairsejahtera.co.id'],
            [
                'customer_id' => $customer->id,
                'name' => 'Hendra Kurniawan',
                'password' => 'password',
                'is_active' => true,
            ]
        );

        // ===== Contract (spesifik, langsung membentuk 1 Project) =====
        $contract = Contract::query()->firstOrCreate(
            ['contract_number' => 'KTR-DEMO-0001'],
            [
                'customer_id' => $customer->id,
                'unit_id' => $unit->id,
                'contract_type' => 'spesifik',
                'contract_date' => now()->subDays(45),
                'start_date' => now()->subDays(40),
                'end_date' => now()->addDays(50),
                'scope_description' => 'Overhaul 2 unit pompa sentrifugal area produksi.',
                'fixed_value' => 285000000,
                'status' => 'active',
                'created_by' => $pic?->id,
            ]
        );

        // ===== Project =====
        $project = Project::query()->firstOrCreate(
            ['contract_id' => $contract->id],
            [
                'unit_id' => $unit->id,
                'pic_user_id' => $pic?->id,
                'name' => 'Overhaul Pompa Sentrifugal P-301 & P-302',
                'description' => 'Overhaul menyeluruh dua unit pompa sentrifugal area produksi milik PT Sumber Air Sejahtera, termasuk penggantian bearing & seal, serta uji performa pasca-overhaul.',
                'budget' => 220000000,
                'project_value' => 285000000,
                'start_date' => now()->subDays(40),
                'end_date' => now()->addDays(50),
                'status' => 'ongoing',
                'type' => 'project',
            ]
        );

        // ===== Activities (progress ~ sebagian selesai, supaya progress bar client tidak 0% / 100%) =====
        if ($project->activities()->count() === 0) {
            Activity::create(['project_id' => $project->id, 'name' => 'Site Survey & Pembongkaran Unit', 'status' => 'selesai', 'planned_hours' => 16, 'order_no' => 1, 'start_date' => now()->subDays(40), 'end_date' => now()->subDays(36)]);
            Activity::create(['project_id' => $project->id, 'name' => 'Penggantian Bearing & Seal P-301', 'status' => 'selesai', 'planned_hours' => 40, 'order_no' => 2, 'start_date' => now()->subDays(35), 'end_date' => now()->subDays(20)]);
            Activity::create(['project_id' => $project->id, 'name' => 'Penggantian Bearing & Seal P-302', 'status' => 'sedang_dikerjakan', 'planned_hours' => 40, 'order_no' => 3, 'start_date' => now()->subDays(15), 'end_date' => now()->addDays(5)]);
            Activity::create(['project_id' => $project->id, 'name' => 'Uji Performa & Commissioning', 'status' => 'belum_dimulai', 'planned_hours' => 24, 'order_no' => 4, 'start_date' => now()->addDays(10), 'end_date' => now()->addDays(20)]);
            Activity::create(['project_id' => $project->id, 'name' => 'Serah Terima & FAT', 'status' => 'belum_dimulai', 'planned_hours' => 8, 'order_no' => 5, 'start_date' => now()->addDays(45), 'end_date' => now()->addDays(50)]);
        }

        // ===== Dokumen proyek yang terlihat oleh client (is_client_visible = true) =====
        $bappDoc = ProjectDocument::query()->where('project_id', $project->id)->where('category', 'bapp_ro')->first();
        if (! $bappDoc) {
            $bappPath = 'project-documents/'.$project->id.'/bapp_ro/BAPP_RO_P301_P302_Tahap1.txt';
            Storage::disk('local')->put($bappPath, "BERITA ACARA PEMERIKSAAN PEKERJAAN (BAPP) - RELEASE ORDER\n\nProyek: Overhaul Pompa Sentrifugal P-301 & P-302\nCustomer: PT Sumber Air Sejahtera\nTahap: 1 (Site Survey & Penggantian Bearing/Seal P-301)\nStatus: Pekerjaan selesai dan diterima baik oleh perwakilan customer.\n\n(Dokumen contoh/demo untuk Client Portal.)");
            $bappDoc = ProjectDocument::create([
                'project_id' => $project->id,
                'category' => 'bapp_ro',
                'is_client_visible' => true,
                'disk_path' => $bappPath,
                'original_name' => 'BAPP RO Tahap 1 - Overhaul P-301 P-302.txt',
                'mime_type' => 'text/plain',
                'size_bytes' => Storage::disk('local')->size($bappPath),
                'uploaded_by' => $pic?->id,
            ]);
        }

        $drawingDoc = ProjectDocument::query()->where('project_id', $project->id)->where('category', 'drawing')->first();
        if (! $drawingDoc) {
            $drawingPath = 'project-documents/'.$project->id.'/drawing/Drawing_Layout_Pompa_P301_P302.txt';
            Storage::disk('local')->put($drawingPath, "DRAWING LAYOUT POMPA P-301 & P-302\n\n(Dokumen contoh/demo untuk Client Portal -- pada implementasi nyata berupa file gambar teknik/PDF.)");
            $drawingDoc = ProjectDocument::create([
                'project_id' => $project->id,
                'category' => 'drawing',
                'is_client_visible' => true,
                'disk_path' => $drawingPath,
                'original_name' => 'Drawing Layout Pompa P-301 P-302.txt',
                'mime_type' => 'text/plain',
                'size_bytes' => Storage::disk('local')->size($drawingPath),
                'uploaded_by' => $pic?->id,
            ]);
        }

        // Dokumen internal (TIDAK terlihat client) -- supaya kontras terlihat jelas saat demo.
        $internalDoc = ProjectDocument::query()->where('project_id', $project->id)->where('category', 'bal_bulanan')->first();
        if (! $internalDoc) {
            $internalPath = 'project-documents/'.$project->id.'/bal_bulanan/BAL_Bulanan_Internal.txt';
            Storage::disk('local')->put($internalPath, "BERITA ACARA LAPORAN (BAL) BULANAN -- INTERNAL\n\n(Dokumen contoh/demo, sengaja TIDAK ditandai is_client_visible untuk menunjukkan bahwa client TIDAK bisa melihat dokumen ini di portal.)");
            ProjectDocument::create([
                'project_id' => $project->id,
                'category' => 'bal_bulanan',
                'is_client_visible' => false,
                'disk_path' => $internalPath,
                'original_name' => 'BAL Bulanan (Internal).txt',
                'mime_type' => 'text/plain',
                'size_bytes' => Storage::disk('local')->size($internalPath),
                'uploaded_by' => $pic?->id,
            ]);
        }

        // ===== Invoice: 1 terkirim (sent), 1 lunas (paid) -- draft sengaja tidak dibuat
        // karena draft tidak boleh terlihat di Client Portal (lihat ClientInvoices.php). =====
        $invoiceSent = Invoice::query()->firstOrCreate(
            ['invoice_number' => 'INV-DEMO-0001'],
            [
                'project_id' => $project->id,
                'project_document_id' => $bappDoc->id,
                'invoice_date' => now()->subDays(18),
                'due_date' => now()->addDays(12),
                'period_label' => 'Termin 1 - Tahap Site Survey & Penggantian P-301',
                'subtotal' => 95000000,
                'tax_percent' => 11,
                'status' => 'sent',
                'notes' => 'Penagihan termin 1 sesuai BAPP RO Tahap 1.',
                'created_by' => $pic?->id,
                'sent_at' => now()->subDays(17),
            ]
        );

        Invoice::query()->firstOrCreate(
            ['invoice_number' => 'INV-DEMO-0000'],
            [
                'project_id' => $project->id,
                'invoice_date' => now()->subDays(50),
                'due_date' => now()->subDays(20),
                'period_label' => 'Uang Muka (DP) 30%',
                'subtotal' => 85500000,
                'tax_percent' => 11,
                'status' => 'paid',
                'notes' => 'Uang muka 30% dari nilai kontrak, dibayar di awal sebelum mobilisasi.',
                'created_by' => $pic?->id,
                'sent_at' => now()->subDays(49),
                'paid_at' => now()->subDays(35),
                'payment_reference' => 'TRF-BCA-20260822-00981',
            ]
        );

        $this->command?->info('Sample Client Portal: customer="'.$customer->name.'", login=client.demo@sumberairsejahtera.co.id / password=password, project="'.$project->name.'".');
    }
}
