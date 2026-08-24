<?php

namespace App\Providers;

use App\Services\PaystackService;
use App\Services\WhatsAppCloudApiService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PaystackService::class, fn () => new PaystackService(
            secretKey: config('services.paystack.secret_key'),
            baseUrl: config('services.paystack.base_url'),
        ));

        $this->app->singleton(WhatsAppCloudApiService::class, fn () => new WhatsAppCloudApiService(
            apiVersion: config('services.whatsapp.api_version'),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
