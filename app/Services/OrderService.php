<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Scopes\BusinessScope;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderService
{
    public function __construct(private readonly InventoryService $inventory) {}

    /**
     * @param  Collection<int, object{product: \App\Models\Product, quantity: int, subtotal: float}>  $cartItems
     */
    public function createFromCart(Business $business, Collection $cartItems, array $customerData): Order
    {
        if ($cartItems->isEmpty()) {
            throw new InvalidArgumentException('Cannot create an order from an empty cart.');
        }

        return DB::transaction(function () use ($business, $cartItems, $customerData) {
            $customer = Customer::updateOrCreate(
                ['business_id' => $business->id, 'phone' => $customerData['phone']],
                [
                    'name' => $customerData['name'],
                    'email' => $customerData['email'] ?? null,
                    'address' => $customerData['address'] ?? null,
                    'city' => $customerData['city'] ?? null,
                    'state' => $customerData['state'] ?? null,
                ]
            );

            $subtotal = (float) $cartItems->sum('subtotal');
            $deliveryFee = (float) ($customerData['delivery_fee'] ?? 0);

            $order = Order::create([
                'business_id' => $business->id,
                'customer_id' => $customer->id,
                'subtotal' => $subtotal,
                'delivery_fee' => $deliveryFee,
                'total' => $subtotal + $deliveryFee,
                'currency' => $business->currency,
                'payment_method' => $customerData['payment_method'] ?? 'whatsapp',
                'customer_notes' => $customerData['notes'] ?? null,
                'shipping_address' => $customerData['address'] ?? null,
            ]);

            foreach ($cartItems as $item) {
                $order->items()->create([
                    'product_id' => $item->product->id,
                    'product_name' => $item->product->name,
                    'quantity' => $item->quantity,
                    'price' => $item->product->price,
                    'subtotal' => $item->subtotal,
                ]);
            }

            return $order;
        });
    }

    /**
     * Moves an order to a new status, deducting or restocking inventory
     * exactly once as needed:
     *
     * - Leaving 'pending' for any non-cancelled status (business confirms
     *   the order) deducts stock, per spec: "For WhatsApp/manual orders:
     *   Business confirms order → inventory deducted."
     * - Moving to 'cancelled' after stock was deducted restocks it.
     *
     * inventory_deducted_at is the idempotency guard in both directions,
     * so re-saving the same status, or bouncing between statuses, can
     * never double-deduct or double-restock.
     */
    public function updateStatus(Order $order, string $status): Order
    {
        if (! in_array($status, Order::STATUSES, true)) {
            throw new InvalidArgumentException("Invalid order status: {$status}");
        }

        if ($status === $order->order_status) {
            return $order;
        }

        return DB::transaction(function () use ($order, $status) {
            if ($status === 'cancelled') {
                if ($order->inventory_deducted_at) {
                    $this->restockItems($order);
                    $order->inventory_deducted_at = null;
                }
            } elseif (! $order->inventory_deducted_at) {
                $this->deductItems($order);
                $order->inventory_deducted_at = now();
            }

            $order->order_status = $status;
            $order->save();

            return $order;
        });
    }

    public function updatePaymentStatus(Order $order, string $paymentStatus): Order
    {
        if (! in_array($paymentStatus, Order::PAYMENT_STATUSES, true)) {
            throw new InvalidArgumentException("Invalid payment status: {$paymentStatus}");
        }

        $order->update(['payment_status' => $paymentStatus]);

        return $order;
    }

    /**
     * Applies a confirmed online payment to an order: marks it paid,
     * auto-confirms it if it was still pending, and deducts inventory —
     * per spec: "For online payment: Payment confirmed → inventory
     * deducted." Locks the order row and re-checks payment_status under
     * the lock, so this is safe to call from both the payment callback
     * and the webhook without double-deducting even if they race.
     */
    public function markAsPaidViaGateway(Order $order, array $context = []): Order
    {
        return DB::transaction(function () use ($order, $context) {
            $locked = Order::withoutGlobalScope(BusinessScope::class)
                ->whereKey($order->getKey())
                ->lockForUpdate()
                ->first();

            if ($locked->payment_status === 'paid') {
                return $locked; // idempotent — already processed
            }

            $locked->payment_status = 'paid';
            $locked->payment_method = 'paystack';

            if (! empty($context['reference'])) {
                $locked->payment_reference = $context['reference'];
            }

            if ($locked->order_status === 'pending') {
                $locked->order_status = 'confirmed';
            }

            if (! $locked->inventory_deducted_at) {
                $this->deductItems($locked);
                $locked->inventory_deducted_at = now();
            }

            $locked->save();

            return $locked;
        });
    }

    private function deductItems(Order $order): void
    {
        foreach ($order->items as $item) {
            $product = $this->resolveProduct($item->product_id);

            if (! $product) {
                continue; // product was deleted since the order was placed
            }

            $this->inventory->decrease($product, $item->quantity, 'sale', [
                'reference_type' => Order::class,
                'reference_id' => $order->id,
                'notes' => "Order {$order->order_number} confirmed",
            ]);
        }
    }

    private function restockItems(Order $order): void
    {
        foreach ($order->items as $item) {
            $product = $this->resolveProduct($item->product_id);

            if (! $product) {
                continue;
            }

            $this->inventory->increase($product, $item->quantity, 'return', [
                'reference_type' => Order::class,
                'reference_id' => $order->id,
                'notes' => "Order {$order->order_number} cancelled",
            ]);
        }
    }

    private function resolveProduct(?int $productId): ?Product
    {
        if (! $productId) {
            return null;
        }

        return Product::withoutGlobalScope(BusinessScope::class)->find($productId);
    }
}
