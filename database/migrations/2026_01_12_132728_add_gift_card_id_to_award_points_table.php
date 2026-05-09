<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('award_points', function (Blueprint $table) {
            $table->bigInteger('value')->change();
            $table->unsignedBigInteger('sale_id')->nullable()->change();
            if (!Schema::hasColumn('award_points', 'gift_card_id')) {
                $table->unsignedBigInteger('gift_card_id')->nullable()->after('sale_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('award_points', function (Blueprint $table) {
            $table->dropColumn('gift_card_id');
        });
    }
};
