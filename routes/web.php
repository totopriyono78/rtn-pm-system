<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Client\ClientAuthenticatedSessionController;
use App\Http\Controllers\Client\ClientDocumentController;
use App\Http\Controllers\Client\ClientInvoicePrintController;
use App\Http\Controllers\ProjectDocumentController;
use App\Http\Controllers\ProjectDocumentZipController;
use App\Http\Controllers\SalesOrderDocumentController;
use App\Http\Controllers\DeliveryGatepassFileController;
use App\Http\Controllers\PurchaseOrderPrintController;
use App\Http\Controllers\InvoicePrintController;
use App\Http\Controllers\ReportFileController;
use App\Http\Controllers\SafetyTalkPhotoController;
use App\Http\Controllers\CashAdvanceReceiptController;
use App\Http\Controllers\PayslipPrintController;
use App\Http\Controllers\UserSignatureFileController;
use App\Http\Controllers\WebsiteController;
use App\Livewire\Admin\AttendanceOverrides;
use App\Livewire\Assets\ManagePumpAssets;
use App\Livewire\Admin\KpiSettings;
use App\Livewire\Admin\ManageLocations;
use App\Livewire\Admin\ManageUsers;
use App\Livewire\Admin\ManageRolePermissions;
use App\Livewire\CostControl\ManageProjectCost;
use App\Livewire\Admin\EmployeeSalaries;
use App\Livewire\Admin\PayrollRuns;
use App\Livewire\Admin\PayrollRunDetail;
use App\Livewire\Finance\ManageCashBankAccounts;
use App\Livewire\Finance\CashBankLedger;
use App\Livewire\Finance\FinancialReports;
use App\Livewire\Finance\ManageChartOfAccounts;
use App\Livewire\Finance\JournalEntries;
use App\Livewire\Finance\ManageOtherReceivables;
use App\Livewire\Finance\ManageFixedAssets;
use App\Livewire\Finance\ManageBudgets;
use App\Livewire\Finance\GeneralLedgerReport;
use App\Livewire\Finance\TrialBalance;
use App\Livewire\Contracts\ContractDetail;
use App\Livewire\Contracts\CustomerQuotationDetail;
use App\Livewire\Contracts\ManageContracts;
use App\Livewire\Contracts\ManageCustomers;
use App\Livewire\Contracts\ManageClientUsers;
use App\Livewire\Contracts\ManageInvoices;
use App\Livewire\Contracts\ReleaseOrderDetail;
use App\Livewire\Dashboard;
use App\Livewire\Notifications\NotificationCenter;
use App\Livewire\Admin\AuditTrail;
use App\Livewire\Sales\ManageProspects;
use App\Livewire\Sales\ManageSalesOrders;
use App\Livewire\Sales\MarketingDashboard;
use App\Livewire\Sales\ProspectDetail;
use App\Livewire\Sales\SalesOrderDetail;
use App\Livewire\Client\ClientDashboard;
use App\Livewire\Client\ClientProjectDetail;
use App\Livewire\Client\ClientInvoices;
use App\Livewire\Kpi\DirekturDashboard;
use App\Livewire\Operasional\ManageGatepasses;
use App\Livewire\Operasional\ManageCashAdvances;
use App\Livewire\Operasional\WeeklyReportRecap;
use App\Livewire\Kpi\EmployeeDrilldown;
use App\Livewire\Profile\ManageSignature;
use App\Livewire\Projects\ManageProjects;
use App\Livewire\Projects\ProjectDetail;
use App\Livewire\Purchasing\ManageItems;
use App\Livewire\Purchasing\ManagePurchaseOrders;
use App\Livewire\Purchasing\ManageRfqs;
use App\Livewire\Purchasing\ManageVendors;
use App\Livewire\Purchasing\MaterialTracking;
use App\Livewire\Purchasing\RfqDetail;
use App\Livewire\Purchasing\VendorDetail;
use App\Livewire\Teknisi\CheckIn;
use App\Livewire\Teknisi\CashAdvances;
use App\Livewire\Teknisi\MyReports;
use App\Livewire\Teknisi\MySchedule;
use App\Livewire\Teknisi\SafetyTalks;
use App\Livewire\Teknisi\SubmitReport;
use App\Livewire\Website\ManageWebsitePortfolio;
use App\Livewire\Website\ManageWebsiteProducts;
use App\Livewire\Website\ManageWebsiteProfile;
use App\Livewire\Website\ManageWebsiteServices;
use App\Livewire\Website\ManageWebsiteSlides;
use App\Livewire\Website\WebsiteContactMessages;
use Illuminate\Support\Facades\Route;

