<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppConnectTest extends TestCase
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

        config([
            'services.whatsapp.app_id' => 'test-app-id',
            'services.whatsapp.app_secret' => 'test-app-secret',
            'services.whatsapp.embedded_signup_config_id' => 'test-config-id',
        ]);
    }

    private function fakeSuccessfulGraphResponses(): void
    {
        Http::fake([
            'graph.facebook.com/oauth/access_token*' => Http::response(['access_token' => 'exchanged-token']),
            'graph.facebook.com/*/subscribed_apps' => Http::response(['success' => true]),
            'graph.facebook.com/*/register' => Http::response(['success' => true]),
            'graph.facebook.com/*' => Http::response(['display_phone_number' => '+234 801 234 5678', 'verified_name' => 'Test Store']),
        ]);
    }

    public function test_owner_can_connect_whatsapp_via_embedded_signup(): void
    {
        $this->fakeSuccessfulGraphResponses();

        $response = $this->actingAs($this->owner)->post(route('settings.whatsapp.connect'), [
            'code' => 'auth-code-123',
            'waba_id' => 'waba-abc',
            'phone_number_id' => 'phone-xyz',
        ]);

        $response->assertRedirect(route('settings.edit'));
        $fresh = $this->business->fresh();
        $this->assertSame('phone-xyz', $fresh->whatsapp_phone_number_id);
        $this->assertSame('waba-abc', $fresh->whatsapp_business_account_id);
        $this->assertSame('+234 801 234 5678', $fresh->whatsapp_display_phone_number);
        $this->assertSame('embedded_signup', $fresh->whatsapp_connected_via);
        $this->assertNull($fresh->whatsapp_access_token, 'Embedded Signup must never store a per-business token.');
        $this->assertNotNull($fresh->whatsapp_connected_at);
        $this->assertTrue($fresh->hasWhatsAppCloudApi());
        $this->assertTrue($fresh->isWhatsAppConnectedViaEmbeddedSignup());

        Http::assertSent(fn ($request) => str_contains($request->url(), '/waba-abc/subscribed_apps'));
    }

    public function test_a_phone_number_already_connected_to_another_business_is_rejected(): void
    {
        Business::factory()->create(['whatsapp_phone_number_id' => 'phone-xyz']);
        $this->fakeSuccessfulGraphResponses();

        $response = $this->actingAs($this->owner)->post(route('settings.whatsapp.connect'), [
            'code' => 'auth-code-123',
            'waba_id' => 'waba-abc',
            'phone_number_id' => 'phone-xyz',
        ]);

        $response->assertSessionHas('error');
        $this->assertNull($this->business->fresh()->whatsapp_phone_number_id);
        Http::assertNothingSent();
    }

    public function test_a_failed_code_exchange_shows_a_friendly_error_and_saves_nothing(): void
    {
        Http::fake(['graph.facebook.com/oauth/access_token*' => Http::response(['error' => ['message' => 'invalid code']], 400)]);

        $response = $this->actingAs($this->owner)->post(route('settings.whatsapp.connect'), [
            'code' => 'bad-code',
            'waba_id' => 'waba-abc',
            'phone_number_id' => 'phone-xyz',
        ]);

        $response->assertSessionHas('error');
        $this->assertNull($this->business->fresh()->whatsapp_phone_number_id);
    }

    public function test_missing_platform_meta_configuration_shows_a_friendly_error(): void
    {
        config(['services.whatsapp.app_id' => null]);

        $response = $this->actingAs($this->owner)->post(route('settings.whatsapp.connect'), [
            'code' => 'auth-code-123',
            'waba_id' => 'waba-abc',
            'phone_number_id' => 'phone-xyz',
        ]);

        $response->assertSessionHas('error');
        $this->assertNull($this->business->fresh()->whatsapp_phone_number_id);
    }

    public function test_code_waba_id_and_phone_number_id_are_required(): void
    {
        $response = $this->actingAs($this->owner)->post(route('settings.whatsapp.connect'), []);

        $response->assertSessionHasErrors(['code', 'waba_id', 'phone_number_id']);
    }

    public function test_a_staff_member_without_permission_cannot_connect_whatsapp(): void
    {
        $staff = User::factory()->create(['business_id' => $this->business->id]);
        $staff->assignRole('Staff');

        $this->actingAs($staff)->post(route('settings.whatsapp.connect'), [
            'code' => 'auth-code-123',
            'waba_id' => 'waba-abc',
            'phone_number_id' => 'phone-xyz',
        ])->assertForbidden();
    }

    public function test_owner_can_disconnect_whatsapp(): void
    {
        Http::fake(['graph.facebook.com/*' => Http::response(['success' => true])]);

        $this->business->update([
            'whatsapp_phone_number_id' => 'phone-xyz',
            'whatsapp_business_account_id' => 'waba-abc',
            'whatsapp_connected_via' => 'embedded_signup',
            'whatsapp_display_phone_number' => '+234 801 234 5678',
            'whatsapp_connected_at' => now(),
        ]);
        config(['services.whatsapp.system_user_token' => 'platform-token']);

        $response = $this->actingAs($this->owner)->post(route('settings.whatsapp.disconnect'));

        $response->assertRedirect(route('settings.edit'));
        $fresh = $this->business->fresh();
        $this->assertNull($fresh->whatsapp_phone_number_id);
        $this->assertNull($fresh->whatsapp_business_account_id);
        $this->assertNull($fresh->whatsapp_connected_via);
        $this->assertFalse($fresh->hasWhatsAppCloudApi());
    }

    public function test_a_staff_member_without_permission_cannot_disconnect_whatsapp(): void
    {
        $staff = User::factory()->create(['business_id' => $this->business->id]);
        $staff->assignRole('Staff');

        $this->actingAs($staff)->post(route('settings.whatsapp.disconnect'))->assertForbidden();
    }
}
