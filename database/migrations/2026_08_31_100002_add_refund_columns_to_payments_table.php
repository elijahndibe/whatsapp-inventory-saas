<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tracks refunds reported by Paystack (refund.processed webhook) against
 * an already-successful payment. Deliberately separate from `status`
 * (pending/success/failed/abandoned, which describes the original charge
 * attempt) rather than overloading it with a 'refunded' value — a
 * refund is a later, independent event, and refunded_amount can be less
 * than amount for a partial refund, which a single status value can't
 * represent cleanly.
 *
 * commission_amount/seller_amount are NOT touched by a refund — the
 * platform's commission on a sale it already facilitated is kept
 * regardless (a deliberate product decision), so those columns keep
 * meaning exactly what they always have: the permanent snapshot of what
 * was earned at the time of the original charge.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('refunded_amount')->nullable()->after('payment_fee');
            $table->timestamp('refunded_at')->nullable()->after('refunded_amount');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['refunded_amount', 'refunded_at']);
        });
    }
};
