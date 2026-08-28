<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Supports the seller-initiated "Confirm order & request payment" flow for
 * WhatsApp orders (see OrderController::requestPayment(), OrderService,
 * PaymentService::initializeForOrder()):
 *
 *  - orders.source records which of the two customer journeys created the
 *    order ('storefront' direct-checkout vs 'whatsapp') — set once at
 *    creation and never changed, independent of how it later gets paid,
 *    so reporting (Admin > Transactions) can always show true origin even
 *    after a WhatsApp order is paid via Paystack.
 *  - orders.order_status changes from a native MySQL ENUM to a plain
 *    string, so a new 'awaiting_payment' value can be added without a
 *    database-specific ALTER (ENUM-modification syntax isn't portable —
 *    it doesn't exist on SQLite, which the test suite runs on, and this
 *    app doesn't have doctrine/dbal installed for Schema::change()).
 *    Validity is already enforced at the application layer via
 *    Order::STATUSES and OrderService::updateStatus()'s in_array() check,
 *    so this changes nothing about actual data integrity. Done via an
 *    add-copy-drop-rename so existing orders keep their real status
 *    rather than reverting to the column's default. 'awaiting_payment':
 *    a WhatsApp order the seller has confirmed and generated a payment
 *    link for, but the customer hasn't paid yet. Inventory is NOT
 *    deducted on entering this status (see OrderService::updateStatus)
 *    — it deducts once, later, when payment actually clears, exactly
 *    like the existing direct-checkout path already does.
 *  - payments.authorization_url persists the Paystack checkout URL so the
 *    seller can revisit the order later and re-send/copy the same link
 *    rather than the app silently generating a new Paystack transaction
 *    on every page load.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('source')->nullable()->after('payment_method');
            $table->string('order_status_new')->default('pending')->after('order_status');
        });

        DB::table('orders')->update(['order_status_new' => DB::raw('order_status')]);

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['business_id', 'order_status']);
            $table->dropColumn('order_status');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->renameColumn('order_status_new', 'order_status');
        });

        DB::table('orders')->whereNull('order_status')->update(['order_status' => 'pending']);

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['business_id', 'order_status']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('authorization_url')->nullable()->after('reference');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('authorization_url');
        });

        DB::table('orders')->where('order_status', 'awaiting_payment')->update(['order_status' => 'pending']);

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['business_id', 'order_status']);
            $table->dropColumn('source');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['business_id', 'order_status']);
        });
    }
};
