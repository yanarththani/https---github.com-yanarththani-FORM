<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\GoogleSheetsService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register GoogleSheetsService
        $this->app->singleton(GoogleSheetsService::class, function ($app) {
            return new GoogleSheetsService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
