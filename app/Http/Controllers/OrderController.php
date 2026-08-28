<?php

namespace App\Http\Controllers;

use App\Exceptions\InsufficientStockException;
use App\Exceptions\PaystackException;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Http\Requests\Order\UpdatePaymentStatusRequest;
use App\Models\Order;
use App\Services\FeatureService;
use App\Services\OrderService;
use App\Services\PaymentService;
use App\Services\WhatsAppMessageFormatter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly FeatureService $features,
        private readonly PaymentService $payments,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Order::class);

        $orders = Order::with('customer')
            ->when($request->filled('order_status'), fn ($q) => $q->where('order_status', $request->string('order_status')))
            ->when($request->filled('payment_status'), fn ($q) => $q->where('payment_status', $request->string('payment_status')))
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->string('search')->toString();
                $q->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($q) => $q->where('name', 'like', "%{$search}%")->orWhere('phone', 'like', "%{$search}%"));
                });
            })
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order, WhatsAppMessageFormatter $formatter): View
    {
        $this->authorize('view', $order);

        $order->load(['items', 'customer', 'business']);
        $canUseInvoices = $this->features->enabled($order->business, 'invoices');

        $pendingPayment = $order->payments()
            ->where('status', 'pending')
            ->whereNotNull('authorization_url')
            ->latest('id')
            ->first();

        $paymentLinkWhatsAppUrl = $pendingPayment
            ? $formatter->customerChatUrl($order, $formatter->paymentRequestMessage($order, $pendingPayment->authorization_url))
            : null;

        return view('orders.show', compact('order', 'canUseInvoices', 'pendingPayment', 'paymentLinkWhatsAppUrl'));
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        try {
            $this->orders->updateStatus($order, $request->validated('order_status'));
        } catch (InsufficientStockException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Order status updated.');
    }

    public function updatePaymentStatus(UpdatePaymentStatusRequest $request, Order $order): RedirectResponse
    {
        $this->orders->updatePaymentStatus($order, $request->validated('payment_status'));

        return back()->with('status', 'Payment status updated.');
    }

    /**
     * "Confirm order & request payment" — the seller-initiated half of the
     * WhatsApp order flow. Generates a real Paystack payment link tied to
     * this exact order (reusing PaymentService/CommissionService exactly
     * as the direct storefront checkout does — see section 7 of the
     * product spec), then moves the order to awaiting_payment so
     * inventory stays untouched until the customer actually pays.
     *
     * Deliberately does not fall back to platform-held funds the way
     * direct checkout does when no subaccount is connected — this
     * specific action requires one, per product requirement, rather than
     * silently generating a payment link the platform can't split
     * commission on.
     */
    public function requestPayment(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('update', $order);

        if ($order->payment_status === 'paid') {
            return back()->with('error', 'This order is already paid.');
        }

        if ($order->order_status === 'cancelled') {
            return back()->with('error', 'A cancelled order cannot be sent a payment request.');
        }

        if (! $order->business->hasPaystackSubaccount()) {
            return back()->with('error', 'Connect Paystack to accept secure online payments and automatically process your commission.');
        }

        $email = $order->customer->email ?: $this->placeholderEmail($order);

        try {
            $this->payments->initializeOrReuseForOrder($order, $email);
        } catch (PaystackException) {
            return back()->with('error', 'Unable to generate a payment link right now. Please try again shortly.');
        }

        if ($order->order_status === 'pending') {
            $this->orders->updateStatus($order, 'awaiting_payment');
        }

        return back()->with('status', 'Payment link generated.');
    }

    /**
     * Paystack requires a syntactically valid email to initialize a
     * transaction, but WhatsApp orders never collect one (see
     * CheckoutRequest — email is only required for the direct-checkout
     * journey). Not deliverability-sensitive: Paystack only uses this for
     * its own receipt, never to contact the customer on our behalf.
     */
    private function placeholderEmail(Order $order): string
    {
        return 'order-'.strtolower($order->order_number).'@whatsapp-order.invalid';
    }
}
