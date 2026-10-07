<?php

namespace App\Providers;

use App\Models\RequestForQuotation;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
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

        // Client Portal (SRS 4.3) guard 'client': bawaan Laravel, kalau akun
        // SUDAH login (lewat middleware 'guest:client' di /portal/login) akan
        // di-redirect ke route 'dashboard'/'home' pertama yang ditemukan DI
        // SELURUH aplikasi -- tidak tahu ada guard terpisah, jadi selalu
        // kena route 'dashboard' milik staf internal. Karena guard client
        // tidak lolos middleware 'auth' (web) di halaman itu, ujung-ujungnya
        // malah dilempar lagi ke /login staf (lihat Riwayat bug #6). Override
        // ini membuat guard client diarahkan ke client.dashboard miliknya
        // sendiri, simetris dengan redirectGuestsTo() di bootstrap/app.php.
        RedirectIfAuthenticated::redirectUsing(function ($request) {
            return $request->is('portal/*') ? route('client.dashboard') : route('dashboard');
        });

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
