<?php

namespace Database\Seeders;

use App\Services\PlatformSettingsService;
use Illuminate\Database\Seeder;

/**
 * Seeds the launch defaults for the commission-first model: commission
 * enabled at 1.5%, subscriptions OFF. Uses updateOrCreate semantics via
 * PlatformSettingsService::set() only when a key is missing, so re-running
 * this seeder never clobbers values an admin has since changed.
 */
class PlatformSettingsSeeder extends Seeder
{
    public function run(PlatformSettingsService $settings): void
    {
        $defaults = [
            'commission.enabled' => true,
            'commission.type' => 'percentage',
            'commission.rate' => 1.5,
            'commission.min' => null,
            'commission.max' => null,
            'subscription.enabled' => false,
        ];

        foreach ($defaults as $key => $value) {
            if (\App\Models\PlatformSetting::where('key', $key)->doesntExist()) {
                $settings->set($key, $value);
            }
        }
    }
}
