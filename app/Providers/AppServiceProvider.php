<?php

namespace App\Providers;

use App\Models\Banner;
use App\Models\Order;
use App\Models\Product;
use App\Observers\BannerObserver;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
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
        Order::observe(OrderObserver::class);
        Banner::observe(BannerObserver::class);
    }
}
