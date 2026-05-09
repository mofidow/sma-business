<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cost_allocations')) {
            return;
        }
        Schema::create('cost_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_item_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('purchase_item_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('variation_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('return_order_item_id')->nullable()->index()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->index()->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->index()->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 15, 4);
            $table->decimal('unit_cost', 25, 4);
            $table->decimal('total_cost', 25, 4);
            $table->string('type', 20)->default('sale');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['product_id', 'store_id', 'purchase_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_allocations');
    }
};
