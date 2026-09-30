<?php

namespace App\Providers;

use App\Models\SystemSetting;
use App\Models\Booking;
use App\Policies\BookingPolicy;
use App\Contracts\AddOnCheckoutGateway;
use App\Services\PaymongoAddOnCheckoutGateway;
use Cloudinary\Cloudinary as CloudinaryClient;
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
        $this->app->singleton(CloudinaryClient::class, function ($app) {
            $cloudinaryUrl = $app['config']->get('cloudinary.cloudinary_url');

            if (filled($cloudinaryUrl)) {
                return new CloudinaryClient($cloudinaryUrl);
            }

            return new CloudinaryClient([
                'cloud' => $app['config']->get('cloudinary.cloud'),
                'url' => $app['config']->get('cloudinary.url'),
            ]);
        });
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
