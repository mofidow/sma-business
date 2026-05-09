<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shop_back_in_stock_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variation_id')->nullable()->constrained()->nullOnDelete();
            $table->string('email');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('notified_at')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'variation_id', 'email'], 'unique_back_in_stock');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shop_back_in_stock_subscriptions');
    }
};
