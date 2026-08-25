<?php

namespace App\Services;

use App\Exceptions\WhatsAppEmbeddedSignupException;
use App\Models\Business;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around the WhatsApp Cloud API (Meta Graph API). Message
 * sending uses per-business identifiers with a resolved access token (see
 * Business::whatsappAccessToken()) — mirrors PaystackService's shape: no
 * SDK package, just the REST calls this app actually needs.
 *
 * Also owns the server-side half of the WhatsApp Embedded Signup flow
 * (code exchange, webhook subscription, phone number registration/
 * lookup) — kept in this same class rather than a separate service
 * because it's the same Graph API abstraction, just different endpoints.
 * See WhatsAppConnectController for how these are orchestrated, and
 * WHATSAPP_SETUP.md for the Meta Developer Dashboard configuration this
 * depends on.
 *
 * Sending is always best-effort from the caller's perspective — a business
 * that hasn't configured Cloud API credentials, or a transient Meta API
 * failure, must never block an order/payment flow. Callers should go
 * through the queued SendWhatsAppOrderMessage job rather than calling
 * this directly from a request cycle.
 */
class WhatsAppCloudApiService
{
    public function __construct(private readonly string $apiVersion) {}

    /**
     * Sends a plain text message. Returns false (and logs) instead of
     * throwing when the business has no usable token/number configured,
     * since that is an expected, common state, not an error condition.
     */
    public function sendTextMessage(Business $business, string $toPhone, string $message): bool
    {
        $token = $business->whatsappAccessToken();

        if (! $business->hasWhatsAppCloudApi() || ! $token) {
            return false;
        }

        $to = preg_replace('/\D+/', '', $toPhone);

        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(15)
            ->post("https://graph.facebook.com/{$this->apiVersion}/{$business->whatsapp_phone_number_id}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'text',
                'text' => ['body' => $message],
            ]);

        if (! $response->successful()) {
            Log::warning('WhatsApp Cloud API send failed', [
                'business_id' => $business->id,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
        }

        return $response->successful();
    }

    /**
     * Step 1 of Embedded Signup: exchange the short-lived authorization
     * code the frontend got from FB.login() for an access token scoped to
     * the WABA the user just shared with our Meta app. We only need this
     * token transiently, to complete steps 2 (subscribe) and 3 (register)
     * below — ongoing sends use the platform System User token instead
     * (see Business::whatsappAccessToken()), so this exchanged token is
     * never persisted.
     */
    public function exchangeCodeForToken(string $code, string $appId, string $appSecret): string
    {
        $response = $this->client()->get('https://graph.facebook.com/oauth/access_token', [
            'client_id' => $appId,
            'client_secret' => $appSecret,
            'code' => $code,
        ]);

        if (! $response->successful() || ! $response->json('access_token')) {
            $this->throwFor($response, 'We couldn\'t complete the connection with Meta. Please try again.');
        }

        return $response->json('access_token');
    }

    /**
     * Step 2: without this, Meta will never deliver webhook events
     * (incoming messages, delivery/status updates) for this WABA to our
     * app's webhook URL — the app has to explicitly subscribe per WABA,
     * even though the webhook URL itself is configured once, platform-wide,
     * in the Meta App Dashboard.
     */
    public function subscribeAppToWaba(string $wabaId, string $token): void
    {
        $response = $this->client()->withToken($token)
            ->post("https://graph.facebook.com/{$this->apiVersion}/{$wabaId}/subscribed_apps");

        if (! $response->successful()) {
            $this->throwFor($response, 'WhatsApp connected, but we could not enable message notifications for it. Please try disconnecting and reconnecting.');
        }
    }

    public function unsubscribeAppFromWaba(string $wabaId, string $token): void
    {
        $response = $this->client()->withToken($token)
            ->delete("https://graph.facebook.com/{$this->apiVersion}/{$wabaId}/subscribed_apps");

        if (! $response->successful()) {
            Log::warning('WhatsApp: failed to unsubscribe app from WABA on disconnect', [
                'waba_id' => $wabaId,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
        }
    }

    /**
     * Step 3: required once per phone number before the Cloud API will
     * send/receive on it. Numbers added through Embedded Signup are
     * usually pre-registered by Meta's own flow already, so a failure
     * here is treated as non-fatal (logged, not thrown) — re-registering
     * an already-registered number is a harmless no-op on Meta's side,
     * but we don't want a transient failure on this specific call to
     * block an otherwise-successful connection.
     */
    public function registerPhoneNumber(string $phoneNumberId, string $token): void
    {
        $response = $this->client()->withToken($token)
            ->post("https://graph.facebook.com/{$this->apiVersion}/{$phoneNumberId}/register", [
                'messaging_product' => 'whatsapp',
                'pin' => str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            ]);

        if (! $response->successful()) {
            Log::info('WhatsApp: phone number register call did not succeed (often already registered)', [
                'phone_number_id' => $phoneNumberId,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
        }
    }

    /**
     * @return array{display_phone_number: ?string, verified_name: ?string}
     */
    public function fetchPhoneNumberDetails(string $phoneNumberId, string $token): array
    {
        $response = $this->client()->withToken($token)
            ->get("https://graph.facebook.com/{$this->apiVersion}/{$phoneNumberId}", [
                'fields' => 'display_phone_number,verified_name',
            ]);

        if (! $response->successful()) {
            $this->throwFor($response, 'WhatsApp connected, but we could not retrieve your phone number details.');
        }

        return [
            'display_phone_number' => $response->json('display_phone_number'),
            'verified_name' => $response->json('verified_name'),
        ];
    }

    private function throwFor($response, string $friendlyMessage): never
    {
        Log::warning('WhatsApp Embedded Signup Graph API call failed', [
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        throw new WhatsAppEmbeddedSignupException($friendlyMessage);
    }

    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::acceptJson()->timeout(15);
    }
}
