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
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
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

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
