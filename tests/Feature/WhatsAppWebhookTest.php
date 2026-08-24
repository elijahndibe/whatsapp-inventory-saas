<?php

namespace Tests\Feature;

use App\Models\Business;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_verification_handshake_returns_the_challenge_for_a_correct_token(): void
    {
        $response = $this->get(route('webhooks.whatsapp.verify', [
            'hub_mode' => 'subscribe',
            'hub_verify_token' => 'fake_verify_token',
            'hub_challenge' => 'challenge-123',
        ]));

        $response->assertOk();
        $response->assertSee('challenge-123');
    }

    public function test_the_verification_handshake_rejects_a_wrong_token(): void
    {
        $response = $this->get(route('webhooks.whatsapp.verify', [
            'hub_mode' => 'subscribe',
            'hub_verify_token' => 'wrong-token',
            'hub_challenge' => 'challenge-123',
        ]));

        $response->assertStatus(403);
    }

    private function sign(string $body): string
    {
        return 'sha256='.hash_hmac('sha256', $body, config('services.whatsapp.app_secret'));
    }

    public function test_a_valid_signature_is_accepted_and_logged_against_the_matching_business(): void
    {
        $business = Business::factory()->create(['whatsapp_phone_number_id' => '1234567890']);

        $payload = [
            'entry' => [[
                'changes' => [[
                    'value' => [
                        'metadata' => ['phone_number_id' => '1234567890'],
                        'messages' => [['from' => '2348011112222', 'type' => 'text', 'text' => ['body' => 'Hi']]],
                    ],
                ]],
            ]],
        ];
        $body = json_encode($payload);

        $response = $this->call('POST', route('webhooks.whatsapp.handle'), [], [], [], [
            'HTTP_x-hub-signature-256' => $this->sign($body),
            'CONTENT_TYPE' => 'application/json',
        ], $body);

        $response->assertOk();
    }

    public function test_an_invalid_signature_is_rejected(): void
    {
        $payload = ['entry' => []];
        $body = json_encode($payload);

        $response = $this->call('POST', route('webhooks.whatsapp.handle'), [], [], [], [
            'HTTP_x-hub-signature-256' => 'sha256=not-correct',
            'CONTENT_TYPE' => 'application/json',
        ], $body);

        $response->assertStatus(400);
    }
}
