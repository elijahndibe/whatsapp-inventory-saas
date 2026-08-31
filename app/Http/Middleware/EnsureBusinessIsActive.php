<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks access for users whose business has been suspended by a
 * super-admin, or whose own account has been deactivated by their
 * Owner/Admin. Super-admin users (no business_id) are exempt.
 */
class EnsureBusinessIsActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && ! $user->isSuperAdmin()) {
            if (! $user->isActive()) {
                Auth::logout();

                return redirect()->route('login')
                    ->withErrors(['email' => 'Your account has been deactivated. Contact your business owner.']);
            }

            if ($user->business && ! $user->business->isActive()) {
                Auth::logout();

                $message = $user->business->isClosed()
                    ? 'This business has been closed.'
                    : 'This business account has been suspended.';

                return redirect()->route('login')->withErrors(['email' => $message]);
            }
        }

        return $next($request);
    }
}
