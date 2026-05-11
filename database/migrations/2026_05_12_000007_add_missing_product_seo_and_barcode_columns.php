<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds product columns that ProductRequest validates and SaveProduct writes
 * but that no previous migration ever created.
 *
 *  - symbology   : barcode type (CODE128 / CODE39 / EAN8 / EAN13 / UPC)
 *  - title       : SEO <title> tag override, max 60 chars
 *  - keywords    : SEO meta keywords
 *  - noindex     : tell search engines not to index this product page
 *  - nofollow    : tell search engines not to follow links on this product page
 *  - file        : path to downloadable file for Digital product type
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'symbology')) {
                $table->string('symbology')->nullable()->default('CODE128')->after('code');
            }
            if (! Schema::hasColumn('products', 'title')) {
                $table->string('title', 60)->nullable()->after('slug');
            }
            if (! Schema::hasColumn('products', 'keywords')) {
                $table->string('keywords', 255)->nullable()->after('title');
            }
            if (! Schema::hasColumn('products', 'noindex')) {
                $table->boolean('noindex')->default(0)->after('keywords');
            }
            if (! Schema::hasColumn('products', 'nofollow')) {
                $table->boolean('nofollow')->default(0)->after('noindex');
            }
            if (! Schema::hasColumn('products', 'file')) {
                $table->string('file')->nullable()->after('nofollow');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['symbology', 'title', 'keywords', 'noindex', 'nofollow', 'file']);
        });
    }
};
