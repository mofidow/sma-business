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
        Schema::table('orders', function (Blueprint $table) {
            $table->bigInteger('user_id')->unsigned()->nullable()->change();
            $table->bigInteger('customer_id')->unsigned()->nullable()->change();
            $table->bigInteger('register_id')->unsigned()->nullable()->change();
            $table->string('source', 10)->default('pos')->after('number');
            $table->string('customer_name')->nullable()->after('customer_id');
            $table->string('status', 20)->nullable()->after('data');

            $table->index('source');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['source']);
            $table->dropIndex(['status']);
            $table->dropColumn(['source', 'customer_name', 'status']);
            $table->bigInteger('user_id')->unsigned()->nullable(false)->change();
            $table->bigInteger('customer_id')->unsigned()->nullable(false)->change();
            $table->bigInteger('register_id')->unsigned()->nullable(false)->change();
        });
    }
};
