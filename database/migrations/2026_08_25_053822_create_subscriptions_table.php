<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();

            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->timestamp('starts_at');
            $table->timestamp('ends_at')->nullable(); // null = does not expire (e.g. Free)

            $table->string('payment_reference')->nullable();
            $table->unsignedBigInteger('amount_paid')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index(['business_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
