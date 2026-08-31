<?php

namespace App\Services;

use App\Models\PhoneVerification;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Sends and checks WhatsApp one-time codes for phone verification — used
 * on Registration and on Settings when a business changes its phone
 * number. Deliberately not tied to a user/business (see the migration):
 * callers ask "is this exact phone string verified recently?" rather than
 * looking a record up by owner.
 */
class PhoneVerificationService
{
    private const CODE_TTL_MINUTES = 10;

    private const MAX_VERIFY_ATTEMPTS = 5;

    // How recently a phone must have been verified for callers (the
    // registration/settings save) to accept it without asking again —
    // long enough to cover filling out the rest of a form, short enough
    // that a stale verified-but-abandoned code can't be reused much later.
    private const TRUST_WINDOW_MINUTES = 30;

    public function __construct(private readonly WhatsAppCloudApiService $whatsapp) {}

    public function isConfigured(): bool
    {
        return filled(config('services.whatsapp.platform_phone_number_id'))
            && filled(config('services.whatsapp.system_user_token'));
    }

    /**
     * @return array{sent: bool, message: string}
     */
    public function sendCode(string $phone): array
    {
        if (! $this->isConfigured()) {
            return ['sent' => false, 'message' => __('Phone verification is not available right now. Please try again later.')];
        }

        // One send per phone per 60s — a resend button that fires on every
        // click would otherwise let someone hammer the WhatsApp API (and
        // Meta's per-template rate limits) for a single number.
        $throttleKey = 'phone-verification-send:'.$phone;
        if (RateLimiter::tooManyAttempts($throttleKey, 1)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            return ['sent' => false, 'message' => __('Please wait :seconds seconds before requesting another code.', ['seconds' => $seconds])];
        }
        RateLimiter::hit($throttleKey, 60);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        PhoneVerification::updateOrCreate(
            ['phone' => $phone, 'verified_at' => null],
            ['code' => $code, 'attempts' => 0, 'expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES)]
        );

        $delivered = $this->whatsapp->sendOtpTemplate($phone, $code);

        if (! $delivered) {
            return ['sent' => false, 'message' => __('We could not send a code to that number. Double-check it and try again.')];
        }

        return ['sent' => true, 'message' => __('A verification code was sent to your WhatsApp.')];
    }

    /**
     * @return array{verified: bool, message: string}
     */
    public function verifyCode(string $phone, string $code): array
    {
        $verification = PhoneVerification::where('phone', $phone)
            ->whereNull('verified_at')
            ->latest('id')
            ->first();

        if (! $verification) {
            return ['verified' => false, 'message' => __('Please request a new code.')];
        }

        if ($verification->expires_at->isPast()) {
            return ['verified' => false, 'message' => __('That code has expired. Please request a new one.')];
        }

        if ($verification->attempts >= self::MAX_VERIFY_ATTEMPTS) {
            return ['verified' => false, 'message' => __('Too many incorrect attempts. Please request a new code.')];
        }

        if (! hash_equals($verification->code, $code)) {
            $verification->increment('attempts');

            return ['verified' => false, 'message' => __('That code is incorrect.')];
        }

        $verification->update(['verified_at' => now()]);

        return ['verified' => true, 'message' => __('Phone number verified.')];
    }

    /**
     * Whether $phone has a verification confirmed within the trust window
     * — what RegisterBusinessRequest/UpdateBusinessSettingsRequest actually
     * check before allowing a (new) phone number to be saved.
     */
    public function isVerified(string $phone): bool
    {
        return PhoneVerification::where('phone', $phone)
            ->whereNotNull('verified_at')
            ->where('verified_at', '>=', now()->subMinutes(self::TRUST_WINDOW_MINUTES))
            ->exists();
    }
}
