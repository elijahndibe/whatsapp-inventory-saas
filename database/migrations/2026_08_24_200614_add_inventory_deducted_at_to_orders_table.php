<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Idempotency guard: whether stock has already been deducted for
            // this order, so re-saving or re-visiting a status can never
            // deduct (or later, refund-restock) twice.
            $table->timestamp('inventory_deducted_at')->nullable()->after('order_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('inventory_deducted_at');
        });
    }
};
