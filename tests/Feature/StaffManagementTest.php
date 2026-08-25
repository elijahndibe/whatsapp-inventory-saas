<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Plan;
use App\Models\User;
use App\Services\SubscriptionService;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffManagementTest extends TestCase
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

    public function test_owner_can_add_a_staff_member_with_selected_permissions(): void
    {
        $response = $this->actingAs($this->owner)->post(route('staff.store'), [
            'name' => 'Tunde Bakare',
            'email' => 'tunde@example.com',
            'role' => 'Staff',
            'permissions' => ['view orders', 'update orders'],
        ]);

        $response->assertRedirect(route('staff.index'));
        $staff = User::where('email', 'tunde@example.com')->firstOrFail();
        $this->assertSame($this->business->id, $staff->business_id);
        $this->assertTrue($staff->hasRole('Staff'));
        $this->assertTrue($staff->can('view orders'));
        $this->assertTrue($staff->can('update orders'));
        $this->assertFalse($staff->can('manage staff'));
    }

    public function test_admin_role_gets_the_admin_permission_set_not_hand_picked_ones(): void
    {
        $this->actingAs($this->owner)->post(route('staff.store'), [
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'Admin',
        ]);

        $admin = User::where('email', 'admin@example.com')->firstOrFail();
        $this->assertTrue($admin->hasRole('Admin'));
        $this->assertTrue($admin->can('view orders')); // via role
        $this->assertFalse($admin->can('manage staff')); // Admin excluded per RolesAndPermissionsSeeder
    }

    public function test_owner_can_update_a_staff_members_permissions_and_status(): void
    {
        $staff = User::factory()->create(['business_id' => $this->business->id]);
        $staff->assignRole('Staff');
        $staff->givePermissionTo('view orders');

        $response = $this->actingAs($this->owner)->put(route('staff.update', $staff), [
            'role' => 'Staff',
            'status' => 'inactive',
            'permissions' => ['view customers'],
        ]);

        $response->assertRedirect(route('staff.index'));
        $fresh = $staff->fresh();
        $this->assertSame('inactive', $fresh->status);
        $this->assertTrue($fresh->can('view customers'));
        $this->assertFalse($fresh->can('view orders'), 'Permissions not in the new submission must be removed, not merged.');
    }

    public function test_the_owner_account_cannot_be_edited_from_the_staff_screen(): void
    {
        $response = $this->actingAs($this->owner)->get(route('staff.edit', $this->owner));

        $response->assertForbidden();
    }

    public function test_a_user_cannot_manage_staff_belonging_to_another_business(): void
    {
        $otherBusiness = Business::factory()->create();
        $otherStaff = User::factory()->create(['business_id' => $otherBusiness->id]);
        $otherStaff->assignRole('Staff');

        $editResponse = $this->actingAs($this->owner)->get(route('staff.edit', $otherStaff));
        $editResponse->assertNotFound();

        $updateResponse = $this->actingAs($this->owner)->put(route('staff.update', $otherStaff), [
            'role' => 'Admin', 'status' => 'active',
        ]);
        $updateResponse->assertNotFound();

        // Confirm the other business's staff member was truly untouched.
        $this->assertTrue($otherStaff->fresh()->hasRole('Staff'));
    }

    public function test_admin_cannot_manage_staff_only_owner_can(): void
    {
        $admin = User::factory()->create(['business_id' => $this->business->id]);
        $admin->assignRole('Admin');

        $this->actingAs($admin)->get(route('staff.index'))->assertForbidden();
    }

    public function test_adding_staff_is_blocked_once_the_plan_limit_is_reached(): void
    {
        $plan = Plan::create(['name' => 'Solo', 'price' => 0, 'max_staff' => 1]);
        app(SubscriptionService::class)->subscribeToPlan($this->business, $plan);
        // The owner already counts as the one seat.

        $response = $this->actingAs($this->owner)->post(route('staff.store'), [
            'name' => 'Extra Person',
            'email' => 'extra@example.com',
            'role' => 'Staff',
        ]);

        $response->assertSessionHas('error');
        $this->assertNull(User::where('email', 'extra@example.com')->first());
    }
}
