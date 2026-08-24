<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the platform super-admin area (Phase 7). Kept separate from
 * business roles/permissions entirely — a business Owner must never be
 * able to reach these routes no matter what permissions they hold.
 */
class EnsureUserIsSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        return $next($request);
    }
}
