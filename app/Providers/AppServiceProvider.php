<?php

namespace App\Providers;

use App\Models\RequestForQuotation;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Supaya nama hari/bulan (translatedFormat, diffForHumans, dll) tampil dalam Bahasa Indonesia.
        Carbon::setLocale(config('app.locale'));

        // Badge "menunggu approval" di menu sidebar (tampil di semua halaman,
        // bukan cuma dashboard) supaya approver langsung sadar ada RFQ yang
        // perlu diproses begitu masuk aplikasi.
        View::composer('partials.sidebar-nav', function ($view) {
            $user = Auth::user();

            $pendingApprovalCount = ($user && $user->hasPermissionTo('approve-purchasing'))
                ? RequestForQuotation::where('status', 'submitted')->count()
                : 0;

            $view->with('pendingApprovalCount', $pendingApprovalCount);
        });
    }
}
