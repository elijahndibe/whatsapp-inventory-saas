<?php

namespace App\Services;

use App\Models\PlatformSetting;
use App\Models\User;

/**
 * The single source of truth for platform-wide monetization configuration
 * (commission rate/type/enabled, subscription system on/off). Every value
 * is admin-editable via the Monetization panel and read here rather than
 * hardcoded anywhere in application code — see Admin\MonetizationController.
 *
 * Deliberately uncached: platform_settings is a handful of rows, read
 * frequency is nowhere near hot-path, and a stale cache here would mean
 * an admin's commission/subscription-toggle change not taking effect
 * immediately — not an acceptable tradeoff for a save-and-forget speedup.
 */
class PlatformSettingsService
{
    private const DEFAULTS = [
        'commission.enabled' => ['value' => true, 'type' => 'boolean'],
        'commission.type' => ['value' => 'percentage', 'type' => 'string'],
        'commission.rate' => ['value' => 1.5, 'type' => 'float'],
        'commission.min' => ['value' => null, 'type' => 'float'],
        'commission.max' => ['value' => null, 'type' => 'float'],
        'subscription.enabled' => ['value' => false, 'type' => 'boolean'],
    ];

    public function get(string $key, mixed $default = null): mixed
    {
        $all = $this->all();

        if (array_key_exists($key, $all)) {
            return $all[$key];
        }

        return $default ?? (self::DEFAULTS[$key]['value'] ?? null);
    }

    public function set(string $key, mixed $value, ?User $admin = null, ?AuditLogService $audit = null): void
    {
        $type = self::DEFAULTS[$key]['type'] ?? (is_bool($value) ? 'boolean' : (is_float($value) ? 'float' : 'string'));
        $old = $this->get($key);

        PlatformSetting::updateOrCreate(
            ['key' => $key],
            ['value' => $this->encode($value, $type), 'type' => $type],
        );

        if ($audit) {
            $audit->record($admin, "setting.{$key}.changed", $old, $value);
        }
    }

    public function commissionEnabled(): bool
    {
        return (bool) $this->get('commission.enabled', true);
    }

    public function commissionType(): string
    {
        return (string) $this->get('commission.type', 'percentage');
    }

    public function commissionRate(): float
    {
        return (float) $this->get('commission.rate', 1.5);
    }

    public function commissionMin(): ?float
    {
        $value = $this->get('commission.min');

        return $value === null ? null : (float) $value;
    }

    public function commissionMax(): ?float
    {
        $value = $this->get('commission.max');

        return $value === null ? null : (float) $value;
    }

    public function subscriptionSystemEnabled(): bool
    {
        return (bool) $this->get('subscription.enabled', false);
    }

    private function all(): array
    {
        return PlatformSetting::all()->mapWithKeys(
            fn (PlatformSetting $setting) => [$setting->key => $this->decode($setting->value, $setting->type)]
        )->all();
    }

    private function encode(mixed $value, string $type): ?string
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => $value ? '1' : '0',
            default => (string) $value,
        };
    }

    private function decode(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'boolean' => (bool) $value,
            'integer' => (int) $value,
            'float' => (float) $value,
            default => $value,
        };
    }
}
