<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Snapshot values, computed once server-side at initialize time and
            // never recalculated from the live commission config afterwards —
            // this is what makes historical reports accurate forever, even
            // after the platform or seller rate later changes.
            $table->decimal('commission_rate', 5, 2)->nullable()->after('amount');
            $table->unsignedBigInteger('commission_amount')->nullable()->after('commission_rate');
            $table->unsignedBigInteger('seller_amount')->nullable()->after('commission_amount');
            $table->unsignedBigInteger('payment_fee')->nullable()->after('seller_amount');
            $table->enum('settlement_status', ['pending', 'settled', 'platform_held', 'failed'])
                ->default('pending')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'commission_rate',
                'commission_amount',
                'seller_amount',
                'payment_fee',
                'settlement_status',
            ]);
        });
    }
};
