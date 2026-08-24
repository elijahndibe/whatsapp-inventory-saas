<?php

namespace Tests\Unit;

use App\Models\Business;
use App\Models\Concerns\BelongsToTenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Exercises the tenant-isolation mechanism every business-owned model
 * (Product, Order, Customer, ...) will rely on. This is the single most
 * safety-critical piece of the whole app, so it is tested directly
 * against a throwaway table rather than waiting for a real domain model.
 */
class BelongsToTenantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained();
            $table->string('name');
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('widgets');

        parent::tearDown();
    }

    public function test_queries_are_automatically_scoped_to_the_authenticated_users_business(): void
    {
        $businessA = Business::factory()->create();
        $businessB = Business::factory()->create();

        $userA = User::factory()->create(['business_id' => $businessA->id]);
        $userB = User::factory()->create(['business_id' => $businessB->id]);

        TenantWidget::withoutGlobalScopes()->create(['business_id' => $businessA->id, 'name' => 'A-widget']);
        TenantWidget::withoutGlobalScopes()->create(['business_id' => $businessB->id, 'name' => 'B-widget']);

        $this->actingAs($userA);
        $this->assertSame(['A-widget'], TenantWidget::pluck('name')->all());

        $this->actingAs($userB);
        $this->assertSame(['B-widget'], TenantWidget::pluck('name')->all());
    }

    public function test_a_user_cannot_fetch_another_businesss_record_by_id(): void
    {
        $businessA = Business::factory()->create();
        $businessB = Business::factory()->create();
        $userA = User::factory()->create(['business_id' => $businessA->id]);

        $otherBusinessWidget = TenantWidget::withoutGlobalScopes()
            ->create(['business_id' => $businessB->id, 'name' => 'not-yours']);

        $this->actingAs($userA);

        $this->assertNull(TenantWidget::find($otherBusinessWidget->id));
    }

    public function test_business_id_is_auto_filled_on_create(): void
    {
        $business = Business::factory()->create();
        $user = User::factory()->create(['business_id' => $business->id]);

        $this->actingAs($user);

        $widget = TenantWidget::create(['name' => 'auto-filled']);

        $this->assertSame($business->id, $widget->business_id);
    }

    public function test_for_business_scope_bypasses_the_authenticated_user_for_background_jobs(): void
    {
        $business = Business::factory()->create();
        TenantWidget::withoutGlobalScopes()->create(['business_id' => $business->id, 'name' => 'job-context']);

        // No authenticated user (simulates a queued job / webhook handler).
        $this->assertSame(
            ['job-context'],
            TenantWidget::forBusiness($business->id)->pluck('name')->all()
        );
    }
}

class TenantWidget extends Model
{
    use BelongsToTenant;

    protected $table = 'widgets';

    protected $fillable = ['business_id', 'name'];
}
