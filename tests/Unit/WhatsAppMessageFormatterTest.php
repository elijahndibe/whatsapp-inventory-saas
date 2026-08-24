<?php

namespace Tests\Unit;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Order;
use App\Services\WhatsAppMessageFormatter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppMessageFormatterTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_formats_an_order_into_the_expected_message(): void
    {
        $business = Business::factory()->create(['name' => 'Amaka\'s Fashion Store', 'currency' => 'NGN', 'whatsapp_number' => '2348012345678']);
        $customer = Customer::factory()->create(['business_id' => $business->id, 'name' => 'John Doe', 'phone' => '08012345678']);
        $order = Order::factory()->create([
            'business_id' => $business->id,
            'customer_id' => $customer->id,
            'order_number' => 'ORD-10025',
            'subtotal' => 195000,
            'delivery_fee' => 5000,
            'total' => 200000,
            'currency' => 'NGN',
            'shipping_address' => 'Lagos',
        ]);
        $order->items()->create(['product_id' => null, 'product_name' => 'Nike Air Max', 'quantity' => 2, 'price' => 85000, 'subtotal' => 170000]);
        $order->items()->create(['product_id' => null, 'product_name' => 'T-Shirt', 'quantity' => 1, 'price' => 25000, 'subtotal' => 25000]);

        $message = app(WhatsAppMessageFormatter::class)->forOrder($order);

        $this->assertStringContainsString("Hello Amaka's Fashion Store,", $message);
        $this->assertStringContainsString('Order: #ORD-10025', $message);
        $this->assertStringContainsString('1. Nike Air Max x2 - ₦170,000.00', $message);
        $this->assertStringContainsString('2. T-Shirt x1 - ₦25,000.00', $message);
        $this->assertStringContainsString('Subtotal: ₦195,000.00', $message);
        $this->assertStringContainsString('Delivery: ₦5,000.00', $message);
        $this->assertStringContainsString('Total: ₦200,000.00', $message);
        $this->assertStringContainsString('Name: John Doe', $message);
        $this->assertStringContainsString('Phone: 08012345678', $message);
        $this->assertStringContainsString('Address: Lagos', $message);
    }

    public function test_chat_url_is_null_when_the_business_has_no_whatsapp_number(): void
    {
        $business = Business::factory()->create(['whatsapp_number' => null, 'phone' => null]);
        $customer = Customer::factory()->create(['business_id' => $business->id]);
        $order = Order::factory()->create(['business_id' => $business->id, 'customer_id' => $customer->id]);

        $url = app(WhatsAppMessageFormatter::class)->chatUrl($order);

        $this->assertNull($url);
    }

    public function test_chat_url_strips_non_digit_characters_from_the_number(): void
    {
        $business = Business::factory()->create(['whatsapp_number' => '+234 801 234 5678']);
        $customer = Customer::factory()->create(['business_id' => $business->id]);
        $order = Order::factory()->create(['business_id' => $business->id, 'customer_id' => $customer->id]);

        $url = app(WhatsAppMessageFormatter::class)->chatUrl($order);

        $this->assertStringStartsWith('https://wa.me/2348012345678?text=', $url);
    }
}
