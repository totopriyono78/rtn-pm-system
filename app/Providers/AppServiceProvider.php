<?php

namespace App\Providers;

use Illuminate\Support\Carbon;
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
    }
}
