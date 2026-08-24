<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // One order can have multiple payment attempts (retries after a
            // failed/abandoned charge), so reference is per-attempt, not
            // reused from the order.
            $table->string('reference')->unique();
            $table->string('gateway')->default('paystack');
            $table->unsignedBigInteger('amount');
            $table->string('currency', 3);
            $table->enum('status', ['pending', 'success', 'failed', 'abandoned'])->default('pending');
            $table->text('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();

            $table->index(['business_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
