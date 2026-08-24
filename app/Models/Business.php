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
            // whatsapp_phone_number_id is a plain, queryable identifier (like a
            // publishable key) — the incoming-webhook handler looks businesses
            // up by it, which an encrypted column can't support. Only the
            // actual credentials are encrypted at rest.
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

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
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

    public function hasWhatsAppCloudApi(): bool
    {
        return filled($this->whatsapp_phone_number_id) && filled($this->whatsapp_access_token);
    }

    /**
     * Staff notifications (new order, payment received, low stock) go to
     * whoever on this specific business holds the permission — roles and
     * permissions aren't business-scoped themselves, so this filters the
     * business's own users rather than querying the permission globally.
     */
    public function staffWithPermission(string $permission): \Illuminate\Support\Collection
    {
        return $this->users()->get()->filter(fn (User $user) => $user->can($permission))->values();
    }
}
