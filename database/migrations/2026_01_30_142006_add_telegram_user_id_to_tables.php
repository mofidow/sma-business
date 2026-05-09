<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['customers', 'suppliers', 'stores', 'users'] as $t) {
            if (!Schema::hasColumn($t, 'telegram_user_id')) {
                Schema::table($t, function (Blueprint $table) {
                    $table->string('telegram_user_id')->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        foreach (['customers', 'suppliers', 'stores', 'users'] as $t) {
            Schema::table($t, function (Blueprint $table) {
                $table->dropColumn('telegram_user_id');
            });
        }
    }
};
