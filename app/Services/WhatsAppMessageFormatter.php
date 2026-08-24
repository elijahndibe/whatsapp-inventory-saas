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
}
