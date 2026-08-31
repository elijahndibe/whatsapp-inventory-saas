<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Sentry\Laravel\Integration;

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
        $middleware->appendToGroup('web', \App\Http\Middleware\SecurityHeaders::class);

        // Paystack/Meta post to these URLs directly — they can't send a CSRF
        // token. The signature checks inside the webhook controllers are
        // what actually authenticate the requests.
        $middleware->validateCsrfTokens(except: ['webhooks/paystack', 'webhooks/whatsapp']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // No-op when SENTRY_LARAVEL_DSN is blank — same "fails open, not
        // closed" convention as the rest of this app's optional
        // integrations (WhatsApp Embedded Signup, phone verification):
        // an unconfigured DSN means errors just aren't reported anywhere,
        // never that the app breaks.
        Integration::handles($exceptions);
    })->create();
