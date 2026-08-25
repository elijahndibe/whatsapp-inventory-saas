<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->unsignedBigInteger('price')->default(0); // minor units
            $table->string('currency', 3)->default('NGN');
            $table->unsignedInteger('duration_days')->default(30);

            // Null = unlimited.
            $table->unsignedInteger('max_products')->nullable();
            $table->unsignedInteger('max_orders_per_month')->nullable();
            $table->unsignedInteger('max_staff')->nullable();
            $table->unsignedInteger('max_locations')->nullable();

            // Feature flags: whatsapp_cloud_api, paystack, invoices,
            // advanced_analytics, priority_support, etc. Keeping this as a
            // JSON bag (rather than one column per feature) is what makes
            // limits configurable from the admin panel instead of requiring
            // a migration every time a new gate is added.
            $table->json('features')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
