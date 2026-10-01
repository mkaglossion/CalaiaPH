<?php

namespace App\Providers;

use App\Models\SystemSetting;
use App\Models\Booking;
use App\Policies\BookingPolicy;
use App\Contracts\AddOnCheckoutGateway;
use App\Services\PaymongoAddOnCheckoutGateway;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AddOnCheckoutGateway::class, PaymongoAddOnCheckoutGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Booking::class, BookingPolicy::class);

        // Disable Vite preload tags to fix unused CSS preload warning
        Vite::usePreloadTagAttributes(function () {
            return false;
        });

        if (app()->runningInConsole() && ! app()->runningUnitTests()) {
            return;
        }

        View::share('systemSettings', SystemSetting::cachedData());

        
    }
}
