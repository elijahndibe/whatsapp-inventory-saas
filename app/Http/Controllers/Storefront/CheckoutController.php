<?php

namespace App\Http\Controllers\Storefront;

use App\Exceptions\PaystackException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\CheckoutRequest;
use App\Models\Business;
use App\Models\Order;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\FeatureService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\WhatsAppMessageFormatter;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly OrderService $orders,
        private readonly PaymentService $payments,
        private readonly FeatureService $features,
        private readonly CouponService $coupons,
    ) {}

    public function create(Business $business): View|RedirectResponse
    {
        abort_unless($business->isActive(), 404);

        $items = $this->cart->detailed($business);

        if ($items->isEmpty()) {
            return redirect()->route('storefront.cart.index', $business)
                ->with('error', 'Your cart is empty.');
        }

        $subtotal = (float) $items->sum('subtotal');
        $couponCode = $this->cart->appliedCouponCode($business->id);
        $couponDiscount = $couponCode ? $this->coupons->validate($business, $couponCode, $subtotal)['discount'] : 0.0;

        return view('storefront.checkout', [
            'business' => $business,
            'items' => $items,
            'subtotal' => $subtotal,
            'canPayOnline' => $this->features->enabled($business, 'paystack'),
            'appliedCouponCode' => $couponCode,
            'couponDiscount' => $couponDiscount,
        ]);
    }

    public function store(CheckoutRequest $request, Business $business): RedirectResponse
    {
        abort_unless($business->isActive(), 404);

        $items = $this->cart->detailed($business);

        if ($items->isEmpty()) {
            return redirect()->route('storefront.cart.index', $business)->with('error', 'Your cart is empty.');
        }

        foreach ($items as $item) {
            if (! $business->allow_overselling && $item->product->stock_quantity < $item->quantity) {
                return redirect()->route('storefront.cart.index', $business)
                    ->with('error', "Only {$item->product->stock_quantity} of \"{$item->product->name}\" available.");
            }
        }

        if ($request->validated('payment_method') === 'paystack' && ! $this->features->enabled($business, 'paystack')) {
            return back()->withInput()->with('error', 'Online payment is not available for this store right now. Please order via WhatsApp instead.');
        }

        $orderData = $request->validated();

        // Re-validated here, never trusted from the cart page's earlier
        // check — a code can expire, hit its usage limit, or (now that we
        // finally have a phone number) turn out already used by this exact
        // customer, in the time between "Apply" and submitting the order.
        if ($couponCode = $this->cart->appliedCouponCode($business->id)) {
            $subtotal = (float) $items->sum('subtotal');
            $result = $this->coupons->validate($business, $couponCode, $subtotal, $orderData['phone']);

            if ($result['error']) {
                $this->cart->removeCoupon($business->id);

                return redirect()->route('storefront.cart.index', $business)
                    ->with('error', "Your coupon could no longer be applied: {$result['error']}");
            }

            $orderData['coupon'] = $result['coupon'];
            $orderData['coupon_discount'] = $result['discount'];
        }

        $order = $this->orders->createFromCart($business, $items, $orderData);

        $this->cart->clear($business->id);

        if ($request->validated('payment_method') === 'paystack') {
            try {
                $result = $this->payments->initializeForOrder($order, $request->validated('email'));

                return redirect()->away($result['authorization_url']);
            } catch (PaystackException) {
                return redirect()->route('storefront.orders.confirmation', [$business, $order->public_token])
                    ->with('error', 'Unable to start online payment right now. You can retry payment or order via WhatsApp below.');
            }
        }

        return redirect()->route('storefront.orders.confirmation', [$business, $order->public_token]);
    }

    public function confirmation(Business $business, string $publicToken, WhatsAppMessageFormatter $formatter): View
    {
        $order = Order::withoutGlobalScopes()
            ->where('business_id', $business->id)
            ->where('public_token', $publicToken)
            ->with('items')
            ->firstOrFail();

        return view('storefront.confirmation', [
            'business' => $business,
            'order' => $order,
            'whatsappUrl' => $formatter->chatUrl($order),
        ]);
    }
}
