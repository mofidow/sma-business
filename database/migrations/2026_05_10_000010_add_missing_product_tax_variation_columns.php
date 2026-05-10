<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // ── taxes ─────────────────────────────────────────────────────
        // Base schema used 'percentage'; v4 code queries 'rate'
        if (Schema::hasTable('taxes')) {
            if (! Schema::hasColumn('taxes', 'rate')) {
                Schema::table('taxes', function (Blueprint $t) {
                    $t->decimal('rate', 8, 4)->default(0)->after('name');
                });
                // Copy existing percentage values into rate
                DB::statement('UPDATE taxes SET rate = percentage WHERE rate = 0');
            }
            Schema::table('taxes', function (Blueprint $t) {
                if (! Schema::hasColumn('taxes', 'type'))        $t->string('type')->nullable()->after('rate');
                if (! Schema::hasColumn('taxes', 'same'))        $t->boolean('same')->default(0)->after('type');
                if (! Schema::hasColumn('taxes', 'state'))       $t->boolean('state')->default(0)->after('same');
                if (! Schema::hasColumn('taxes', 'compound'))    $t->boolean('compound')->default(0)->after('state');
                if (! Schema::hasColumn('taxes', 'recoverable')) $t->boolean('recoverable')->default(0)->after('compound');
                if (! Schema::hasColumn('taxes', 'number'))      $t->string('number')->nullable()->after('recoverable');
                if (! Schema::hasColumn('taxes', 'details'))     $t->text('details')->nullable()->after('number');
            });
        }

        // ── products ──────────────────────────────────────────────────
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $t) {
                if (! Schema::hasColumn('products', 'price'))            $t->decimal('price', 25, 4)->nullable()->default(0)->after('cost');
                if (! Schema::hasColumn('products', 'featured'))         $t->boolean('featured')->default(0);
                if (! Schema::hasColumn('products', 'secondary_name'))   $t->string('secondary_name')->nullable();
                if (! Schema::hasColumn('products', 'sale_unit_id'))     $t->unsignedBigInteger('sale_unit_id')->nullable();
                if (! Schema::hasColumn('products', 'purchase_unit_id')) $t->unsignedBigInteger('purchase_unit_id')->nullable();
                if (! Schema::hasColumn('products', 'tax_id'))           $t->unsignedBigInteger('tax_id')->nullable();
                if (! Schema::hasColumn('products', 'tax_method'))       $t->string('tax_method')->nullable()->default('Exclusive');
                if (! Schema::hasColumn('products', 'tax_included'))     $t->boolean('tax_included')->default(0);
                if (! Schema::hasColumn('products', 'min_price'))        $t->decimal('min_price', 25, 4)->nullable();
                if (! Schema::hasColumn('products', 'max_price'))        $t->decimal('max_price', 25, 4)->nullable();
                if (! Schema::hasColumn('products', 'max_discount'))     $t->decimal('max_discount', 8, 4)->nullable();
                if (! Schema::hasColumn('products', 'alert_quantity'))   $t->decimal('alert_quantity', 25, 4)->nullable();
                if (! Schema::hasColumn('products', 'hide_in_pos'))      $t->boolean('hide_in_pos')->default(0);
                if (! Schema::hasColumn('products', 'hide_in_shop'))     $t->boolean('hide_in_shop')->default(0);
                if (! Schema::hasColumn('products', 'can_edit_price'))   $t->boolean('can_edit_price')->default(0);
                if (! Schema::hasColumn('products', 'has_expiry_date'))  $t->boolean('has_expiry_date')->default(0);
                if (! Schema::hasColumn('products', 'weight'))           $t->decimal('weight', 10, 4)->nullable();
                if (! Schema::hasColumn('products', 'dimensions'))       $t->string('dimensions')->nullable();
                if (! Schema::hasColumn('products', 'rack_location'))    $t->string('rack_location')->nullable();
                if (! Schema::hasColumn('products', 'supplier_part_id')) $t->string('supplier_part_id')->nullable();
                if (! Schema::hasColumn('products', 'features'))         $t->text('features')->nullable();
                if (! Schema::hasColumn('products', 'details'))          $t->text('details')->nullable();
                if (! Schema::hasColumn('products', 'video_url'))        $t->string('video_url')->nullable();
                if (! Schema::hasColumn('products', 'hsn_number'))       $t->string('hsn_number')->nullable();
                if (! Schema::hasColumn('products', 'sac_number'))       $t->string('sac_number')->nullable();
            });
        }

        // ── variations ────────────────────────────────────────────────
        if (Schema::hasTable('variations')) {
            Schema::table('variations', function (Blueprint $t) {
                if (! Schema::hasColumn('variations', 'code'))       $t->string('code')->nullable();
                if (! Schema::hasColumn('variations', 'cost'))       $t->decimal('cost', 25, 4)->nullable()->default(0);
                if (! Schema::hasColumn('variations', 'price'))      $t->decimal('price', 25, 4)->nullable()->default(0);
                if (! Schema::hasColumn('variations', 'weight'))     $t->decimal('weight', 10, 4)->nullable();
                if (! Schema::hasColumn('variations', 'dimensions')) $t->string('dimensions')->nullable();
            });
        }
    }

    public function down(): void {}
};
