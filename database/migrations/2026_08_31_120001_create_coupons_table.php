<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `value`'s meaning depends on `type` (mirrors how `businesses.commission_rate`
 * and `payments.commission_rate` are already plain decimals, not
 * minor-currency-unit integers, elsewhere in this app):
 *  - type=percentage: value is the percent itself, e.g. 15.00 = 15% off.
 *  - type=fixed: value is a major-currency-unit amount, e.g. 500.00 = ₦500 off.
 * Same major-unit convention applies to max_discount_amount (the cap on a
 * percentage discount) and minimum_order_amount (subtotal threshold to
 * qualify) — both compared directly against Order::subtotal (itself a
 * major-unit float via its own Attribute accessor), so no minor-unit
 * conversion is needed anywhere in the coupon-validation code path.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->enum('type', ['percentage', 'fixed']);
            $table->decimal('value', 10, 2);
            $table->decimal('max_discount_amount', 10, 2)->nullable();
            $table->decimal('minimum_order_amount', 10, 2)->nullable();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_limit_per_customer')->nullable();
            $table->unsignedInteger('times_used')->default(0);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Codes only need to be unique within a business, not
            // platform-wide — two different sellers can both run "SAVE10".
            $table->unique(['business_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
