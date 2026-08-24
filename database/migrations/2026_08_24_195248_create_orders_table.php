<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->string('order_number');
            // Guest-facing lookup key for the confirmation page — never expose
            // the auto-increment id in a public URL.
            $table->string('public_token')->unique();

            $table->unsignedBigInteger('subtotal');
            $table->unsignedBigInteger('discount')->default(0);
            $table->unsignedBigInteger('delivery_fee')->default(0);
            $table->unsignedBigInteger('tax')->default(0);
            $table->unsignedBigInteger('total');
            $table->string('currency', 3);

            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded', 'partially_refunded'])->default('pending');
            $table->string('payment_method')->nullable();
            $table->enum('order_status', ['pending', 'confirmed', 'processing', 'ready', 'shipped', 'completed', 'cancelled', 'refunded'])->default('pending');
            $table->string('payment_reference')->nullable();

            $table->text('notes')->nullable();
            $table->text('customer_notes')->nullable();
            $table->string('shipping_address')->nullable();

            $table->timestamps();

            $table->unique(['business_id', 'order_number']);
            $table->index(['business_id', 'order_status']);
            $table->index(['business_id', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
