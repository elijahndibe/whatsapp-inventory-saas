<?php

namespace App\Rules;

use App\Services\PhoneVerificationService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Passes when the submitted phone number has a confirmed WhatsApp code
 * verification within the trust window (PhoneVerificationService), OR
 * when phone verification isn't configured on this platform at all —
 * an unconfigured feature must never block every registration/settings
 * save, only enforce once someone has actually turned it on (see
 * WHATSAPP_SETUP.md for the platform WhatsApp number this needs).
 */
class PhoneIsVerified implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (blank($value)) {
            return;
        }

        $service = app(PhoneVerificationService::class);

        if (! $service->isConfigured()) {
            return;
        }

        if (! $service->isVerified($value)) {
            $fail(__('Please verify this phone number before continuing.'));
        }
    }
}
