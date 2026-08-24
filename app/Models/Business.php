<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'whatsapp_number',
        'logo',
        'description',
        'address',
        'city',
        'state',
        'country',
        'currency',
        'timezone',
        'status',
        'whatsapp_phone_number_id',
        'whatsapp_business_account_id',
        'whatsapp_access_token',
        'allow_overselling',
    ];

    protected function casts(): array
    {
        return [
            'allow_overselling' => 'boolean',
            // Encrypted at rest: never store WhatsApp Cloud API secrets in plain text.
            'whatsapp_phone_number_id' => 'encrypted',
            'whatsapp_business_account_id' => 'encrypted',
            'whatsapp_access_token' => 'encrypted',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Business $business) {
            if (empty($business->slug)) {
                $business->slug = static::generateUniqueSlug($business->name);
            }
        });
    }

    public static function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$original}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function currencySymbol(): string
    {
        return static::currencySymbolFor($this->currency);
    }

    public static function currencySymbolFor(string $currency): string
    {
        return match (strtoupper($currency)) {
            'NGN' => '₦',
            'USD' => '$',
            'GBP' => '£',
            'EUR' => '€',
            'GHS' => 'GH₵',
            'KES' => 'KSh',
            'ZAR' => 'R',
            default => strtoupper($currency).' ',
        };
    }

    /**
     * WhatsApp's wa.me click-to-chat links require digits only (country
     * code + number, no '+', spaces, or leading zeros after the code).
     */
    public function whatsappChatNumber(): ?string
    {
        $raw = $this->whatsapp_number ?: $this->phone;

        if (! $raw) {
            return null;
        }

        return preg_replace('/\D+/', '', $raw);
    }
}
