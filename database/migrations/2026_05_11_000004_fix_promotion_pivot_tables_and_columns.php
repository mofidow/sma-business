<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fix promotions table columns: app uses 'discount' and 'discount_method',
        // but base schema created 'discount_value' and 'discount_type'
        Schema::table('promotions', function (Blueprint $table) {
            if (! Schema::hasColumn('promotions', 'discount')) {
                // If old column exists, copy data then rename; otherwise just add
                if (Schema::hasColumn('promotions', 'discount_value')) {
                    $table->decimal('discount', 25, 4)->nullable()->after('discount_value');
                } else {
                    $table->decimal('discount', 25, 4)->nullable();
                }
            }
            if (! Schema::hasColumn('promotions', 'discount_method')) {
                if (Schema::hasColumn('promotions', 'discount_type')) {
                    $table->string('discount_method')->nullable()->after('discount');
                } else {
                    $table->string('discount_method')->nullable();
                }
            }
            if (! Schema::hasColumn('promotions', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        // Copy old column data into correctly-named columns (if old columns existed)
        if (Schema::hasColumn('promotions', 'discount_value')) {
            DB::statement('UPDATE promotions SET discount = discount_value WHERE discount IS NULL AND discount_value IS NOT NULL');
        }
        if (Schema::hasColumn('promotions', 'discount_type')) {
            DB::statement('UPDATE promotions SET discount_method = discount_type WHERE discount_method IS NULL AND discount_type IS NOT NULL');
        }

        // Fix pivot table names: Laravel auto-generates alphabetical order.
        // Promotion::products() => belongsToMany(Product::class) => 'product_promotion'
        // but base schema created 'promotion_product'
        if (! Schema::hasTable('product_promotion')) {
            Schema::create('product_promotion', function (Blueprint $table) {
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('promotion_id');
                $table->primary(['product_id', 'promotion_id']);
            });

            // Copy any existing rows from the old table
            if (Schema::hasTable('promotion_product')) {
                DB::statement('INSERT IGNORE INTO product_promotion (product_id, promotion_id) SELECT product_id, promotion_id FROM promotion_product');
            }
        }

        // Promotion::categories() => belongsToMany(Category::class) => 'category_promotion'
        // but base schema created 'promotion_category'
        if (! Schema::hasTable('category_promotion')) {
            Schema::create('category_promotion', function (Blueprint $table) {
                $table->unsignedBigInteger('category_id');
                $table->unsignedBigInteger('promotion_id');
                $table->primary(['category_id', 'promotion_id']);
            });

            if (Schema::hasTable('promotion_category')) {
                DB::statement('INSERT IGNORE INTO category_promotion (category_id, promotion_id) SELECT category_id, promotion_id FROM promotion_category');
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_promotion');
        Schema::dropIfExists('category_promotion');

        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn(array_filter([
                Schema::hasColumn('promotions', 'discount') ? 'discount' : null,
                Schema::hasColumn('promotions', 'discount_method') ? 'discount_method' : null,
                Schema::hasColumn('promotions', 'deleted_at') ? 'deleted_at' : null,
            ]));
        });
    }
};
