<?php

namespace Tests\Feature;

use App\Models\PhoneVerification;
use App\Services\PhoneVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class PhoneVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.whatsapp.platform_phone_number_id' => 'fake_platform_number_id',
            'services.whatsapp.system_user_token' => 'fake_token',
            'services.whatsapp.otp_template_name' => 'phone_verification',
        ]);
    }

    public function test_send_endpoint_is_a_no_op_message_when_not_configured(): void
    {
        config(['services.whatsapp.platform_phone_number_id' => null]);

        $response = $this->postJson('/phone-verification/send', ['phone' => '+2348012345678']);

        $response->assertStatus(422);
        $this->assertFalse($response->json('sent'));
        $this->assertDatabaseCount('phone_verifications', 0);
    }

    public function test_send_endpoint_generates_and_delivers_a_code(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.test']]], 200)]);

        $response = $this->postJson('/phone-verification/send', ['phone' => '+2348012345678']);

        $response->assertOk();
        $this->assertTrue($response->json('sent'));

        $verification = PhoneVerification::where('phone', '+2348012345678')->first();
        $this->assertNotNull($verification);
        $this->assertNull($verification->verified_at);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $verification->code);

        Http::assertSent(function ($request) {
            return $request['type'] === 'template'
                && $request['template']['name'] === 'phone_verification'
                && $request['to'] === '2348012345678';
        });
    }

    public function test_send_endpoint_is_rate_limited_per_phone(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['messages' => [['id' => 'wamid.test']]], 200)]);

        $this->postJson('/phone-verification/send', ['phone' => '+2348012345678'])->assertOk();
        $response = $this->postJson('/phone-verification/send', ['phone' => '+2348012345678']);

        $response->assertStatus(422);
        $this->assertFalse($response->json('sent'));
        $this->assertDatabaseCount('phone_verifications', 1);

        RateLimiter::clear('phone-verification-send:+2348012345678');
    }

    public function test_verify_endpoint_accepts_the_correct_code(): void
    {
        PhoneVerification::create([
            'phone' => '+2348012345678',
            'code' => '654321',
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/phone-verification/verify', ['phone' => '+2348012345678', 'code' => '654321']);

        $response->assertOk();
        $this->assertTrue($response->json('verified'));
        $this->assertTrue(app(PhoneVerificationService::class)->isVerified('+2348012345678'));
    }

    public function test_verify_endpoint_rejects_an_incorrect_code_and_counts_the_attempt(): void
    {
        $verification = PhoneVerification::create([
            'phone' => '+2348012345678',
            'code' => '654321',
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/phone-verification/verify', ['phone' => '+2348012345678', 'code' => '000000']);

        $response->assertStatus(422);
        $this->assertFalse($response->json('verified'));
        $this->assertSame(1, $verification->fresh()->attempts);
    }

    public function test_verify_endpoint_rejects_an_expired_code(): void
    {
        PhoneVerification::create([
            'phone' => '+2348012345678',
            'code' => '654321',
            'expires_at' => now()->subMinute(),
        ]);

        $response = $this->postJson('/phone-verification/verify', ['phone' => '+2348012345678', 'code' => '654321']);

        $response->assertStatus(422);
        $this->assertFalse($response->json('verified'));
    }

    public function test_five_incorrect_attempts_locks_the_code_out(): void
    {
        PhoneVerification::create([
            'phone' => '+2348012345678',
            'code' => '654321',
            'expires_at' => now()->addMinutes(10),
            'attempts' => 5,
        ]);

        $response = $this->postJson('/phone-verification/verify', ['phone' => '+2348012345678', 'code' => '654321']);

        $response->assertStatus(422);
        $this->assertFalse($response->json('verified'));
    }

    public function test_is_verified_is_false_outside_the_trust_window(): void
    {
        PhoneVerification::create([
            'phone' => '+2348012345678',
            'code' => '654321',
            'expires_at' => now()->addMinutes(10),
            'verified_at' => now()->subHours(2),
        ]);

        $this->assertFalse(app(PhoneVerificationService::class)->isVerified('+2348012345678'));
    }
}
