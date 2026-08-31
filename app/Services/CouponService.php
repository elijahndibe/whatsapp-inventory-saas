<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Scopes\BusinessScope;

/**
 * Coupon *creation/editing* is plain CRUD (see CouponController) — this
 * service is specifically the redemption path: deciding whether a typed
 * code is currently usable for a given cart, and recording that it was
 * used. Re-run at both "Apply" on the cart page and again at checkout
 * submission (never trusted from an earlier, possibly-stale check), same
 * principle as stock being re-verified at checkout despite already having
 * been checked when the item was added to the cart.
 */
class CouponService
{
    public function __construct(private readonly FeatureService $features) {}

    /**
     * @return array{coupon: ?Coupon, discount: float, error: ?string}
     */
    public function validate(Business $business, string $code, float $subtotal, ?string $customerPhone = null): array
    {
        $fail = fn (string $message) => ['coupon' => null, 'discount' => 0.0, 'error' => $message];

        if (! $this->features->enabled($business, 'coupons')) {
            return $fail('Coupon codes are not available for this store.');
        }

        $code = strtoupper(trim($code));

        if ($code === '') {
            return $fail('Enter a coupon code.');
        }

        $coupon = Coupon::where('business_id', $business->id)->where('code', $code)->first();

        if (! $coupon) {
            return $fail('This coupon code isn\'t valid.');
        }

        if (! $coupon->is_active) {
            return $fail('This coupon is no longer active.');
        }

        if (! $coupon->hasStarted()) {
            return $fail('This coupon isn\'t active yet.');
        }

        if ($coupon->isExpired()) {
            return $fail('This coupon has expired.');
        }

        if ($coupon->hasReachedUsageLimit()) {
            return $fail('This coupon has reached its usage limit.');
        }

        if ($coupon->minimum_order_amount !== null && $subtotal < $coupon->minimum_order_amount) {
            return $fail("This coupon requires a minimum order of {$business->currencySymbol()}".number_format($coupon->minimum_order_amount, 2).'.');
        }

        if ($customerPhone && $coupon->usage_limit_per_customer !== null) {
            $usedByCustomer = Order::withoutGlobalScope(BusinessScope::class)
                ->where('business_id', $business->id)
                ->where('coupon_id', $coupon->id)
                ->whereHas('customer', fn ($q) => $q->where('phone', $customerPhone))
                ->count();

            if ($usedByCustomer >= $coupon->usage_limit_per_customer) {
                return $fail('You\'ve already used this coupon.');
            }
        }

        return ['coupon' => $coupon, 'discount' => $coupon->calculateDiscount($subtotal), 'error' => null];
    }

    /**
     * Counted at order creation, not payment confirmation — a WhatsApp
     * order is never guaranteed to go through an online payment at all,
     * so gating redemption on "paid" would let usage_limit_per_customer
     * be trivially bypassed by ordering and never paying.
     */
    public function redeem(Coupon $coupon): void
    {
        $coupon->increment('times_used');
    }
}
