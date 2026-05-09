<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('return_orders', function (Blueprint $table) {
            $table->decimal('return_payment_amount', 25, 4)->nullable()->after('grand_total');
            $table->string('return_payment_method', 25)->nullable()->after('return_payment_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('return_orders', function (Blueprint $table) {
            $table->dropColumn(['return_payment_amount', 'return_payment_method']);
        });
    }
};
