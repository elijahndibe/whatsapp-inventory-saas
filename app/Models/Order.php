<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'business_id',
        'customer_id',
        'coupon_id',
        'coupon_code',
        'order_number',
        'public_token',
        'subtotal',
        'discount',
        'delivery_fee',
        'tax',
        'total',
        'currency',
        'payment_status',
        'payment_method',
        'order_status',
        'payment_reference',
        'notes',
        'customer_notes',
        'shipping_address',
        'inventory_deducted_at',
        'source',
    ];

    /**
     * Migration-level column defaults aren't reliably honored by SQLite
     * (used in the test suite) — same reasoning as User::$attributes —
     * so default order_status explicitly here too, on top of the DB
     * column default.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'order_status' => 'pending',
    ];

    protected function casts(): array
    {
        return [
            'inventory_deducted_at' => 'datetime',
        ];
    }

    public const STATUSES = ['pending', 'awaiting_payment', 'confirmed', 'processing', 'ready', 'shipped', 'completed', 'cancelled', 'refunded'];

    public const PAYMENT_STATUSES = ['pending', 'paid', 'failed', 'refunded', 'partially_refunded'];

    public const SOURCES = ['storefront', 'whatsapp'];

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber($order->business_id);
            }
            if (empty($order->public_token)) {
                $order->public_token = static::generatePublicToken();
            }
        });
    }

    public static function generateOrderNumber(int $businessId): string
    {
        do {
            $number = 'ORD-'.strtoupper(Str::random(8));
        } while (static::withoutGlobalScopes()->where('business_id', $businessId)->where('order_number', $number)->exists());

        return $number;
    }

    public static function generatePublicToken(): string
    {
        do {
            $token = Str::random(40);
        } while (static::withoutGlobalScopes()->where('public_token', $token)->exists());

        return $token;
    }

    /**
     * Money is stored as integer minor currency units; the model API works
     * in major units transparently, same convention as Product.
     */
    protected function subtotal(): Attribute
    {
        return $this->moneyAttribute();
    }

    protected function discount(): Attribute
    {
        return $this->moneyAttribute();
    }

    protected function deliveryFee(): Attribute
    {
        return $this->moneyAttribute();
    }

    protected function tax(): Attribute
    {
        return $this->moneyAttribute();
    }

    protected function total(): Attribute
    {
        return $this->moneyAttribute();
    }

    private function moneyAttribute(): Attribute
    {
        return Attribute::make(
            get: fn (?int $value) => $value === null ? null : $value / 100,
            set: fn (?float $value) => $value === null ? null : (int) round($value * 100),
        );
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function currencySymbol(): string
    {
        return Business::currencySymbolFor($this->currency);
    }

    public function isFromWhatsApp(): bool
    {
        return $this->source === 'whatsapp';
    }

    /**
     * Raw integer minor-unit total, e.g. for Paystack's `amount` param
     * which expects kobo, not the major-unit float the `total` accessor
     * returns.
     */
    public function totalInMinorUnits(): int
    {
        return (int) $this->getRawOriginal('total');
    }
}
