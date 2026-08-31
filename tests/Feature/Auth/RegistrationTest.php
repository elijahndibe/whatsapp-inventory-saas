<?php

namespace Tests\Feature\Auth;

use App\Models\Business;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);
    }

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'business_name' => "Amaka's Fashion Store",
            'name' => 'Amaka Okafor',
            'email' => 'amaka@example.com',
            'phone' => '+2348012345678',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => 1,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('onboarding.show', absolute: false));
    }

    public function test_registering_creates_a_business_and_assigns_the_owner_role(): void
    {
        $this->post('/register', [
            'business_name' => "Amaka's Fashion Store",
            'name' => 'Amaka Okafor',
            'email' => 'amaka@example.com',
            'phone' => '+2348012345678',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => 1,
        ]);

        $business = Business::first();
        $user = User::first();

        $this->assertNotNull($business);
        $this->assertSame("Amaka's Fashion Store", $business->name);
        $this->assertSame('amakas-fashion-store', $business->slug);

        $this->assertSame($business->id, $user->business_id);
        $this->assertTrue($user->hasRole('Owner'));
        $this->assertTrue($user->can('manage staff'));
    }

    public function test_business_name_is_required_to_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Amaka Okafor',
            'email' => 'amaka@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => 1,
        ]);

        $response->assertSessionHasErrors('business_name');
        $this->assertGuest();
    }

    public function test_phone_is_required_to_register(): void
    {
        $response = $this->post('/register', [
            'business_name' => "Amaka's Fashion Store",
            'name' => 'Amaka Okafor',
            'email' => 'amaka@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => 1,
        ]);

        $response->assertSessionHasErrors('phone');
        $this->assertGuest();
    }

    public function test_registration_still_works_when_phone_verification_is_not_configured(): void
    {
        // The default state — nothing in .env.example's
        // WHATSAPP_PLATFORM_PHONE_NUMBER_ID/WHATSAPP_OTP_TEMPLATE_NAME is
        // set. Feature-off must never block signups, only enforce once
        // someone has actually turned it on (see PhoneIsVerified).
        config(['services.whatsapp.platform_phone_number_id' => null]);

        $response = $this->post('/register', [
            'business_name' => "Amaka's Fashion Store",
            'name' => 'Amaka Okafor',
            'email' => 'amaka@example.com',
            'phone' => '+2348012345678',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => 1,
        ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertNotNull(Business::first());
    }

    public function test_registration_is_blocked_when_phone_verification_is_configured_but_not_completed(): void
    {
        config([
            'services.whatsapp.platform_phone_number_id' => 'fake_platform_number_id',
            'services.whatsapp.system_user_token' => 'fake_token',
        ]);

        $response = $this->post('/register', [
            'business_name' => "Amaka's Fashion Store",
            'name' => 'Amaka Okafor',
            'email' => 'amaka@example.com',
            'phone' => '+2348012345678',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => 1,
        ]);

        $response->assertSessionHasErrors('phone');
        $this->assertGuest();
        $this->assertNull(Business::first());
    }

    public function test_agreeing_to_the_terms_is_required_to_register(): void
    {
        $response = $this->post('/register', [
            'business_name' => "Amaka's Fashion Store",
            'name' => 'Amaka Okafor',
            'email' => 'amaka@example.com',
            'phone' => '+2348012345678',
            'password' => 'password',
            'password_confirmation' => 'password',
            // 'terms' intentionally omitted
        ]);

        $response->assertSessionHasErrors('terms');
        $this->assertGuest();
        $this->assertNull(Business::first());
    }

    public function test_registration_succeeds_once_the_phone_has_been_verified(): void
    {
        config([
            'services.whatsapp.platform_phone_number_id' => 'fake_platform_number_id',
            'services.whatsapp.system_user_token' => 'fake_token',
        ]);

        \App\Models\PhoneVerification::create([
            'phone' => '+2348012345678',
            'code' => '123456',
            'expires_at' => now()->addMinutes(10),
            'verified_at' => now(),
        ]);

        $response = $this->post('/register', [
            'business_name' => "Amaka's Fashion Store",
            'name' => 'Amaka Okafor',
            'email' => 'amaka@example.com',
            'phone' => '+2348012345678',
            'password' => 'password',
            'password_confirmation' => 'password',
            'terms' => 1,
        ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertSame('+2348012345678', Business::first()->phone);
    }
}
