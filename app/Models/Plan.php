<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'is_default',
        'price',
        'currency',
        'duration_days',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
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

    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
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
