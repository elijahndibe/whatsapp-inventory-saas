<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            // Null = onboarding checklist still showing / not dismissed.
            // Set once the owner finishes the checklist or explicitly skips it.
            $table->timestamp('onboarding_completed_at')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn('onboarding_completed_at');
        });
    }
};
