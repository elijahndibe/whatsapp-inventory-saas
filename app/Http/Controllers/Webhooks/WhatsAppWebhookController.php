<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Meta sends webhooks to one URL for the whole app (like Paystack), not
 * per business, so incoming events are routed to a business by matching
 * `phone_number_id` in the payload against Business::whatsapp_phone_number_id.
 *
 * V1 scope: verify and log incoming messages/status updates against the
 * right business. A full two-way chat inbox is a separate, much larger
 * feature and isn't built here — this is the foundation it would sit on.
 */
class WhatsAppWebhookController extends Controller
{
    /**
     * Meta's one-time verification handshake when registering the webhook
     * URL in the developer console.
     */
    public function verify(Request $request): Response
    {
        $mode = $request->query('hub_mode', $request->query('hub.mode'));
        $token = $request->query('hub_verify_token', $request->query('hub.verify_token'));
        $challenge = $request->query('hub_challenge', $request->query('hub.challenge'));

        $expected = config('services.whatsapp.webhook_verify_token');

        if ($mode === 'subscribe' && $expected && hash_equals($expected, (string) $token)) {
            return response($challenge, 200);
        }

        return response('Forbidden', 403);
    }

    public function handle(Request $request): Response
    {
        $signature = $request->header('x-hub-signature-256');
        $appSecret = config('services.whatsapp.app_secret');

        if ($appSecret) {
            $expected = 'sha256='.hash_hmac('sha256', $request->getContent(), $appSecret);

            if (! $signature || ! hash_equals($expected, $signature)) {
                Log::warning('Rejected WhatsApp webhook: invalid signature.');

                return response()->json(['message' => 'invalid signature'], 400);
            }
        }

        foreach ($request->input('entry', []) as $entry) {
            foreach ($entry['changes'] ?? [] as $change) {
                $this->processChange($change['value'] ?? []);
            }
        }

        return response()->json(['message' => 'ok']);
    }

    private function processChange(array $value): void
    {
        $phoneNumberId = $value['metadata']['phone_number_id'] ?? null;

        if (! $phoneNumberId) {
            return;
        }

        $business = Business::where('whatsapp_phone_number_id', $phoneNumberId)->first();

        if (! $business) {
            Log::info('WhatsApp webhook: no business matches phone_number_id.', ['phone_number_id' => $phoneNumberId]);

            return;
        }

        foreach ($value['messages'] ?? [] as $message) {
            Log::info('WhatsApp incoming message', [
                'business_id' => $business->id,
                'from' => $message['from'] ?? null,
                'type' => $message['type'] ?? null,
                'text' => $message['text']['body'] ?? null,
            ]);
        }

        foreach ($value['statuses'] ?? [] as $status) {
            Log::info('WhatsApp message status', [
                'business_id' => $business->id,
                'message_id' => $status['id'] ?? null,
                'status' => $status['status'] ?? null,
            ]);
        }
    }
}
