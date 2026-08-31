<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Coupon;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponManagementTest extends TestCase
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

    public function test_owner_can_create_a_percentage_coupon(): void
    {
        $response = $this->actingAs($this->owner)->post(route('coupons.store'), [
            'code' => 'launch20',
            'type' => 'percentage',
            'value' => 20,
            'max_discount_amount' => 5000,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('coupons.index'));
        $coupon = Coupon::where('business_id', $this->business->id)->firstOrFail();
        $this->assertSame('LAUNCH20', $coupon->code, 'Codes are normalized to uppercase.');
        $this->assertSame('percentage', $coupon->type);
        $this->assertEquals(20, $coupon->value);
        $this->assertEquals(5000, $coupon->max_discount_amount);
    }

    public function test_owner_can_create_a_fixed_amount_coupon(): void
    {
        $response = $this->actingAs($this->owner)->post(route('coupons.store'), [
            'code' => 'FLAT500',
            'type' => 'fixed',
            'value' => 500,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('coupons.index'));
        $this->assertSame(1, Coupon::where('business_id', $this->business->id)->count());
    }

    public function test_a_percentage_value_over_100_is_rejected(): void
    {
        $response = $this->actingAs($this->owner)->post(route('coupons.store'), [
            'code' => 'TOOBIG', 'type' => 'percentage', 'value' => 150, 'is_active' => 1,
        ]);

        $response->assertSessionHasErrors('value');
        $this->assertSame(0, Coupon::count());
    }

    public function test_two_different_businesses_can_use_the_same_code(): void
    {
        $otherBusiness = Business::factory()->create();
        Coupon::create(['business_id' => $otherBusiness->id, 'code' => 'SAVE10', 'type' => 'percentage', 'value' => 10]);

        $response = $this->actingAs($this->owner)->post(route('coupons.store'), [
            'code' => 'SAVE10', 'type' => 'percentage', 'value' => 10, 'is_active' => 1,
        ]);

        $response->assertRedirect(route('coupons.index'));
        $this->assertSame(2, Coupon::withoutGlobalScopes()->where('code', 'SAVE10')->count());
    }

    public function test_the_same_business_cannot_reuse_a_code(): void
    {
        Coupon::create(['business_id' => $this->business->id, 'code' => 'SAVE10', 'type' => 'percentage', 'value' => 10]);

        $response = $this->actingAs($this->owner)->post(route('coupons.store'), [
            'code' => 'save10', 'type' => 'percentage', 'value' => 5, 'is_active' => 1,
        ]);

        $response->assertSessionHasErrors('code');
        $this->assertSame(1, Coupon::where('business_id', $this->business->id)->count());
    }

    public function test_owner_can_update_and_delete_a_coupon(): void
    {
        $coupon = Coupon::create(['business_id' => $this->business->id, 'code' => 'SAVE10', 'type' => 'percentage', 'value' => 10]);

        $this->actingAs($this->owner)->put(route('coupons.update', $coupon), [
            'code' => 'SAVE10', 'type' => 'percentage', 'value' => 15, 'is_active' => 0,
        ])->assertRedirect(route('coupons.index'));

        $this->assertEquals(15, $coupon->fresh()->value);
        $this->assertFalse($coupon->fresh()->is_active);

        $this->actingAs($this->owner)->delete(route('coupons.destroy', $coupon))
            ->assertRedirect(route('coupons.index'));
        $this->assertNull(Coupon::find($coupon->id));
    }

    public function test_a_staff_member_without_the_permission_cannot_manage_coupons(): void
    {
        $staff = User::factory()->create(['business_id' => $this->business->id]);
        $staff->assignRole('Staff');

        $this->actingAs($staff)->get(route('coupons.index'))->assertForbidden();
    }

    public function test_a_staff_member_granted_the_permission_can_manage_coupons(): void
    {
        $staff = User::factory()->create(['business_id' => $this->business->id]);
        $staff->assignRole('Staff');
        $staff->givePermissionTo('manage coupons');

        $this->actingAs($staff)->get(route('coupons.index'))->assertOk();
    }

    public function test_a_coupon_cannot_be_managed_from_another_business(): void
    {
        $otherBusiness = Business::factory()->create();
        $foreignCoupon = Coupon::create(['business_id' => $otherBusiness->id, 'code' => 'SAVE10', 'type' => 'percentage', 'value' => 10]);

        $this->actingAs($this->owner)->get(route('coupons.edit', $foreignCoupon))->assertNotFound();
    }
}
