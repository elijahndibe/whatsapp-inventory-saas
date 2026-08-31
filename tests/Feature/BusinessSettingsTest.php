<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BusinessSettingsTest extends TestCase
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

    public function test_owner_can_view_and_update_settings(): void
    {
        // The GET below fetches the payout bank list (the business hasn't
        // connected one yet) — faked so this test never hits the real API.
        Http::fake(['api.paystack.co/bank*' => Http::response(['status' => true, 'data' => []])]);

        $this->actingAs($this->owner)->get(route('settings.edit'))->assertOk();

        $response = $this->actingAs($this->owner)->put(route('settings.update'), [
            'name' => 'Renamed Store',
            'currency' => 'USD',
            'timezone' => 'Africa/Lagos',
            'whatsapp_phone_number_id' => '111222333',
            'whatsapp_business_account_id' => '444555666',
            'whatsapp_access_token' => 'super-secret-token',
        ]);

        $response->assertRedirect(route('settings.edit'));
        $fresh = $this->business->fresh();
        $this->assertSame('Renamed Store', $fresh->name);
        $this->assertSame('USD', $fresh->currency);
        $this->assertSame('111222333', $fresh->whatsapp_phone_number_id);
        $this->assertSame('super-secret-token', $fresh->whatsapp_access_token);
        $this->assertTrue($fresh->hasWhatsAppCloudApi());
    }

    public function test_leaving_the_access_token_blank_does_not_erase_the_existing_one(): void
    {
        $this->business->update(['whatsapp_access_token' => 'original-token', 'whatsapp_phone_number_id' => '123']);

        $this->actingAs($this->owner)->put(route('settings.update'), [
            'name' => $this->business->name,
            'currency' => 'NGN',
            'timezone' => 'Africa/Lagos',
            'whatsapp_access_token' => '',
        ]);

        $this->assertSame('original-token', $this->business->fresh()->whatsapp_access_token);
    }

    public function test_owner_can_update_phone_country_and_the_geo_select_fields(): void
    {
        $this->actingAs($this->owner)->put(route('settings.update'), [
            'name' => $this->business->name,
            'phone' => '+233241234567',
            'country' => 'Ghana',
            'currency' => 'GHS',
            'timezone' => 'Africa/Accra',
        ]);

        $fresh = $this->business->fresh();
        $this->assertSame('+233241234567', $fresh->phone);
        $this->assertSame('Ghana', $fresh->country);
        $this->assertSame('GHS', $fresh->currency);
        $this->assertSame('Africa/Accra', $fresh->timezone);
    }

    public function test_changing_the_phone_number_is_blocked_without_verification_when_configured(): void
    {
        config([
            'services.whatsapp.platform_phone_number_id' => 'fake_platform_number_id',
            'services.whatsapp.system_user_token' => 'fake_token',
        ]);
        $this->business->update(['phone' => '+2348011110000']);

        $response = $this->actingAs($this->owner)->put(route('settings.update'), [
            'name' => $this->business->name,
            'phone' => '+2348022220000',
            'currency' => 'NGN',
            'timezone' => 'Africa/Lagos',
        ]);

        $response->assertSessionHasErrors('phone');
        $this->assertSame('+2348011110000', $this->business->fresh()->phone);
    }

    public function test_changing_to_a_verified_phone_number_succeeds(): void
    {
        config([
            'services.whatsapp.platform_phone_number_id' => 'fake_platform_number_id',
            'services.whatsapp.system_user_token' => 'fake_token',
        ]);
        $this->business->update(['phone' => '+2348011110000']);
        \App\Models\PhoneVerification::create([
            'phone' => '+2348022220000',
            'code' => '123456',
            'expires_at' => now()->addMinutes(10),
            'verified_at' => now(),
        ]);

        $response = $this->actingAs($this->owner)->put(route('settings.update'), [
            'name' => $this->business->name,
            'phone' => '+2348022220000',
            'currency' => 'NGN',
            'timezone' => 'Africa/Lagos',
        ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertSame('+2348022220000', $this->business->fresh()->phone);
    }

    public function test_saving_settings_without_changing_the_phone_number_never_requires_verification(): void
    {
        config([
            'services.whatsapp.platform_phone_number_id' => 'fake_platform_number_id',
            'services.whatsapp.system_user_token' => 'fake_token',
        ]);
        $this->business->update(['phone' => '+2348011110000']);

        $response = $this->actingAs($this->owner)->put(route('settings.update'), [
            'name' => 'Renamed while keeping the same phone',
            'phone' => '+2348011110000',
            'currency' => 'NGN',
            'timezone' => 'Africa/Lagos',
        ]);

        $response->assertSessionDoesntHaveErrors();
    }

    public function test_settings_page_never_mentions_the_payment_processor_by_name(): void
    {
        // A seller shouldn't need to know what powers payouts behind the
        // scenes — see settings/edit.blade.php's Payments tab.
        Cache::flush();
        Http::fake(['api.paystack.co/bank*' => Http::response([
            'status' => true,
            'data' => [['name' => 'Guaranty Trust Bank', 'code' => '058']],
        ])]);

        $response = $this->actingAs($this->owner)->get(route('settings.edit'));

        $response->assertOk();
        $response->assertDontSee('Paystack');
    }

    public function test_a_business_without_a_bank_account_yet_is_offered_a_bank_name_dropdown(): void
    {
        Cache::flush();
        Http::fake(['api.paystack.co/bank*' => Http::response([
            'status' => true,
            'data' => [
                ['name' => 'Guaranty Trust Bank', 'code' => '058'],
                ['name' => 'Access Bank', 'code' => '044'],
            ],
        ])]);

        $response = $this->actingAs($this->owner)->get(route('settings.edit'));

        $response->assertOk();
        $response->assertViewHas('banks', function (?array $banks) {
            return $banks !== null && collect($banks)->pluck('name')->contains('Guaranty Trust Bank');
        });
        $response->assertSee('Guaranty Trust Bank');
    }

    public function test_a_business_with_a_bank_account_already_connected_is_not_offered_the_dropdown(): void
    {
        $this->business->update(['paystack_subaccount_code' => 'ACCT_test123']);

        $response = $this->actingAs($this->owner)->get(route('settings.edit'));

        $response->assertOk();
        $response->assertViewHas('banks', null);
    }

    public function test_the_bank_form_falls_back_to_a_plain_field_when_the_bank_list_cannot_be_loaded(): void
    {
        Cache::flush();
        Http::fake(['api.paystack.co/bank*' => Http::response(['status' => false, 'message' => 'unavailable'], 500)]);

        $response = $this->actingAs($this->owner)->get(route('settings.edit'));

        $response->assertOk();
        $response->assertViewHas('banks', null);
    }

    public function test_admin_cannot_access_settings(): void
    {
        $admin = User::factory()->create(['business_id' => $this->business->id]);
        $admin->assignRole('Admin');

        $this->actingAs($admin)->get(route('settings.edit'))->assertForbidden();
    }

    public function test_staff_without_permission_cannot_access_settings(): void
    {
        $staff = User::factory()->create(['business_id' => $this->business->id]);
        $staff->assignRole('Staff');

        $this->actingAs($staff)->get(route('settings.edit'))->assertForbidden();
    }
}
