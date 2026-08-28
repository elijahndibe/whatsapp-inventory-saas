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
}
