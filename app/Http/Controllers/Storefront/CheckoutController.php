<?php

namespace App\Http\Controllers\Storefront;

use App\Exceptions\PaystackException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\CheckoutRequest;
use App\Models\Business;
use App\Models\Order;
use App\Services\CartService;
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
    ) {}

    public function create(Business $business): View|RedirectResponse
    {
        abort_unless($business->isActive(), 404);

        $items = $this->cart->detailed($business);

        if ($items->isEmpty()) {
            return redirect()->route('storefront.cart.index', $business)
                ->with('error', 'Your cart is empty.');
        }

        return view('storefront.checkout', [
            'business' => $business,
            'items' => $items,
            'subtotal' => $items->sum('subtotal'),
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

        $order = $this->orders->createFromCart($business, $items, $request->validated());

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
