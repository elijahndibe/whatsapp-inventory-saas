<?php

namespace App\Services;

use App\Models\Business;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin wrapper around the WhatsApp Cloud API (Meta Graph API), using
 * per-business credentials stored on the Business model. Mirrors
 * PaystackService's shape: no SDK package, just the couple of REST calls
 * this app actually needs.
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
     * throwing when the business has no Cloud API credentials configured,
     * since that is an expected, common state, not an error condition.
     */
    public function sendTextMessage(Business $business, string $toPhone, string $message): bool
    {
        if (! $business->hasWhatsAppCloudApi()) {
            return false;
        }

        $to = preg_replace('/\D+/', '', $toPhone);

        $response = Http::withToken($business->whatsapp_access_token)
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
}
