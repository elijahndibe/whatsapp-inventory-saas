<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;
use App\Notifications\NewOrderNotification;
use App\Notifications\PaymentReceivedNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationPreferencesTest extends TestCase
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

    public function test_a_user_with_no_saved_preferences_is_emailed_by_default(): void
    {
        $this->assertTrue($this->owner->wantsEmailNotification('new_order'));
        $this->assertTrue($this->owner->wantsEmailNotification('payment_received'));
        $this->assertTrue($this->owner->wantsEmailNotification('low_stock'));
    }

    public function test_saving_preferences_only_keeps_the_submitted_types_enabled(): void
    {
        $response = $this->actingAs($this->owner)->put(route('notification-preferences.update'), [
            'email' => ['new_order'],
        ]);

        $response->assertRedirect(route('settings.edit').'#notifications');

        $fresh = $this->owner->fresh();
        $this->assertTrue($fresh->wantsEmailNotification('new_order'));
        $this->assertFalse($fresh->wantsEmailNotification('payment_received'));
        $this->assertFalse($fresh->wantsEmailNotification('low_stock'));
    }

    public function test_submitting_no_types_turns_all_email_notifications_off(): void
    {
        $this->actingAs($this->owner)->put(route('notification-preferences.update'), []);

        $fresh = $this->owner->fresh();
        $this->assertFalse($fresh->wantsEmailNotification('new_order'));
        $this->assertFalse($fresh->wantsEmailNotification('payment_received'));
        $this->assertFalse($fresh->wantsEmailNotification('low_stock'));
    }

    public function test_a_disabled_type_drops_the_mail_channel_but_keeps_the_in_app_bell(): void
    {
        $this->owner->update(['notification_preferences' => ['low_stock' => false]]);

        $product = Product::factory()->for($this->business)->create(['stock_quantity' => 0]);
        $channels = (new LowStockNotification($product))->via($this->owner);

        $this->assertNotContains('mail', $channels);
        $this->assertContains('database', $channels);
    }

    public function test_an_enabled_type_keeps_both_channels(): void
    {
        $order = Order::factory()->for($this->business)->create();

        $channels = (new NewOrderNotification($order))->via($this->owner);
        $this->assertContains('mail', $channels);
        $this->assertContains('database', $channels);

        $channels = (new PaymentReceivedNotification($order))->via($this->owner);
        $this->assertContains('mail', $channels);
        $this->assertContains('database', $channels);
    }

    public function test_the_settings_notifications_tab_reflects_saved_preferences(): void
    {
        $this->owner->update(['notification_preferences' => ['new_order' => false, 'payment_received' => true, 'low_stock' => true]]);

        $this->actingAs($this->owner)->get(route('settings.edit'))->assertOk();
    }
}
