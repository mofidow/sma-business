<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // sale_items: comment field is validated in SaleRequest and passed to create()
        if (Schema::hasTable('sale_items') && ! Schema::hasColumn('sale_items', 'comment')) {
            Schema::table('sale_items', function (Blueprint $t) {
                $t->string('comment', 190)->nullable()->after('price');
            });
        }

        // purchase_items: batch_no validated in PurchaseRequest, not stripped before create()
        if (Schema::hasTable('purchase_items') && ! Schema::hasColumn('purchase_items', 'batch_no')) {
            Schema::table('purchase_items', function (Blueprint $t) {
                $t->string('batch_no')->nullable()->after('expiry_date');
            });
        }

        // return_order_items: both FK fields validated in ReturnOrderRequest, not stripped before create()
        if (Schema::hasTable('return_order_items')) {
            Schema::table('return_order_items', function (Blueprint $t) {
                if (! Schema::hasColumn('return_order_items', 'sale_item_id')) {
                    $t->unsignedBigInteger('sale_item_id')->nullable()->after('return_order_id');
                }
                if (! Schema::hasColumn('return_order_items', 'purchase_item_id')) {
                    $t->unsignedBigInteger('purchase_item_id')->nullable()->after('sale_item_id');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sale_items', 'comment')) {
            Schema::table('sale_items', fn (Blueprint $t) => $t->dropColumn('comment'));
        }
        if (Schema::hasColumn('purchase_items', 'batch_no')) {
            Schema::table('purchase_items', fn (Blueprint $t) => $t->dropColumn('batch_no'));
        }
        if (Schema::hasColumn('return_order_items', 'sale_item_id')) {
            Schema::table('return_order_items', fn (Blueprint $t) => $t->dropColumn('sale_item_id'));
        }
        if (Schema::hasColumn('return_order_items', 'purchase_item_id')) {
            Schema::table('return_order_items', fn (Blueprint $t) => $t->dropColumn('purchase_item_id'));
        }
    }
};
