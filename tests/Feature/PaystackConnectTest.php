<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PaystackConnectTest extends TestCase
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

    public function test_owner_can_connect_a_paystack_subaccount(): void
    {
        Http::fake(['api.paystack.co/subaccount' => Http::response([
            'status' => true,
            'data' => ['subaccount_code' => 'ACCT_test123', 'account_name' => 'Jane Doe'],
        ])]);

        $response = $this->actingAs($this->owner)->post(route('settings.paystack.connect'), [
            'settlement_bank' => '058',
            'account_number' => '0123456789',
        ]);

        $response->assertRedirect(route('settings.edit'));
        $fresh = $this->business->fresh();
        $this->assertSame('ACCT_test123', $fresh->paystack_subaccount_code);
        $this->assertSame('Jane Doe', $fresh->paystack_account_name);
        $this->assertTrue($fresh->hasPaystackSubaccount());
    }

    public function test_a_paystack_failure_does_not_connect_the_account(): void
    {
        Http::fake(['api.paystack.co/subaccount' => Http::response([
            'status' => false,
            'message' => 'Invalid account number',
        ], 400)]);

        $response = $this->actingAs($this->owner)->post(route('settings.paystack.connect'), [
            'settlement_bank' => '058',
            'account_number' => 'bad',
        ]);

        $response->assertSessionHas('error');
        $this->assertNull($this->business->fresh()->paystack_subaccount_code);
    }

    public function test_a_staff_member_without_permission_cannot_connect_paystack(): void
    {
        $staff = User::factory()->create(['business_id' => $this->business->id]);
        $staff->assignRole('Staff');

        $this->actingAs($staff)->post(route('settings.paystack.connect'), [
            'settlement_bank' => '058',
            'account_number' => '0123456789',
        ])->assertForbidden();
    }

    public function test_bank_code_and_account_number_are_required(): void
    {
        $response = $this->actingAs($this->owner)->post(route('settings.paystack.connect'), []);

        $response->assertSessionHasErrors(['settlement_bank', 'account_number']);
    }
}
