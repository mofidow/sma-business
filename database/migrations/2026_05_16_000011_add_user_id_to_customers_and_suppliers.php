<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('customers') && ! Schema::hasColumn('customers', 'user_id')) {
            Schema::table('customers', function (Blueprint $t) {
                $t->unsignedBigInteger('user_id')->nullable()->after('id');
            });
        }

        if (Schema::hasTable('suppliers') && ! Schema::hasColumn('suppliers', 'user_id')) {
            Schema::table('suppliers', function (Blueprint $t) {
                $t->unsignedBigInteger('user_id')->nullable()->after('id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('customers', 'user_id')) {
            Schema::table('customers', fn (Blueprint $t) => $t->dropColumn('user_id'));
        }
        if (Schema::hasColumn('suppliers', 'user_id')) {
            Schema::table('suppliers', fn (Blueprint $t) => $t->dropColumn('user_id'));
        }
    }
};
