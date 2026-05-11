<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('products')) {
            return;
        }

        Schema::table('products', function (Blueprint $t) {
            // Shop Product::$selectColumns references these columns
            if (! Schema::hasColumn('products', 'photo')) {
                $t->string('photo')->nullable()->after('slug');
            }
            // selectColumns uses 'video'; migration 000010 added 'video_url' instead
            if (! Schema::hasColumn('products', 'video')) {
                $t->string('video')->nullable()->after('photo');
            }
            if (! Schema::hasColumn('products', 'has_serials')) {
                $t->boolean('has_serials')->default(0)->after('active');
            }
            if (! Schema::hasColumn('products', 'has_variants')) {
                $t->boolean('has_variants')->default(0)->after('has_serials');
            }
        });
    }

    public function down(): void {}
};
