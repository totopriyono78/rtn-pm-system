<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\PurchaseOrderPrintController;
use App\Http\Controllers\ReportFileController;
use App\Livewire\Admin\ManageLocations;
use App\Livewire\Admin\ManageUsers;
use App\Livewire\Dashboard;
use App\Livewire\Kpi\DirekturDashboard;
use App\Livewire\Kpi\EmployeeDrilldown;
use App\Livewire\Projects\ManageProjects;
use App\Livewire\Projects\ProjectDetail;
use App\Livewire\Purchasing\ManageItems;
use App\Livewire\Purchasing\ManagePurchaseOrders;
use App\Livewire\Purchasing\ManageRfqs;
use App\Livewire\Purchasing\ManageVendors;
use App\Livewire\Purchasing\MaterialTracking;
use App\Livewire\Purchasing\RfqDetail;
use App\Livewire\Purchasing\VendorDetail;
use App\Livewire\Teknisi\MyReports;
use App\Livewire\Teknisi\MySchedule;
use App\Livewire\Teknisi\SubmitReport;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    // ===== Administrasi (User & Master Lokasi) =====
    Route::middleware('permission:manage-users')->group(function () {
        Route::get('/admin/users', ManageUsers::class)->name('admin.users');
    });

    Route::middleware('permission:manage-projects')->group(function () {
        Route::get('/admin/locations', ManageLocations::class)->name('admin.locations');
    });

    // ===== Project Management =====
    Route::get('/projects', ManageProjects::class)->name('projects.index');
    Route::get('/projects/{project}', ProjectDetail::class)->name('projects.show');

    // ===== Modul Teknisi =====
    Route::middleware('permission:submit-report')->group(function () {
        Route::get('/teknisi/jadwal', MySchedule::class)->name('teknisi.schedule');
        Route::get('/teknisi/laporan/baru', SubmitReport::class)->name('teknisi.report.create');
        Route::get('/teknisi/laporan', MyReports::class)->name('teknisi.report.index');
    });

    Route::get('/reports/files/{reportFile}', ReportFileController::class)->name('reports.files.show');

    // ===== KPI & Work Log =====
    Route::middleware('permission:view-kpi-team')->group(function () {
        Route::get('/kpi', DirekturDashboard::class)->name('kpi.dashboard');
        Route::get('/kpi/karyawan/{user}', EmployeeDrilldown::class)->name('kpi.drilldown');
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
});
