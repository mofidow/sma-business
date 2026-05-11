<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds columns that the order Request classes validate and Save actions write,
 * but that the base schema never created.
 *
 *  sales:
 *    - details           : free-text note for the whole sale
 *    - order_number      : FK to orders.number (Sale::order() uses this as the local key)
 *    - order_reference   : POS order reference string
 *    - tendered          : cash tendered at POS
 *    - change_returned   : change given back at POS
 *    - payment_method    : POS payment method label
 *    - shipping          : shop shipping cost added to grand_total
 *    - shop_coupon_id    : FK to shop_coupons applied at checkout
 *    - shop_shipping_method_id : FK to shop_shipping_methods chosen at checkout
 *
 *  purchases, quotations, return_orders:
 *    - details           : free-text note (PurchaseRequest / QuotationRequest / ReturnOrderRequest validate it)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── sales ───────────────────────────────────────────────────────
        if (Schema::hasTable('sales')) {
            Schema::table('sales', function (Blueprint $t) {
                if (! Schema::hasColumn('sales', 'details')) {
                    $t->text('details')->nullable()->after('shop');
                }
                if (! Schema::hasColumn('sales', 'order_number')) {
                    $t->string('order_number')->nullable()->index()->after('details');
                }
                if (! Schema::hasColumn('sales', 'order_reference')) {
                    $t->string('order_reference')->nullable()->after('order_number');
                }
                if (! Schema::hasColumn('sales', 'tendered')) {
                    $t->decimal('tendered', 25, 4)->nullable()->after('order_reference');
                }
                if (! Schema::hasColumn('sales', 'change_returned')) {
                    $t->decimal('change_returned', 25, 4)->nullable()->after('tendered');
                }
                if (! Schema::hasColumn('sales', 'payment_method')) {
                    $t->string('payment_method')->nullable()->after('change_returned');
                }
                if (! Schema::hasColumn('sales', 'shipping')) {
                    $t->decimal('shipping', 25, 4)->nullable()->default(0)->after('payment_method');
                }
                if (! Schema::hasColumn('sales', 'shop_coupon_id')) {
                    $t->unsignedBigInteger('shop_coupon_id')->nullable()->after('shipping');
                }
                if (! Schema::hasColumn('sales', 'shop_shipping_method_id')) {
                    $t->unsignedBigInteger('shop_shipping_method_id')->nullable()->after('shop_coupon_id');
                }
            });
        }

        // ── purchases ───────────────────────────────────────────────────
        if (Schema::hasTable('purchases')) {
            Schema::table('purchases', function (Blueprint $t) {
                if (! Schema::hasColumn('purchases', 'details')) {
                    $t->text('details')->nullable();
                }
            });
        }

        // ── quotations ──────────────────────────────────────────────────
        if (Schema::hasTable('quotations')) {
            Schema::table('quotations', function (Blueprint $t) {
                if (! Schema::hasColumn('quotations', 'details')) {
                    $t->text('details')->nullable();
                }
            });
        }

        // ── return_orders ───────────────────────────────────────────────
        if (Schema::hasTable('return_orders')) {
            Schema::table('return_orders', function (Blueprint $t) {
                if (! Schema::hasColumn('return_orders', 'details')) {
                    $t->text('details')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('sales')) {
            Schema::table('sales', function (Blueprint $t) {
                $t->dropColumn([
                    'details', 'order_number', 'order_reference',
                    'tendered', 'change_returned', 'payment_method',
                    'shipping', 'shop_coupon_id', 'shop_shipping_method_id',
                ]);
            });
        }
        foreach (['purchases', 'quotations', 'return_orders'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'details')) {
                Schema::table($table, fn (Blueprint $t) => $t->dropColumn('details'));
            }
        }
    }
};
