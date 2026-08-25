<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use BelongsToTenant;

    public const STATUSES = ['pending', 'success', 'failed', 'abandoned'];

    public const SETTLEMENT_STATUSES = ['pending', 'settled', 'platform_held', 'failed'];

    protected $fillable = [
        'business_id',
        'order_id',
        'reference',
        'gateway',
        'amount',
        'currency',
        'status',
        'gateway_response',
        'paid_at',
        'commission_rate',
        'commission_amount',
        'seller_amount',
        'payment_fee',
        'settlement_status',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'commission_rate' => 'float',
        ];
    }

    /**
     * Same integer-minor-units convention as Product/Order, but note:
     * amount-integrity checks against Paystack's verified response MUST
     * compare against getRawOriginal('amount'), never this accessor —
     * see PaymentService::handleVerifiedTransaction().
     */
    protected function amount(): Attribute
    {
        return Attribute::make(
            get: fn (?int $value) => $value === null ? null : $value / 100,
            set: fn (?float $value) => $value === null ? null : (int) round($value * 100),
        );
    }

    private function minorUnitAttribute(): Attribute
    {
        return Attribute::make(
            get: fn (?int $value) => $value === null ? null : $value / 100,
            set: fn (?float $value) => $value === null ? null : (int) round($value * 100),
        );
    }

    protected function commissionAmount(): Attribute
    {
        return $this->minorUnitAttribute();
    }

    protected function sellerAmount(): Attribute
    {
        return $this->minorUnitAttribute();
    }

    protected function paymentFee(): Attribute
    {
        return $this->minorUnitAttribute();
    }

    public function commissionAmountInMinorUnits(): int
    {
        return (int) $this->getRawOriginal('commission_amount');
    }

    public function sellerAmountInMinorUnits(): int
    {
        return (int) $this->getRawOriginal('seller_amount');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
