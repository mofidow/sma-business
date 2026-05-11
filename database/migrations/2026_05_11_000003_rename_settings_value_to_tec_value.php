<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        // The base schema named the column 'value'; v4 code queries 'tec_value'.
        // Rename the existing column so all get_settings() / Setting:: calls work.
        if (Schema::hasColumn('settings', 'value') && ! Schema::hasColumn('settings', 'tec_value')) {
            Schema::table('settings', function (Blueprint $t) {
                $t->renameColumn('value', 'tec_value');
            });
        }

        // If somehow both exist (partial migration), drop the old one
        if (Schema::hasColumn('settings', 'value') && Schema::hasColumn('settings', 'tec_value')) {
            Schema::table('settings', function (Blueprint $t) {
                $t->dropColumn('value');
            });
        }

        // If only tec_value is missing (fresh install without any data), add it
        if (! Schema::hasColumn('settings', 'tec_value')) {
            Schema::table('settings', function (Blueprint $t) {
                $t->longText('tec_value')->nullable()->after('tec_key');
            });
        }
    }

    public function down(): void {}
};
