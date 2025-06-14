<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Pembelian;
use App\Observers\PembelianObserver;
use App\Models\Pembayaran;
use App\Observers\PembayaranObserver;

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
        Pembelian::observe(PembelianObserver::class);
        Pembayaran::observe(PembayaranObserver::class);
    }
}
