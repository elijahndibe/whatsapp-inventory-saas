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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp_number')->nullable();
            $table->string('logo')->nullable();
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->default('Nigeria');
            $table->string('currency', 3)->default('NGN');
            $table->string('timezone')->default('Africa/Lagos');
            $table->enum('status', ['active', 'suspended'])->default('active');

            // WhatsApp Cloud API credentials (per-business, encrypted via model casts)
            $table->text('whatsapp_phone_number_id')->nullable();
            $table->text('whatsapp_business_account_id')->nullable();
            $table->text('whatsapp_access_token')->nullable();

            // Storefront / order behaviour
            $table->boolean('allow_overselling')->default(false);

            $table->timestamps();

            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
