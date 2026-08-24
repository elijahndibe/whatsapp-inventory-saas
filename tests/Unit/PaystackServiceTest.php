<?php

namespace Tests\Unit;

use App\Exceptions\PaystackException;
use App\Services\PaystackService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaystackServiceTest extends TestCase
{
    private function service(): PaystackService
    {
        return new PaystackService('sk_test_fake_secret', 'https://api.paystack.co');
    }

    public function test_initialize_transaction_returns_the_authorization_data(): void
    {
        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'message' => 'Authorization URL created',
                'data' => [
                    'authorization_url' => 'https://checkout.paystack.com/abc123',
                    'access_code' => 'abc123',
                    'reference' => 'PAY-XYZ',
                ],
            ]),
        ]);

        $result = $this->service()->initializeTransaction([
            'email' => 'buyer@example.com',
            'amount' => 500000,
            'currency' => 'NGN',
            'reference' => 'PAY-XYZ',
            'callback_url' => 'https://example.test/callback',
        ]);

        $this->assertSame('https://checkout.paystack.com/abc123', $result['authorization_url']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.paystack.co/transaction/initialize'
                && $request->hasHeader('Authorization', 'Bearer sk_test_fake_secret')
                && $request['amount'] === 500000;
        });
    }

    public function test_initialize_transaction_throws_on_failure_response(): void
    {
        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response(['status' => false, 'message' => 'Invalid key'], 401),
        ]);

        $this->expectException(PaystackException::class);

        $this->service()->initializeTransaction([
            'email' => 'buyer@example.com',
            'amount' => 500000,
            'currency' => 'NGN',
            'reference' => 'PAY-XYZ',
            'callback_url' => 'https://example.test/callback',
        ]);
    }

    public function test_verify_transaction_returns_the_transaction_data(): void
    {
        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'message' => 'Verification successful',
                'data' => [
                    'status' => 'success',
                    'reference' => 'PAY-XYZ',
                    'amount' => 500000,
                    'currency' => 'NGN',
                ],
            ]),
        ]);

        $result = $this->service()->verifyTransaction('PAY-XYZ');

        $this->assertSame('success', $result['status']);
        $this->assertSame(500000, $result['amount']);

        Http::assertSent(fn ($request) => $request->url() === 'https://api.paystack.co/transaction/verify/PAY-XYZ');
    }

    public function test_verify_transaction_throws_on_failure_response(): void
    {
        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response(['status' => false, 'message' => 'Transaction not found'], 404),
        ]);

        $this->expectException(PaystackException::class);

        $this->service()->verifyTransaction('does-not-exist');
    }
}
