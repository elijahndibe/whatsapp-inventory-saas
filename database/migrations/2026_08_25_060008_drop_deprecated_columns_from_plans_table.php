<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * max_products/max_orders_per_month/max_staff/max_locations and the
 * features JSON column are superseded by the features/plan_features
 * tables (see create_features_table / create_plan_features_table). Their
 * values are migrated into plan_features rows by PlansSeeder before this
 * runs in the normal seed order, so no data is lost — this just removes
 * the now-redundant second source of truth.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['max_products', 'max_orders_per_month', 'max_staff', 'max_locations', 'features']);
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->unsignedInteger('max_products')->nullable();
            $table->unsignedInteger('max_orders_per_month')->nullable();
            $table->unsignedInteger('max_staff')->nullable();
            $table->unsignedInteger('max_locations')->nullable();
            $table->json('features')->nullable();
        });
    }
};
