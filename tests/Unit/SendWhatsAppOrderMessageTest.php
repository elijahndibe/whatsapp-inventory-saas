<?php

namespace Tests\Unit;

use App\Jobs\SendWhatsAppOrderMessage;
use App\Models\Business;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SendWhatsAppOrderMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_sends_a_status_message_when_the_business_has_cloud_api_configured(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.1']]])]);

        $business = Business::factory()->create([
            'whatsapp_phone_number_id' => '1234567890',
            'whatsapp_access_token' => 'token',
        ]);
        $customer = Customer::factory()->create(['business_id' => $business->id, 'phone' => '08012345678']);
        $order = Order::create([
            'business_id' => $business->id, 'customer_id' => $customer->id,
            'subtotal' => 100, 'total' => 100, 'currency' => 'NGN', 'order_status' => 'confirmed',
        ]);

        (new SendWhatsAppOrderMessage($order->id, 'order_confirmed'))->handle(
            app(\App\Services\WhatsAppCloudApiService::class),
            app(\App\Services\WhatsAppMessageFormatter::class),
        );

        Http::assertSent(fn ($request) => str_contains($request->url(), '1234567890/messages')
            && str_contains($request['text']['body'], 'confirmed'));
    }

    public function test_it_no_ops_when_the_business_has_no_cloud_api_configured(): void
    {
        Http::fake();

        $business = Business::factory()->create(['whatsapp_phone_number_id' => null, 'whatsapp_access_token' => null]);
        $customer = Customer::factory()->create(['business_id' => $business->id]);
        $order = Order::create([
            'business_id' => $business->id, 'customer_id' => $customer->id,
            'subtotal' => 100, 'total' => 100, 'currency' => 'NGN',
        ]);

        (new SendWhatsAppOrderMessage($order->id, 'order_confirmed'))->handle(
            app(\App\Services\WhatsAppCloudApiService::class),
            app(\App\Services\WhatsAppMessageFormatter::class),
        );

        Http::assertNothingSent();
    }
}
