<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessPaystackWebhook;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaystackWebhookController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $signature = $request->header('x-paystack-signature');
        $expected = hash_hmac('sha512', $request->getContent(), (string) config('services.paystack.secret_key'));

        if (! $signature || ! hash_equals($expected, $signature)) {
            Log::warning('Rejected Paystack webhook: invalid signature.');

            return response()->json(['message' => 'invalid signature'], 400);
        }

        $event = $request->input('event');
        $reference = $request->input('data.reference');

        if ($event === 'charge.success' && $reference) {
            ProcessPaystackWebhook::dispatch($reference);
        }

        // Always 200 quickly once the signature checks out — actual
        // verification happens in the queued job, never inline here.
        return response()->json(['message' => 'ok']);
    }
}
