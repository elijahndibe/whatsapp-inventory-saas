<?php

namespace App\Services;

use App\Models\Order;

/**
 * Builds the plain-text order message shared by the wa.me click-to-chat
 * link (used now) and the WhatsApp Cloud API business-initiated messages
 * (Phase 6) — one place defines what an order looks like as a message.
 */
class WhatsAppMessageFormatter
{
    public function forOrder(Order $order): string
    {
        $order->loadMissing('items', 'customer', 'business');
        $symbol = $order->currencySymbol();

        $lines = [];
        $lines[] = "Hello {$order->business->name},";
        $lines[] = '';
        $lines[] = "I'd like to place an order.";
        $lines[] = '';
        $lines[] = "Order: #{$order->order_number}";
        $lines[] = '';

        foreach ($order->items as $index => $item) {
            $lineTotal = number_format($item->subtotal, 2);
            $lines[] = ($index + 1).". {$item->product_name} x{$item->quantity} - {$symbol}{$lineTotal}";
        }

        $lines[] = '';
        $lines[] = "Subtotal: {$symbol}".number_format($order->subtotal, 2);

        if ($order->delivery_fee > 0) {
            $lines[] = "Delivery: {$symbol}".number_format($order->delivery_fee, 2);
        }
        if ($order->discount > 0) {
            $lines[] = "Discount: -{$symbol}".number_format($order->discount, 2);
        }

        $lines[] = "Total: {$symbol}".number_format($order->total, 2);
        $lines[] = '';
        $lines[] = "Name: {$order->customer->name}";
        $lines[] = "Phone: {$order->customer->phone}";

        if ($order->shipping_address) {
            $lines[] = "Address: {$order->shipping_address}";
        }
        if ($order->customer_notes) {
            $lines[] = '';
            $lines[] = "Notes: {$order->customer_notes}";
        }

        $lines[] = '';
        $lines[] = 'Thank you.';

        return implode("\n", $lines);
    }

    public function chatUrl(Order $order): ?string
    {
        $number = $order->business->whatsappChatNumber();

        if (! $number) {
            return null;
        }

        return 'https://wa.me/'.$number.'?text='.rawurlencode($this->forOrder($order));
    }

    /**
     * The reverse direction of chatUrl(): opened from the seller's own
     * dashboard to message the customer (not the other way around) — used
     * for "Send payment link on WhatsApp" (see OrderController and
     * orders/show.blade.php). Targets the customer's own phone number,
     * never the business's.
     */
    public function customerChatUrl(Order $order, string $message): ?string
    {
        $order->loadMissing('customer');
        $raw = $order->customer?->phone;

        if (! $raw) {
            return null;
        }

        $number = preg_replace('/\D+/', '', $raw);

        return 'https://wa.me/'.$number.'?text='.rawurlencode($message);
    }

    /**
     * The message a seller sends the customer after generating a Paystack
     * payment link for a WhatsApp order — see section 8 of the product
     * spec this implements.
     */
    public function paymentRequestMessage(Order $order, string $paymentUrl): string
    {
        $order->loadMissing('business');
        $symbol = $order->currencySymbol();

        return "Your order #{$order->order_number} has been confirmed.\n\n"
            ."Total: {$symbol}".number_format($order->total, 2)."\n\n"
            ."Please complete your payment securely using the link below:\n\n"
            ."{$paymentUrl}\n\n"
            .'Thank you for shopping with '.$order->business->name.'.';
    }

    /**
     * Business-initiated status update sent to the customer via the
     * WhatsApp Cloud API (as opposed to forOrder(), which is the
     * customer-initiated click-to-chat message).
     */
    public function statusUpdateMessage(Order $order, string $event): string
    {
        $order->loadMissing('business', 'customer');
        $business = $order->business->name;
        $symbol = $order->currencySymbol();
        $total = number_format($order->total, 2);

        $body = match ($event) {
            'payment_received' => "Payment received for order #{$order->order_number} ({$symbol}{$total}). Thank you!",
            'order_confirmed' => "Your order #{$order->order_number} has been confirmed and is being prepared.",
            'processing' => "Your order #{$order->order_number} is now being processed.",
            'ready' => "Your order #{$order->order_number} is ready.",
            'shipped' => "Your order #{$order->order_number} is on its way!",
            'completed' => "Your order #{$order->order_number} has been completed. Thanks for shopping with us!",
            'cancelled' => "Your order #{$order->order_number} has been cancelled. Contact us if you have questions.",
            default => "Update on your order #{$order->order_number}.",
        };

        return "Hello {$order->customer->name},\n\n{$body}\n\n— {$business}";
    }
}
