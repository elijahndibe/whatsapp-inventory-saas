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
            'plan.limit' => \App\Http\Middleware\CheckPlanLimit::class,
        ]);

        $middleware->appendToGroup('web', \App\Http\Middleware\EnsureBusinessIsActive::class);

        // Paystack/Meta post to these URLs directly — they can't send a CSRF
        // token. The signature checks inside the webhook controllers are
        // what actually authenticate the requests.
        $middleware->validateCsrfTokens(except: ['webhooks/paystack', 'webhooks/whatsapp']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
