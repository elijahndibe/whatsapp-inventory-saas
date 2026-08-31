<?php

namespace App\Services;

use App\Exceptions\PaystackException;
use Illuminate\Support\Facades\Cache;
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

    /**
     * Creates a Paystack subaccount for a seller so their share of each
     * transaction can be split automatically at settlement — see
     * PaymentService::initializeForOrder() for how the resulting
     * subaccount_code is then used per-transaction.
     *
     * @param  array{business_name: string, settlement_bank: string, account_number: string}  $data
     * @return array{subaccount_code: string, account_name: string}
     */
    public function createSubaccount(array $data): array
    {
        $response = $this->client()->post('/subaccount', [
            'business_name' => $data['business_name'],
            'settlement_bank' => $data['settlement_bank'],
            'account_number' => $data['account_number'],
            // We control the split per-transaction via transaction_charge on
            // initializeTransaction() instead of a fixed percentage here, so
            // an admin-adjusted commission rate never requires touching the
            // seller's subaccount.
            'percentage_charge' => 0,
        ]);

        if (! $response->successful() || ! ($response->json('status') === true)) {
            throw new PaystackException('Paystack failed to create the subaccount: '.$response->json('message', 'unknown error'));
        }

        return $response->json('data');
    }

    /**
     * The bank list barely ever changes, and this is called on every
     * Settings page load for a business that hasn't connected a payout
     * account yet — cached so that's a once-a-day API call, not one per
     * page view.
     *
     * @return array<int, array{name: string, code: string}>
     */
    public function listBanks(): array
    {
        return Cache::remember('payout_bank_list', now()->addDay(), function () {
            $response = $this->client()->get('/bank', ['country' => 'nigeria', 'currency' => 'NGN']);

            if (! $response->successful() || ! ($response->json('status') === true)) {
                throw new PaystackException('Paystack failed to list banks: '.$response->json('message', 'unknown error'));
            }

            return $response->json('data');
        });
    }

    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withToken($this->secretKey)
            ->acceptJson()
            ->timeout(15);
    }
}
