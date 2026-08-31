<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * A guest cart lives entirely in the session, keyed per business so a
 * customer can browse multiple storefronts in one session without carts
 * bleeding into each other. Only product_id => quantity is stored —
 * prices and stock are always re-read from the database at render/checkout
 * time, so a price change or stock change between "add to cart" and
 * "checkout" is never silently ignored.
 */
class CartService
{
    private function key(int $businessId): string
    {
        return "cart.{$businessId}";
    }

    /**
     * Deliberately a different top-level branch from key() ("cart_coupon",
     * not "cart") — the session helper resolves a dotted string like
     * "cart.{$businessId}" via Arr::get/Arr::set, i.e. as a nested path
     * under a shared "cart" array. A coupon key nested under that same
     * "cart" branch would collide with — and corrupt — the product_id
     * => quantity items array key() already stores there.
     */
    private function couponKey(int $businessId): string
    {
        return "cart_coupon.{$businessId}";
    }

    /**
     * Only the typed code is remembered here — never a cached discount
     * amount. Validity (expiry, usage limits, minimum order) is always
     * re-checked fresh against live data wherever this is read, exactly
     * like cart prices/stock already are.
     */
    public function appliedCouponCode(int $businessId): ?string
    {
        return session($this->couponKey($businessId));
    }

    public function applyCoupon(int $businessId, string $code): void
    {
        session([$this->couponKey($businessId) => strtoupper(trim($code))]);
    }

    public function removeCoupon(int $businessId): void
    {
        session()->forget($this->couponKey($businessId));
    }

    public function items(int $businessId): array
    {
        return session($this->key($businessId), []);
    }

    public function add(int $businessId, int $productId, int $quantity): void
    {
        $items = $this->items($businessId);
        $items[$productId] = max(1, ($items[$productId] ?? 0) + $quantity);
        session([$this->key($businessId) => $items]);
    }

    public function update(int $businessId, int $productId, int $quantity): void
    {
        $items = $this->items($businessId);

        if ($quantity <= 0) {
            unset($items[$productId]);
        } else {
            $items[$productId] = $quantity;
        }

        session([$this->key($businessId) => $items]);
    }

    public function remove(int $businessId, int $productId): void
    {
        $this->update($businessId, $productId, 0);
    }

    public function clear(int $businessId): void
    {
        session()->forget($this->key($businessId));
        $this->removeCoupon($businessId);
    }

    public function count(int $businessId): int
    {
        return array_sum($this->items($businessId));
    }

    /**
     * Resolves cart entries against live product data. Entries for
     * products that no longer exist, or no longer belong to this
     * business, or are inactive, are silently dropped.
     *
     * @return Collection<int, object{product: Product, quantity: int, subtotal: float}>
     */
    public function detailed(Business $business): Collection
    {
        $items = $this->items($business->id);

        if (empty($items)) {
            return collect();
        }

        $products = Product::forBusiness($business->id)
            ->active()
            ->whereIn('id', array_keys($items))
            ->with('images')
            ->get()
            ->keyBy('id');

        return collect($items)
            ->map(function (int $quantity, int $productId) use ($products) {
                $product = $products->get($productId);

                if (! $product) {
                    return null;
                }

                return (object) [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => round($product->price * $quantity, 2),
                ];
            })
            ->filter()
            ->values();
    }

    public function subtotal(Business $business): float
    {
        return (float) $this->detailed($business)->sum('subtotal');
    }
}
