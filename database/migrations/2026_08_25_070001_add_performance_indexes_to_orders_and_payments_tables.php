<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Supports the report timeline query (Order::where('payment_status','paid')
 * ->where('created_at', ...)), the admin dashboard's date-scoped recent
 * orders, and the admin Transactions page's date/status filtering and
 * GMV/commission aggregate sums — all of which scan by these column
 * combinations as transaction volume grows.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['business_id', 'created_at']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['business_id', 'created_at']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
        });
    }
};
