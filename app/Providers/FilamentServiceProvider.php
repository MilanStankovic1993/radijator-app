<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Product;
use App\Observers\ProductObserver;


class FilamentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Product::observe(ProductObserver::class);
    }
}
