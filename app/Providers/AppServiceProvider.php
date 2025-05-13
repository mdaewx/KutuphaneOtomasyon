<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use App\Services\AdminService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register AdminService as a singleton for better performance
        $this->app->singleton(AdminService::class, function ($app) {
            return new AdminService();
        });

        // Keep the named binding for backward compatibility
        $this->app->bind('admin', function ($app) {
            return $app->make(AdminService::class);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fix untuk migration string length error
        Schema::defaultStringLength(191);
    }
}
