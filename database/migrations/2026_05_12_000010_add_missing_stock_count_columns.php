<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * StockCount model casts brands, categories, completed_at, adjusted_at as
 * DB columns, and StockCountRequest validates type, details, file — but none
 * of these existed in the base stock_counts schema.
 *
 * Also fixes repair_orders: tax_amount and tax_included are validated by
 * RepairOrderRequest and filled via SaveRepairOrder, but were never created.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('stock_counts')) {
            Schema::table('stock_counts', function (Blueprint $t) {
                if (! Schema::hasColumn('stock_counts', 'type')) {
                    $t->string('type')->default('full')->after('reference');
                }
                if (! Schema::hasColumn('stock_counts', 'details')) {
                    $t->text('details')->nullable()->after('notes');
                }
                if (! Schema::hasColumn('stock_counts', 'brands')) {
                    $t->json('brands')->nullable();
                }
                if (! Schema::hasColumn('stock_counts', 'categories')) {
                    $t->json('categories')->nullable();
                }
                if (! Schema::hasColumn('stock_counts', 'file')) {
                    $t->string('file')->nullable();
                }
                if (! Schema::hasColumn('stock_counts', 'completed_at')) {
                    $t->timestamp('completed_at')->nullable();
                }
                if (! Schema::hasColumn('stock_counts', 'adjusted_at')) {
                    $t->timestamp('adjusted_at')->nullable();
                }
            });
        }

        // ── repair_orders ───────────────────────────────────────────────
        if (Schema::hasTable('repair_orders')) {
            Schema::table('repair_orders', function (Blueprint $t) {
                if (! Schema::hasColumn('repair_orders', 'tax_amount')) {
                    $t->decimal('tax_amount', 25, 4)->nullable()->default(0);
                }
                if (! Schema::hasColumn('repair_orders', 'tax_included')) {
                    $t->boolean('tax_included')->nullable()->default(0);
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('stock_counts')) {
            Schema::table('stock_counts', function (Blueprint $t) {
                $t->dropColumn(['type', 'details', 'brands', 'categories', 'file', 'completed_at', 'adjusted_at']);
            });
        }
        if (Schema::hasTable('repair_orders')) {
            Schema::table('repair_orders', function (Blueprint $t) {
                $t->dropColumn(['tax_amount', 'tax_included']);
            });
        }
    }
};
