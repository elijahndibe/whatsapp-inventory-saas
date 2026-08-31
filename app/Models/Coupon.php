<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use BelongsToTenant, HasFactory;

    public const TYPE_PERCENTAGE = 'percentage';

    public const TYPE_FIXED = 'fixed';

    public const TYPES = [self::TYPE_PERCENTAGE, self::TYPE_FIXED];

    protected $fillable = [
        'business_id',
        'code',
        'type',
        'value',
        'max_discount_amount',
        'minimum_order_amount',
        'usage_limit',
        'usage_limit_per_customer',
        'times_used',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'float',
            'max_discount_amount' => 'float',
            'minimum_order_amount' => 'float',
            'starts_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Coupon $coupon) {
            $coupon->code = strtoupper(trim($coupon->code));
        });

        static::updating(function (Coupon $coupon) {
            if ($coupon->isDirty('code')) {
                $coupon->code = strtoupper(trim($coupon->code));
            }
        });
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function hasStarted(): bool
    {
        return $this->starts_at === null || $this->starts_at->isPast();
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function hasReachedUsageLimit(): bool
    {
        return $this->usage_limit !== null && $this->times_used >= $this->usage_limit;
    }

    /**
     * Whether this coupon can be redeemed at all right now, ignoring
     * order-specific checks (minimum order amount, per-customer limit) —
     * those are cart/checkout-specific and live in CouponService::validate().
     */
    public function isRedeemable(): bool
    {
        return $this->is_active && $this->hasStarted() && ! $this->isExpired() && ! $this->hasReachedUsageLimit();
    }

    /**
     * The discount amount (major currency units, same as Order::subtotal)
     * for a given cart subtotal. Never exceeds the subtotal itself — a
     * coupon can bring an order to free, never negative.
     */
    public function calculateDiscount(float $subtotal): float
    {
        if ($this->type === self::TYPE_FIXED) {
            return round(min($this->value, $subtotal), 2);
        }

        $discount = round($subtotal * ($this->value / 100), 2);

        if ($this->max_discount_amount !== null) {
            $discount = min($discount, $this->max_discount_amount);
        }

        return round(min($discount, $subtotal), 2);
    }

    /**
     * Human-readable amount for admin/seller screens, e.g. "15% off" or
     * "₦500 off" — currency symbol supplied by the caller since Coupon
     * itself doesn't know its business's currency without a query.
     */
    public function valueLabel(string $currencySymbol): string
    {
        return $this->type === self::TYPE_PERCENTAGE
            ? rtrim(rtrim(number_format($this->value, 2), '0'), '.').'% off'
            : $currencySymbol.number_format($this->value, 2).' off';
    }
}
