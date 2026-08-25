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
        'whatsapp_connected_via',
        'whatsapp_display_phone_number',
        'whatsapp_connected_at',
        'allow_overselling',
        'commission_rate',
        'paystack_subaccount_code',
        'paystack_bank_code',
        'paystack_account_number',
        'paystack_account_name',
    ];

    protected function casts(): array
    {
        return [
            'allow_overselling' => 'boolean',
            // whatsapp_phone_number_id and whatsapp_business_account_id are
            // plain, queryable identifiers (like a publishable key) — the
            // incoming-webhook handler and the Embedded Signup uniqueness
            // check both look businesses up by these, which an encrypted
            // column can't support. Only the actual credential is
            // encrypted at rest.
            'whatsapp_access_token' => 'encrypted',
            'whatsapp_connected_at' => 'datetime',
            'commission_rate' => 'float',
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

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(BusinessLocation::class);
    }

    /**
     * The most recent subscription row, regardless of status — the source
     * of truth SubscriptionService reads from (a business with none is
     * treated as unrestricted, not blocked; see SubscriptionService).
     */
    public function currentSubscription(): ?Subscription
    {
        return $this->subscriptions()->latest('id')->first();
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

    /**
     * True once a phone number is connected, regardless of whether it got
     * there via Embedded Signup (no per-business token stored — see
     * whatsappAccessToken()) or the legacy manual-entry fallback (token
     * stored on this record).
     */
    public function hasWhatsAppCloudApi(): bool
    {
        return filled($this->whatsapp_phone_number_id);
    }

    public function isWhatsAppConnectedViaEmbeddedSignup(): bool
    {
        return $this->whatsapp_connected_via === 'embedded_signup';
    }

    /**
     * The access token used to send/manage messages for this business's
     * WhatsApp number. Embedded Signup connections share the platform's
     * own System User token (config('services.whatsapp.system_user_token')
     * — see WHATSAPP_SETUP.md) rather than a per-business token, since the
     * platform's Meta Business has been granted access to the business's
     * WABA during the signup flow; the legacy manual-entry path still
     * stores and uses its own token per business.
     */
    public function whatsappAccessToken(): ?string
    {
        return $this->whatsapp_access_token ?: config('services.whatsapp.system_user_token');
    }

    public function hasPaystackSubaccount(): bool
    {
        return filled($this->paystack_subaccount_code);
    }

    /**
     * "Default platform commission" vs "Custom seller commission" — surfaced
     * in the admin UI so it's always clear which one currently applies.
     */
    public function hasCustomCommissionRate(): bool
    {
        return $this->commission_rate !== null;
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
