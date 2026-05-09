<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gift_cards', function (Blueprint $table) {
            if (!Schema::hasColumn('gift_cards', 'details')) {
                $table->string('details')->nullable();
            }
            if (!Schema::hasColumn('gift_cards', 'use_award_points')) {
                $table->boolean('use_award_points')->nullable();
            }
            if (!Schema::hasColumn('gift_cards', 'award_points')) {
                $table->unsignedInteger('award_points')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('gift_cards', function (Blueprint $table) {
            $table->dropColumn(['details', 'use_award_points', 'award_points']);
        });
    }
};
