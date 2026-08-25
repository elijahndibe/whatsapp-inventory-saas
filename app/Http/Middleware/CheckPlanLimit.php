<?php

namespace App\Http\Middleware;

use App\Models\Business;
use App\Models\Product;
use App\Services\FeatureService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage: ->middleware('plan.limit:products') or 'plan.limit:orders'.
 * Resolves the business from the authenticated user (dashboard routes) or
 * the {business} route parameter (storefront routes), so the same
 * middleware works on both sides.
 */
class CheckPlanLimit
{
    public function __construct(private readonly FeatureService $features) {}

    public function handle(Request $request, Closure $next, string $limit): Response
    {
        $business = $request->route('business') instanceof Business
            ? $request->route('business')
            : $request->user()?->business;

        if (! $business) {
            return $next($request);
        }

        $allowed = match ($limit) {
            'products' => $this->features->withinLimit($business, 'products', Product::forBusiness($business->id)->count()),
            'orders' => $this->features->withinLimit($business, 'orders_per_month', \App\Models\Order::withoutGlobalScopes()
                ->where('business_id', $business->id)
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count()),
            default => true,
        };

        if (! $allowed) {
            $message = match ($limit) {
                'products' => 'You have reached your plan\'s product limit. Upgrade to add more products.',
                'orders' => 'This store has reached its monthly order limit on its current plan. Please try again later or contact the business directly.',
                default => 'This action is not available on the current plan.',
            };

            return back()->with('error', $message);
        }

        return $next($request);
    }
}
