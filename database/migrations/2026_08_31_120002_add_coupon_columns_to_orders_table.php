<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * coupon_code is a snapshot of the code as it was at redemption time,
 * independent of coupon_id — same reasoning as Payment's commission
 * snapshot: if the Coupon row is later edited or deleted, this order's
 * own record of what code it used must never change or disappear.
 * coupon_id is nullOnDelete (not cascade) for exactly that reason — a
 * deleted coupon must never take historical orders down with it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('coupon_id')->nullable()->after('customer_id')->constrained()->nullOnDelete();
            $table->string('coupon_code')->nullable()->after('coupon_id');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('coupon_id');
            $table->dropColumn('coupon_code');
        });
    }
};
