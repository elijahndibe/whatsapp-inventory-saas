<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Plan extends Model
{
    public const FEATURES = [
        'whatsapp_ordering' => 'WhatsApp ordering (click-to-chat)',
        'basic_inventory' => 'Basic inventory management',
        'paystack' => 'Online payments (Paystack)',
        'invoices' => 'Invoices & receipts',
        'whatsapp_cloud_api' => 'WhatsApp Cloud API (automated messages)',
        'advanced_analytics' => 'Advanced analytics',
        'priority_support' => 'Priority support',
    ];

    protected $fillable = [
        'name',
        'slug',
        'price',
        'currency',
        'duration_days',
        'max_products',
        'max_orders_per_month',
        'max_staff',
        'max_locations',
        'features',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Plan $plan) {
            if (empty($plan->slug)) {
                $plan->slug = Str::slug($plan->name);
            }
        });
    }

    protected function price(): Attribute
    {
        return Attribute::make(
            get: fn (?int $value) => $value === null ? null : $value / 100,
            set: fn (?float $value) => $value === null ? null : (int) round($value * 100),
        );
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function hasFeature(string $feature): bool
    {
        return (bool) ($this->features[$feature] ?? false);
    }

    public function isFree(): bool
    {
        return $this->price == 0;
    }

    public function currencySymbol(): string
    {
        return Business::currencySymbolFor($this->currency);
    }
}
