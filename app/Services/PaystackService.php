<?php

namespace App\Services;

use App\Exceptions\PaystackException;
use Illuminate\Support\Facades\Http;

/**
 * Thin wrapper around the Paystack REST API. No SDK package is used —
 * Paystack's API is plain JSON over HTTP and Laravel's HTTP client covers
 * the two calls this app needs (initialize, verify), so a dependency
 * would only add an abstraction layer without saving real work.
 *
 * This class only talks to Paystack; it never touches our own models —
 * see PaymentService for the orchestration (what to do with the result).
 */
class PaystackService
{
    public function __construct(
        private readonly string $secretKey,
        private readonly string $baseUrl,
    ) {}

    /**
     * @param  array{email: string, amount: int, currency: string, reference: string, callback_url: string, metadata?: array}  $data
     * @return array{authorization_url: string, access_code: string, reference: string}
     */
    public function initializeTransaction(array $data): array
    {
        $response = $this->client()->post('/transaction/initialize', $data);

        if (! $response->successful() || ! ($response->json('status') === true)) {
            throw new PaystackException('Paystack failed to initialize the transaction: '.$response->json('message', 'unknown error'));
        }

        return $response->json('data');
    }

    /**
     * Server-to-server verification — the only source of truth for whether
     * a transaction actually succeeded. Never trust a client-supplied
     * status (query string, webhook payload) without calling this.
     *
     * @return array{status: string, reference: string, amount: int, currency: string, ...}
     */
    public function verifyTransaction(string $reference): array
    {
        $response = $this->client()->get("/transaction/verify/{$reference}");

        if (! $response->successful() || ! ($response->json('status') === true)) {
            throw new PaystackException("Paystack failed to verify transaction {$reference}: ".$response->json('message', 'unknown error'));
        }

        return $response->json('data');
    }

    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withToken($this->secretKey)
            ->acceptJson()
            ->timeout(15);
    }
}
