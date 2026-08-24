<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('sku')->nullable();
            $table->unsignedBigInteger('price');
            $table->unsignedBigInteger('cost_price')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->unsignedInteger('low_stock_threshold')->default(5);
            $table->string('image')->nullable();
            $table->enum('status', ['active', 'inactive', 'archived'])->default('active');
            $table->boolean('featured')->default(false);
            $table->timestamps();

            $table->unique(['business_id', 'slug']);
            $table->unique(['business_id', 'sku']);
            $table->index(['business_id', 'status']);
            $table->index(['business_id', 'category_id']);
            $table->index(['business_id', 'featured']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
