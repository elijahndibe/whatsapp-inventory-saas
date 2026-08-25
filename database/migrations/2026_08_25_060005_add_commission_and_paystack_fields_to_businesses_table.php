<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            // Null = use the platform default commission rate.
            $table->decimal('commission_rate', 5, 2)->nullable()->after('allow_overselling');

            $table->string('paystack_subaccount_code')->nullable()->after('commission_rate');
            $table->string('paystack_bank_code')->nullable()->after('paystack_subaccount_code');
            $table->string('paystack_account_number')->nullable()->after('paystack_bank_code');
            $table->string('paystack_account_name')->nullable()->after('paystack_account_number');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn([
                'commission_rate',
                'paystack_subaccount_code',
                'paystack_bank_code',
                'paystack_account_number',
                'paystack_account_name',
            ]);
        });
    }
};
