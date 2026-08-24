<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'quantity',
        'price',
        'subtotal',
    ];

    protected function price(): Attribute
    {
        return $this->moneyAttribute();
    }

    protected function subtotal(): Attribute
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

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
