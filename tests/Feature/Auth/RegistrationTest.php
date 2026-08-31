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
        ]);

        $response->assertSessionHasErrors('business_name');
        $this->assertGuest();
    }

    public function test_the_detected_country_currency_and_timezone_are_saved_when_submitted(): void
    {
        $this->post('/register', [
            'business_name' => 'Accra Traders',
            'name' => 'Kwame Mensah',
            'email' => 'kwame@example.com',
            'phone' => '+233241234567',
            'country' => 'Ghana',
            'currency' => 'GHS',
            'timezone' => 'Africa/Accra',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $business = Business::first();

        $this->assertSame('Ghana', $business->country);
        $this->assertSame('GHS', $business->currency);
        $this->assertSame('Africa/Accra', $business->timezone);
        $this->assertSame('+233241234567', $business->phone);
    }

    public function test_omitting_country_currency_and_timezone_falls_back_to_the_column_defaults(): void
    {
        // Simulates JS-disabled registration, or a browser whose timezone
        // isn't in the curated country list — the fields simply aren't
        // submitted, and the businesses table's own defaults (Nigeria/
        // NGN/Africa/Lagos) apply exactly as they did before this feature.
        $this->post('/register', [
            'business_name' => 'No JS Store',
            'name' => 'Tola Adeyemi',
            'email' => 'tola@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $business = Business::first();

        $this->assertSame('Nigeria', $business->country);
        $this->assertSame('NGN', $business->currency);
        $this->assertSame('Africa/Lagos', $business->timezone);
    }
}
