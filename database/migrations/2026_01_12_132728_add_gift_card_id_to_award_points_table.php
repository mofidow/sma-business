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
        Schema::table('award_points', function (Blueprint $table) {
            $table->bigInteger('value')->change();
            $table->unsignedBigInteger('sale_id')->nullable()->change();
            $table->unsignedBigInteger('gift_card_id')->nullable()->after('sale_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('award_points', function (Blueprint $table) {
            $table->dropColumn('gift_card_id');
        });
    }
};
