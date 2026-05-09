<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('credit_installments')) {
            return;
        }
        Schema::create('credit_installments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->nullable();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('store_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 22, 4)->default(0);
            $table->decimal('paid_amount', 22, 4)->default(0);
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->json('extra_attributes')->nullable();
            $table->string('hash')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('sale_id');
            $table->index('customer_id');
            $table->index('due_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_installments');
    }
};
