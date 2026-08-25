<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Plan;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BillingTest extends TestCase
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

    public function test_owner_can_view_the_billing_page(): void
    {
        Plan::create(['name' => 'Free', 'slug' => 'free', 'price' => 0]);

        $this->actingAs($this->owner)->get(route('billing.index'))->assertOk();
    }

    public function test_switching_to_a_free_plan_is_immediate_with_no_payment(): void
    {
        $plan = Plan::create(['name' => 'Free', 'slug' => 'free', 'price' => 0]);

        $response = $this->actingAs($this->owner)->post(route('billing.subscribe', $plan));

        $response->assertRedirect(route('billing.index'));
        $this->assertSame($plan->id, $this->business->currentSubscription()->plan_id);
    }

    public function test_subscribing_to_a_paid_plan_redirects_to_paystack(): void
    {
        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => ['authorization_url' => 'https://checkout.paystack.com/sub123', 'access_code' => 'x', 'reference' => 'x'],
            ]),
        ]);

        $plan = Plan::create(['name' => 'Business', 'price' => 150, 'currency' => 'NGN']);

        $response = $this->actingAs($this->owner)->post(route('billing.subscribe', $plan));

        $response->assertRedirect('https://checkout.paystack.com/sub123');

        Http::assertSent(fn ($request) => ($request['amount'] ?? null) === 15000
            && ($request['metadata']['plan_id'] ?? null) === $plan->id);
    }

    public function test_the_callback_activates_the_subscription_after_verification(): void
    {
        $plan = Plan::create(['name' => 'Business', 'price' => 150, 'currency' => 'NGN']);

        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => [
                    'reference' => 'SUB-CALLBACKTEST',
                    'status' => 'success',
                    'amount' => 15000,
                    'metadata' => ['business_id' => $this->business->id, 'plan_id' => $plan->id],
                ],
            ]),
        ]);

        $response = $this->actingAs($this->owner)->get(route('billing.callback', ['reference' => 'SUB-CALLBACKTEST']));

        $response->assertRedirect(route('billing.index'));
        $this->assertSame($plan->id, $this->business->currentSubscription()->plan_id);
    }

    public function test_a_staff_member_without_permission_cannot_access_billing(): void
    {
        $staff = User::factory()->create(['business_id' => $this->business->id]);
        $staff->assignRole('Staff');

        $this->actingAs($staff)->get(route('billing.index'))->assertForbidden();
    }
}
