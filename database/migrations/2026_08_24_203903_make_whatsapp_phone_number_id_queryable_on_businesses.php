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
        // No business has this field populated yet (no settings UI has
        // existed to set it), so this is a safe type change with no data
        // to migrate.
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('whatsapp_phone_number_id');
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->string('whatsapp_phone_number_id')->nullable()->after('whatsapp_number');
            $table->index('whatsapp_phone_number_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('whatsapp_phone_number_id');
        });

        Schema::table('businesses', function (Blueprint $table) {
            $table->text('whatsapp_phone_number_id')->nullable();
        });
    }
};
