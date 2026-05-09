<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('account_transactions')) {
            return;
        }
        Schema::create('account_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->nullable();
            $table->foreignId('account_id')->constrained();
            $table->string('type');
            $table->decimal('amount', 25, 4);
            $table->text('note')->nullable();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_transactions');
    }
};
