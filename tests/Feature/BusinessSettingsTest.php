<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
