<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessLocation;
use App\Models\Plan;
use App\Models\Product;
use App\Models\User;
use App\Services\SubscriptionService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationManagementTest extends TestCase
{
    use RefreshDatabase;

    private Business $business;
    private User $owner;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->business = Business::factory()->create();
        $this->owner = User::factory()->create(['business_id' => $this->business->id]);
        $this->owner->assignRole('Owner');
    }

    public function test_owner_can_create_a_location_and_the_first_one_becomes_default(): void
    {
        $response = $this->actingAs($this->owner)->post(route('locations.store'), [
            'name' => 'Main Warehouse',
            'status' => 'active',
        ]);

        $response->assertRedirect(route('locations.index'));
        $location = BusinessLocation::where('business_id', $this->business->id)->firstOrFail();
        $this->assertTrue($location->is_default);
    }

    public function test_the_default_location_cannot_be_deleted(): void
    {
        $location = $this->business->locations()->create(['name' => 'Main', 'status' => 'active', 'is_default' => true]);

        $response = $this->actingAs($this->owner)->delete(route('locations.destroy', $location));

        $response->assertSessionHas('error');
        $this->assertModelExists($location);
    }

    public function test_a_location_with_allocated_stock_cannot_be_deleted(): void
    {
        $location = $this->business->locations()->create(['name' => 'Branch', 'status' => 'active']);
        $product = Product::factory()->create(['business_id' => $this->business->id]);
        $product->locationStock()->create(['business_id' => $this->business->id, 'location_id' => $location->id, 'quantity' => 5]);

        $response = $this->actingAs($this->owner)->delete(route('locations.destroy', $location));

        $response->assertSessionHas('error');
        $this->assertModelExists($location);
    }

    public function test_location_creation_is_blocked_once_the_plan_limit_is_reached(): void
    {
        $plan = Plan::create(['name' => 'Solo', 'price' => 0, 'max_locations' => 1]);
        app(SubscriptionService::class)->subscribeToPlan($this->business, $plan);
        $this->business->locations()->create(['name' => 'Main', 'status' => 'active', 'is_default' => true]);

        $response = $this->actingAs($this->owner)->post(route('locations.store'), [
            'name' => 'Second Branch',
            'status' => 'active',
        ]);

        $response->assertSessionHas('error');
        $this->assertSame(1, $this->business->locations()->count());
    }

    public function test_a_staff_member_without_permission_cannot_manage_locations(): void
    {
        $staff = User::factory()->create(['business_id' => $this->business->id]);
        $staff->assignRole('Staff');

        $this->actingAs($staff)->get(route('locations.index'))->assertForbidden();
    }
}
