<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Prescription;
use App\Models\Purchase;
use App\Observers\ProductObserver;
use App\Observers\SaleObserver;
use App\Observers\PrescriptionObserver;
use App\Observers\PurchaseObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the NotificationService
        $this->app->singleton(\App\Services\NotificationService::class, function ($app) {
            return new \App\Services\NotificationService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers for automatic notifications
        Product::observe(ProductObserver::class);
        Sale::observe(SaleObserver::class);
        Prescription::observe(PrescriptionObserver::class);
        Purchase::observe(PurchaseObserver::class);
    }
}