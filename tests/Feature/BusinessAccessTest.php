<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_user_from_a_suspended_business_is_logged_out_on_the_next_request(): void
    {
        $business = Business::factory()->suspended()->create();
        $user = User::factory()->create(['business_id' => $business->id]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_a_deactivated_user_is_logged_out_on_the_next_request(): void
    {
        $business = Business::factory()->create();
        $user = User::factory()->create(['business_id' => $business->id, 'status' => 'inactive']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_a_user_from_an_active_business_can_reach_the_dashboard(): void
    {
        $business = Business::factory()->create();
        $user = User::factory()->create(['business_id' => $business->id]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
    }
}
