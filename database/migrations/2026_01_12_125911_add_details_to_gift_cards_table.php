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
        Schema::table('gift_cards', function (Blueprint $table) {
            $table->string('details')->nullable();
            $table->boolean('use_award_points')->nullable();
            $table->unsignedInteger('award_points')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gift_cards', function (Blueprint $table) {
            $table->dropColumn(['details', 'use_award_points', 'award_points']);
        });
    }
};
