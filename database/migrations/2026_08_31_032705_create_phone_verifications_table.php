<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Deliberately not tied to a user/business — a phone gets verified
        // *before* the business exists at registration, and Settings needs
        // to verify a candidate new number before it's saved over the old
        // one. Callers check "is this exact phone string verified within
        // the last N minutes" (PhoneVerificationService::isVerified())
        // rather than looking this table up by owner.
        Schema::create('phone_verifications', function (Blueprint $table) {
            $table->id();
            $table->string('phone')->index();
            $table->string('code', 6);
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('phone_verifications');
    }
};
