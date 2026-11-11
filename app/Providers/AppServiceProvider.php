<?php

namespace App\Providers;

use App\Services\OngkirInterface;
use App\Services\OngkirService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Layanan ongkir memakai driver mock bila belum ada API key;
        // ganti ke driver sungguhan cukup di satu tempat ini.
        $this->app->bind(OngkirInterface::class, OngkirService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
