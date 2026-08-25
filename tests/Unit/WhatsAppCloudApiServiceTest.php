<?php

namespace Tests\Unit;

use App\Models\Business;
use App\Services\WhatsAppCloudApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppCloudApiServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_sending_without_configured_credentials_no_ops(): void
    {
        Http::fake();
        $business = Business::factory()->create(['whatsapp_phone_number_id' => null, 'whatsapp_access_token' => null]);

        $result = app(WhatsAppCloudApiService::class)->sendTextMessage($business, '2348012345678', 'Hello');

        $this->assertFalse($result);
        Http::assertNothingSent();
    }

    public function test_sending_with_configured_credentials_calls_the_graph_api(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.abc']]])]);

        $business = Business::factory()->create([
            'whatsapp_phone_number_id' => '1234567890',
            'whatsapp_access_token' => 'test-token',
        ]);

        $result = app(WhatsAppCloudApiService::class)->sendTextMessage($business, '+234 801 234 5678', 'Hello there');

        $this->assertTrue($result);

        Http::assertSent(function ($request) {
            return str_contains($request->url(), '1234567890/messages')
                && $request->hasHeader('Authorization', 'Bearer test-token')
                && $request['to'] === '2348012345678'
                && $request['text']['body'] === 'Hello there';
        });
    }

    public function test_a_failed_api_response_returns_false(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['error' => ['message' => 'Invalid token']], 401)]);

        $business = Business::factory()->create([
            'whatsapp_phone_number_id' => '1234567890',
            'whatsapp_access_token' => 'bad-token',
        ]);

        $result = app(WhatsAppCloudApiService::class)->sendTextMessage($business, '2348012345678', 'Hello');

        $this->assertFalse($result);
    }

    public function test_an_embedded_signup_connection_sends_using_the_platform_system_user_token(): void
    {
        config(['services.whatsapp.system_user_token' => 'platform-shared-token']);
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.abc']]])]);

        $business = Business::factory()->create([
            'whatsapp_phone_number_id' => '1234567890',
            'whatsapp_access_token' => null, // Embedded Signup never stores a per-business token.
            'whatsapp_connected_via' => 'embedded_signup',
        ]);

        $result = app(WhatsAppCloudApiService::class)->sendTextMessage($business, '2348012345678', 'Hello');

        $this->assertTrue($result);
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer platform-shared-token'));
    }

    public function test_sending_no_ops_when_neither_a_business_token_nor_a_platform_token_is_configured(): void
    {
        config(['services.whatsapp.system_user_token' => null]);
        Http::fake();

        $business = Business::factory()->create([
            'whatsapp_phone_number_id' => '1234567890',
            'whatsapp_access_token' => null,
        ]);

        $result = app(WhatsAppCloudApiService::class)->sendTextMessage($business, '2348012345678', 'Hello');

        $this->assertFalse($result);
        Http::assertNothingSent();
    }
}
