<?php

namespace App\Providers;

use App\Models\SystemSetting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\View;

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
        // Disable Vite preload tags to fix unused CSS preload warning
        Vite::usePreloadTagAttributes(function () {
            return false;
        });

        View::share('systemSettings', SystemSetting::cachedData());
    }
}
