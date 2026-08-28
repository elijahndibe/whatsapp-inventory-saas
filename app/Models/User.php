<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'business_id',
        'name',
        'email',
        'password',
        'status',
        'notification_preferences',
    ];

    /**
     * Keys this user can toggle off email delivery for. The in-app bell
     * (database channel) is never gated by these — only 'mail'.
     */
    public const EMAIL_NOTIFICATION_TYPES = ['new_order', 'payment_received', 'low_stock'];

    /**
     * Migration-level column defaults aren't reliably honored by SQLite
     * (used in the test suite), so default `status` explicitly here too.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'status' => 'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_super_admin' => 'boolean',
            'notification_preferences' => 'array',
        ];
    }

    /**
     * Whether this user should be emailed for a given notification type
     * (one of self::EMAIL_NOTIFICATION_TYPES). Missing key = enabled, so
     * existing users default to "on" without a data migration.
     */
    public function wantsEmailNotification(string $type): bool
    {
        return (bool) ($this->notification_preferences[$type] ?? true);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function isSuperAdmin(): bool
    {
        return (bool) $this->is_super_admin;
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(BusinessLocation::class, 'location_user', 'user_id', 'location_id');
    }
}
