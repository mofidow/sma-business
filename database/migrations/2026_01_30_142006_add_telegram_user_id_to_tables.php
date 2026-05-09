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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('telegram_user_id')->nullable();
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('telegram_user_id')->nullable();
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->string('telegram_user_id')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('telegram_user_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('telegram_user_id');
        });

        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('telegram_user_id');
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->dropColumn('telegram_user_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('telegram_user_id');
        });
    }
};
