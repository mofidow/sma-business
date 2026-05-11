<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── categories ────────────────────────────────────────────────
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $t) {
                if (! Schema::hasColumn('categories', 'photo')) $t->string('photo')->nullable()->after('slug');
                if (! Schema::hasColumn('categories', 'title')) $t->string('title')->nullable()->after('description');
                if (! Schema::hasColumn('categories', 'order')) $t->unsignedInteger('order')->default(0)->after('active');
            });
        }

        // ── brands ────────────────────────────────────────────────────
        if (Schema::hasTable('brands')) {
            Schema::table('brands', function (Blueprint $t) {
                if (! Schema::hasColumn('brands', 'photo')) $t->string('photo')->nullable()->after('slug');
                if (! Schema::hasColumn('brands', 'title')) $t->string('title')->nullable()->after('description');
                if (! Schema::hasColumn('brands', 'order')) $t->unsignedInteger('order')->default(0)->after('active');
            });
        }

        // ── stores: columns added in v4 but missing from base schema ──
        if (Schema::hasTable('stores')) {
            Schema::table('stores', function (Blueprint $t) {
                if (! Schema::hasColumn('stores', 'logo'))           $t->string('logo')->nullable();
                if (! Schema::hasColumn('stores', 'color'))          $t->string('color')->nullable();
                if (! Schema::hasColumn('stores', 'header'))         $t->text('header')->nullable();
                if (! Schema::hasColumn('stores', 'footer'))         $t->text('footer')->nullable();
                if (! Schema::hasColumn('stores', 'price_group_id')) $t->unsignedBigInteger('price_group_id')->nullable();
                if (! Schema::hasColumn('stores', 'lot_no'))         $t->string('lot_no')->nullable();
                if (! Schema::hasColumn('stores', 'street'))         $t->string('street')->nullable();
                if (! Schema::hasColumn('stores', 'address_line_1')) $t->string('address_line_1')->nullable();
                if (! Schema::hasColumn('stores', 'address_line_2')) $t->string('address_line_2')->nullable();
                if (! Schema::hasColumn('stores', 'city'))           $t->string('city')->nullable();
                if (! Schema::hasColumn('stores', 'postal_code'))    $t->string('postal_code')->nullable();
                if (! Schema::hasColumn('stores', 'deleted_at'))     $t->softDeletes();
            });
        }

        // ── customers: address fields stored on customer record ───────
        if (Schema::hasTable('customers')) {
            Schema::table('customers', function (Blueprint $t) {
                if (! Schema::hasColumn('customers', 'lot_no'))         $t->string('lot_no')->nullable();
                if (! Schema::hasColumn('customers', 'street'))         $t->string('street')->nullable();
                if (! Schema::hasColumn('customers', 'address_line_1')) $t->string('address_line_1')->nullable();
                if (! Schema::hasColumn('customers', 'address_line_2')) $t->string('address_line_2')->nullable();
                if (! Schema::hasColumn('customers', 'city'))           $t->string('city')->nullable();
                if (! Schema::hasColumn('customers', 'postal_code'))    $t->string('postal_code')->nullable();
                if (! Schema::hasColumn('customers', 'photo'))          $t->string('photo')->nullable();
            });
        }

        // ── suppliers: same address fields ────────────────────────────
        if (Schema::hasTable('suppliers')) {
            Schema::table('suppliers', function (Blueprint $t) {
                if (! Schema::hasColumn('suppliers', 'lot_no'))         $t->string('lot_no')->nullable();
                if (! Schema::hasColumn('suppliers', 'street'))         $t->string('street')->nullable();
                if (! Schema::hasColumn('suppliers', 'address_line_1')) $t->string('address_line_1')->nullable();
                if (! Schema::hasColumn('suppliers', 'address_line_2')) $t->string('address_line_2')->nullable();
                if (! Schema::hasColumn('suppliers', 'city'))           $t->string('city')->nullable();
                if (! Schema::hasColumn('suppliers', 'postal_code'))    $t->string('postal_code')->nullable();
                if (! Schema::hasColumn('suppliers', 'photo'))          $t->string('photo')->nullable();
            });
        }

        // ── products: slug used by shop module ────────────────────────
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $t) {
                if (! Schema::hasColumn('products', 'slug')) {
                    $t->string('slug')->nullable()->unique();
                }
            });
        }
    }

    public function down(): void {}
};
