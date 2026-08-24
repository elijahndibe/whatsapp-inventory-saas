<?php

namespace App\Http\Controllers\Storefront;

use App\Exceptions\PaystackException;
use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Order;
use App\Services\PaymentService;
use App\Services\PaystackService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaystackService $paystack,
        private readonly PaymentService $payments,
    ) {}

    /**
     * Where Paystack redirects the customer's browser back to after they
     * pay (or cancel). This is a convenience for a fast confirmation
     * screen — it independently re-verifies with Paystack server-to-server
     * rather than trusting the query string, but the webhook remains the
     * authoritative confirmation path for cases where the customer closes
     * the tab before returning.
     */
    public function callback(Request $request, Business $business): RedirectResponse
    {
        $reference = $request->query('reference') ?? $request->query('trxref');

        if ($reference) {
            try {
                $verified = $this->paystack->verifyTransaction($reference);
                $this->payments->handleVerifiedTransaction($verified);
            } catch (\Throwable) {
                // Swallow — the confirmation page below shows current DB
                // state either way, and the webhook will catch up if this
                // verify call itself failed transiently.
            }
        }

        $order = Order::withoutGlobalScopes()
            ->where('business_id', $business->id)
            ->whereHas('payments', fn ($q) => $q->where('reference', $reference))
            ->first();

        abort_unless($order, 404);

        return redirect()->route('storefront.orders.confirmation', [$business, $order->public_token]);
    }

    /**
     * Starts a fresh payment attempt for an order that is not yet paid —
     * used when checkout-time initialization failed, or the customer
     * abandoned their first Paystack session.
     */
    public function retry(Request $request, Business $business, string $publicToken): RedirectResponse
    {
        $order = Order::withoutGlobalScopes()
            ->where('business_id', $business->id)
            ->where('public_token', $publicToken)
            ->firstOrFail();

        if ($order->payment_status === 'paid') {
            return redirect()->route('storefront.orders.confirmation', [$business, $order->public_token]);
        }

        $email = $order->customer->email ?: $request->string('email')->toString();

        if (! $email) {
            return redirect()->route('storefront.orders.confirmation', [$business, $order->public_token])
                ->with('error', 'An email address is required to pay online.');
        }

        try {
            $result = $this->payments->initializeForOrder($order, $email);
        } catch (PaystackException) {
            return redirect()->route('storefront.orders.confirmation', [$business, $order->public_token])
                ->with('error', 'Unable to start payment right now. Please try again shortly.');
        }

        return redirect()->away($result['authorization_url']);
    }
}
