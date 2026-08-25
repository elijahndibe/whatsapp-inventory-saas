<?php

namespace App\Http\Controllers;

use App\Exceptions\PaystackException;
use App\Models\Order;
use App\Models\Plan;
use App\Models\PlanFeature;
use App\Models\Product;
use App\Services\PaystackService;
use App\Services\PlatformSettingsService;
use App\Services\SubscriptionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BillingController extends Controller
{
    public function __construct(
        private readonly SubscriptionService $subscriptions,
        private readonly PaystackService $paystack,
        private readonly PlatformSettingsService $settings,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('manage settings');

        $business = $request->user()->business;
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        $currentPlan = $this->subscriptions->currentPlan($business);
        $subscription = $business->currentSubscription();
        $subscriptionSystemEnabled = $this->settings->subscriptionSystemEnabled();

        $usage = [
            'products' => Product::forBusiness($business->id)->count(),
            'orders_this_month' => Order::withoutGlobalScopes()
                ->where('business_id', $business->id)
                ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
        ];

        $featuresByPlan = PlanFeature::with('feature')
            ->whereIn('plan_id', $plans->pluck('id'))
            ->get()
            ->groupBy('plan_id');

        return view('billing.index', compact('plans', 'currentPlan', 'subscription', 'usage', 'business', 'subscriptionSystemEnabled', 'featuresByPlan'));
    }

    public function subscribe(Request $request, Plan $plan): RedirectResponse
    {
        $this->authorize('manage settings');

        if (! $this->settings->subscriptionSystemEnabled()) {
            return redirect()->route('billing.index')->with('error', 'Subscriptions are not currently available — every plan feature listed here is already included for free.');
        }

        $business = $request->user()->business;

        if ($plan->isFree()) {
            $this->subscriptions->subscribeToPlan($business, $plan);

            return redirect()->route('billing.index')->with('status', "You're now on the {$plan->name} plan.");
        }

        $reference = 'SUB-'.strtoupper(Str::random(14));

        try {
            $result = $this->paystack->initializeTransaction([
                'email' => $request->user()->email,
                'amount' => (int) round($plan->price * 100),
                'currency' => $plan->currency,
                'reference' => $reference,
                'callback_url' => route('billing.callback'),
                'metadata' => [
                    'business_id' => $business->id,
                    'plan_id' => $plan->id,
                    'type' => 'subscription',
                ],
            ]);
        } catch (PaystackException) {
            return redirect()->route('billing.index')->with('error', 'Unable to start payment right now. Please try again shortly.');
        }

        return redirect()->away($result['authorization_url']);
    }

    public function callback(Request $request): RedirectResponse
    {
        $reference = $request->query('reference') ?? $request->query('trxref');

        if ($reference) {
            try {
                $verified = $this->paystack->verifyTransaction($reference);
                $this->subscriptions->activateFromVerifiedPayment($verified);
            } catch (PaystackException) {
                // The webhook will catch up if this verify call failed transiently.
            }
        }

        return redirect()->route('billing.index');
    }
}
