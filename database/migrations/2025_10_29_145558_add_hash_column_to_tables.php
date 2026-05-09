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
        Schema::table('sales', function (Blueprint $table) {
            $table->string('hash')->nullable();
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->string('hash')->nullable();
        });
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('hash')->nullable();
        });
        Schema::table('quotations', function (Blueprint $table) {
            $table->string('hash')->nullable();
        });
        Schema::table('deliveries', function (Blueprint $table) {
            $table->string('hash')->nullable();
        });
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('hash')->nullable();
        });
        Schema::table('return_orders', function (Blueprint $table) {
            $table->string('hash')->nullable();
        });
        Schema::table('transfers', function (Blueprint $table) {
            $table->string('hash')->nullable();
        });
    }
};
