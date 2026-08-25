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
        Schema::table('inventory_transactions', function (Blueprint $table) {
            // Only populated for type='transfer': which locations the stock
            // moved between. Null for every other transaction type.
            $table->foreignId('from_location_id')->nullable()->after('reference_id')
                ->constrained('business_locations')->nullOnDelete();
            $table->foreignId('to_location_id')->nullable()->after('from_location_id')
                ->constrained('business_locations')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_transactions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('from_location_id');
            $table->dropConstrainedForeignId('to_location_id');
        });
    }
};
