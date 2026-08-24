<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'business.active' => \App\Http\Middleware\EnsureBusinessIsActive::class,
            'super_admin' => \App\Http\Middleware\EnsureUserIsSuperAdmin::class,
        ]);

        $middleware->appendToGroup('web', \App\Http\Middleware\EnsureBusinessIsActive::class);

        // Paystack posts to this URL directly — it can't send a CSRF token.
        // The signature check inside PaystackWebhookController is what
        // actually authenticates the request.
        $middleware->validateCsrfTokens(except: ['webhooks/paystack']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