// ===== Company Website (pilar publik, tanpa login -- SRS v2.0 bab 4.2) =====
Route::get('/', [WebsiteController::class, 'home'])->name('website.home');
Route::get('/tentang-kami', [WebsiteController::class, 'about'])->name('website.about');
Route::get('/produk', [WebsiteController::class, 'products'])->name('website.products');
Route::get('/layanan', [WebsiteController::class, 'services'])->name('website.services');
Route::get('/portofolio', [WebsiteController::class, 'portfolio'])->name('website.portfolio');
Route::get('/kontak', [WebsiteController::class, 'contact'])->name('website.contact');
Route::post('/kontak', [WebsiteController::class, 'submitContact'])->name('website.contact.submit');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/notifikasi', NotificationCenter::class)->name('notifications.index');

    // ===== Administrasi (User & Master Lokasi) =====
    Route::middleware('permission:manage-users')->group(function () {
        Route::get('/admin/users', ManageUsers::class)->name('admin.users');
    });

    Route::middleware('permission:manage-roles-permissions')->group(function () {
        Route::get('/admin/role-permissions', ManageRolePermissions::class)->name('admin.role-permissions');
    });

    Route::middleware('permission:manage-cost-control')->group(function () {
        Route::get('/cost-control', ManageProjectCost::class)->name('cost-control.index');
    });

    Route::middleware('permission:manage-projects')->group(function () {
        Route::get('/admin/locations', ManageLocations::class)->name('admin.locations');
    });

    Route::middleware('permission:manage-attendance-overrides')->group(function () {
        Route::get('/admin/attendance-overrides', AttendanceOverrides::class)->name('admin.attendance-overrides');
    });

    // ===== Master Data Aset =====
    Route::middleware('permission:manage-pump-assets')->group(function () {
        Route::get('/assets/pump-units', ManagePumpAssets::class)->name('assets.pump-units');
    });

    // ===== Kontrak & Komersial (Project Controller) =====
    Route::middleware('permission:manage-customers')->group(function () {
        Route::get('/contracts/customers', ManageCustomers::class)->name('contracts.customers');
        Route::get('/contracts/customers/{customer}/client-users', ManageClientUsers::class)->name('contracts.customers.client-users');
    });

    Route::middleware(['permission:manage-contracts|view-contract-value'])->group(function () {
        Route::get('/contracts', ManageContracts::class)->name('contracts.index');
        Route::get('/contracts/{contract}', ContractDetail::class)->name('contracts.show');
        Route::get('/contracts/release-orders/{releaseOrder}', ReleaseOrderDetail::class)->name('contracts.release-orders.show');
    });

    Route::middleware(['permission:manage-invoices|view-contract-value'])->group(function () {
        Route::get('/contracts/invoices', ManageInvoices::class)->name('invoices.index');
        Route::get('/contracts/invoices/{invoice}/cetak', InvoicePrintController::class)->name('invoices.print');
    });

    Route::middleware(['permission:manage-contracts|approve-quotation|view-contract-value'])->group(function () {
        Route::get('/contracts/quotations/{quotation}', CustomerQuotationDetail::class)->name('contracts.quotations.show');
    });

    // ===== Marketing & Sales Support (SRS 4.5 CRM & Pipeline, 4.6 Sales Order) =====
    Route::middleware('permission:manage-prospects')->group(function () {
        Route::get('/sales/prospects', ManageProspects::class)->name('sales.prospects.index');
        Route::get('/sales/prospects/{prospect}', ProspectDetail::class)->name('sales.prospects.show');
    });

    Route::middleware('permission:manage-sales-orders')->group(function () {
        Route::get('/sales/orders', ManageSalesOrders::class)->name('sales.orders.index');
        Route::get('/sales/orders/{salesOrder}', SalesOrderDetail::class)->name('sales.orders.show');
        Route::get('/sales/orders/documents/{salesOrderDocument}', SalesOrderDocumentController::class)->name('sales.orders.documents.show');
    });

    Route::middleware('permission:view-sales-dashboard')->group(function () {
        Route::get('/sales/dashboard', MarketingDashboard::class)->name('sales.dashboard');
    });

    // ===== Tanda Tangan Digital =====
    Route::get('/profile/signature', ManageSignature::class)->name('profile.signature');
    Route::get('/signatures/{userSignature}', UserSignatureFileController::class)->name('signatures.show');

    // ===== Project Management =====
    Route::get('/projects', ManageProjects::class)->name('projects.index');
    Route::get('/projects/{project}', ProjectDetail::class)->name('projects.show');
    Route::get('/projects/documents/{projectDocument}', ProjectDocumentController::class)->name('projects.documents.show');
    Route::get('/projects/{project}/documents/zip', ProjectDocumentZipController::class)->name('projects.documents.zip');

    // ===== Modul Teknisi =====
    Route::middleware('permission:submit-report')->group(function () {
        Route::get('/teknisi/presensi', CheckIn::class)->name('teknisi.checkin');
        Route::get('/teknisi/jadwal', MySchedule::class)->name('teknisi.schedule');
        Route::get('/teknisi/laporan/baru', SubmitReport::class)->name('teknisi.report.create');
        Route::get('/teknisi/laporan', MyReports::class)->name('teknisi.report.index');
        Route::get('/teknisi/safety-talk', SafetyTalks::class)->name('teknisi.safety-talk');
    });

    Route::middleware('permission:request-cash-advance')->group(function () {
        Route::get('/teknisi/kasbon', CashAdvances::class)->name('teknisi.cash-advances');
    });

    Route::get('/reports/files/{reportFile}', ReportFileController::class)->name('reports.files.show');
    Route::get('/safety-talks/{safetyTalk}/photo', SafetyTalkPhotoController::class)->name('safety-talks.photo');
    Route::get('/cash-advance-expenses/{cashAdvanceExpense}/receipt', CashAdvanceReceiptController::class)->name('cash-advance-expenses.receipt');

    // ===== KPI & Work Log =====
    Route::middleware('permission:view-kpi-team')->group(function () {
        Route::get('/kpi', DirekturDashboard::class)->name('kpi.dashboard');
        Route::get('/kpi/karyawan/{user}', EmployeeDrilldown::class)->name('kpi.drilldown');
    });

    Route::middleware('permission:manage-kpi-settings')->group(function () {
        Route::get('/admin/kpi-settings', KpiSettings::class)->name('admin.kpi-settings');
    });

    // ===== Payroll (SRS 4.17) =====
    Route::middleware('permission:manage-payroll')->group(function () {
        Route::get('/admin/payroll/komponen-gaji', EmployeeSalaries::class)->name('admin.employee-salaries');
        Route::get('/admin/payroll', PayrollRuns::class)->name('admin.payroll.index');
        Route::get('/admin/payroll/{payrollRun}', PayrollRunDetail::class)->name('admin.payroll.show');
        Route::get('/payslips/{payslip}/cetak', PayslipPrintController::class)->name('payslips.print');
    });

    // ===== Cash & Bank (SRS 4.14, bagian ringan) =====
    Route::middleware('permission:manage-cash-bank')->group(function () {
        Route::get('/admin/cash-bank/akun', ManageCashBankAccounts::class)->name('admin.cash-bank-accounts');
        Route::get('/admin/cash-bank', CashBankLedger::class)->name('admin.cash-bank.index');
    });

    Route::middleware('permission:view-financial-reports')->group(function () {
        Route::get('/admin/laporan-keuangan', FinancialReports::class)->name('admin.financial-reports');
        Route::get('/admin/audit-trail', AuditTrail::class)->name('admin.audit-trail');
    });

    Route::middleware('permission:manage-general-ledger')->group(function () {
        Route::get('/admin/chart-of-accounts', ManageChartOfAccounts::class)->name('admin.chart-of-accounts');
    });

    Route::middleware(['permission:manage-general-ledger|view-financial-reports'])->group(function () {
        Route::get('/admin/jurnal', JournalEntries::class)->name('admin.journal-entries');
        Route::get('/admin/buku-besar', GeneralLedgerReport::class)->name('admin.general-ledger');
        Route::get('/admin/neraca-saldo', TrialBalance::class)->name('admin.trial-balance');
    });

    // ===== Piutang Lain-lain -- "AR murni" (SRS 4.14, lanjutan GL/Chart of Account) =====
    Route::middleware('permission:manage-other-receivables')->group(function () {
        Route::get('/finance/piutang-lain', ManageOtherReceivables::class)->name('finance.other-receivables');
    });

    // ===== Asset Management (SRS 4.14, lanjutan GL/Chart of Account) =====
    Route::middleware('permission:manage-fixed-assets')->group(function () {
        Route::get('/finance/aset-tetap', ManageFixedAssets::class)->name('finance.fixed-assets');
    });

    // ===== Budgeting formal (SRS 4.14, lanjutan GL/Chart of Account) =====
    Route::middleware('permission:manage-budgets|approve-budgets')->group(function () {
        Route::get('/finance/anggaran', ManageBudgets::class)->name('finance.budgets');
    });

    // ===== Purchasing =====
    Route::middleware('permission:manage-purchasing')->group(function () {
        Route::get('/purchasing/items', ManageItems::class)->name('purchasing.items');
    });

    Route::middleware('permission:view-purchasing')->group(function () {
        Route::get('/purchasing/vendors', ManageVendors::class)->name('purchasing.vendors');
        Route::get('/purchasing/vendors/{vendor}', VendorDetail::class)->name('purchasing.vendors.show');

        Route::get('/purchasing/rfq', ManageRfqs::class)->name('purchasing.rfq');
        Route::get('/purchasing/rfq/{rfq}', RfqDetail::class)->name('purchasing.rfq.show');

        Route::get('/purchasing/purchase-orders', ManagePurchaseOrders::class)->name('purchasing.po');
        Route::get('/purchasing/purchase-orders/{purchaseOrder}/cetak', PurchaseOrderPrintController::class)->name('purchasing.po.print');

        Route::get('/purchasing/material-tracking', MaterialTracking::class)->name('purchasing.tracking');
    });

    // ===== Admin Operasional (Admin Kantor & Admin Purchase) =====
    Route::middleware('permission:view-weekly-recap')->group(function () {
        Route::get('/admin/rekap-laporan', WeeklyReportRecap::class)->name('admin.weekly-recap');
    });

    Route::middleware('permission:manage-delivery-gatepass')->group(function () {
        Route::get('/admin/gatepass', ManageGatepasses::class)->name('admin.gatepasses');
        Route::get('/admin/gatepass/{deliveryGatepass}/file', DeliveryGatepassFileController::class)->name('operasional.gatepasses.file');
    });

    Route::middleware('permission:manage-cash-advances')->group(function () {
        Route::get('/admin/kasbon', ManageCashAdvances::class)->name('admin.cash-advances');
    });

    // ===== CMS Company Website =====
    Route::middleware('permission:manage-website')->group(function () {
        Route::get('/admin/website/profile', ManageWebsiteProfile::class)->name('admin.website.profile');
        Route::get('/admin/website/products', ManageWebsiteProducts::class)->name('admin.website.products');
        Route::get('/admin/website/services', ManageWebsiteServices::class)->name('admin.website.services');
        Route::get('/admin/website/portfolio', ManageWebsitePortfolio::class)->name('admin.website.portfolio');
        Route::get('/admin/website/slides', ManageWebsiteSlides::class)->name('admin.website.slides');
        Route::get('/admin/website/messages', WebsiteContactMessages::class)->name('admin.website.messages');
    });
});

// ===== Client Portal (SRS 4.3) -- guard terpisah 'client', sesi & login
// sama sekali tidak bercampur dengan sesi karyawan internal (guard 'web'). =====
Route::prefix('portal')->group(function () {
    Route::middleware('guest:client')->group(function () {
        Route::get('/login', [ClientAuthenticatedSessionController::class, 'create'])->name('client.login');
        Route::post('/login', [ClientAuthenticatedSessionController::class, 'store']);
    });

    Route::middleware(['auth:client', 'client.active'])->group(function () {
        Route::post('/logout', [ClientAuthenticatedSessionController::class, 'destroy'])->name('client.logout');

        Route::get('/', ClientDashboard::class)->name('client.dashboard');
        Route::get('/projects/{project}', ClientProjectDetail::class)->name('client.projects.show');
        Route::get('/invoices', ClientInvoices::class)->name('client.invoices');
        Route::get('/invoices/{invoice}/cetak', ClientInvoicePrintController::class)->name('client.invoices.print');
        Route::get('/documents/{projectDocument}', ClientDocumentController::class)->name('client.documents.download');
    });
});
