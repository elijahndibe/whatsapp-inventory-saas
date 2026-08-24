<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderService
{
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
}
