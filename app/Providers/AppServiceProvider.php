<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Pagination\Paginator; // 1. استيراد كلاس الترقيم

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
        // إجبار scheme على HTTPS في بيئة الإنتاج (موجود مسبقاً عندك)
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        // 2. إجبار لارافل على استخدام تنسيق Bootstrap للأزرار (Next/Previous)
        Paginator::useBootstrapFive(); 
    }
}