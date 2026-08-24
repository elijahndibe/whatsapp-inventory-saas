<?php

namespace App\Providers;

use App\Services\PaystackService;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
