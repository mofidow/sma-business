<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Creates every pivot table that Laravel's belongsToMany auto-naming convention
 * expects but that was never created (or was created with the wrong name).
 *
 * Each block is fully idempotent — safe to run on any existing DB.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. product_store ─────────────────────────────────────────────
        // Product::stores() expects 'product_store'; base schema created 'product_stores'
        if (! Schema::hasTable('product_store')) {
            if (Schema::hasTable('product_stores')) {
                // Rename the existing table so all existing data is preserved
                Schema::rename('product_stores', 'product_store');
            } else {
                Schema::create('product_store', function (Blueprint $t) {
                    $t->id();
                    $t->unsignedBigInteger('product_id');
                    $t->unsignedBigInteger('store_id');
                    $t->decimal('price', 25, 4)->nullable()->default(0);
                    $t->decimal('quantity', 25, 4)->nullable()->default(0);
                    $t->decimal('alert_quantity', 25, 4)->nullable();
                    $t->json('taxes')->nullable();
                    $t->json('extra_attributes')->nullable();
                    $t->timestamps();
                    $t->unique(['product_id', 'store_id']);
                });
            }
        }

        // ── 2. price_group_product ───────────────────────────────────────
        // Product::priceGroups() via GroupPrice trait → PriceGroup + Product → price_group_product
        if (! Schema::hasTable('price_group_product')) {
            Schema::create('price_group_product', function (Blueprint $t) {
                $t->unsignedBigInteger('price_group_id');
                $t->unsignedBigInteger('product_id');
                $t->decimal('price', 25, 4)->nullable();
                $t->json('taxes')->nullable();
                $t->primary(['price_group_id', 'product_id']);
            });
        }

        // ── 3. price_group_variation ─────────────────────────────────────
        // Variation::priceGroups() → PriceGroup + Variation → price_group_variation
        if (! Schema::hasTable('price_group_variation')) {
            Schema::create('price_group_variation', function (Blueprint $t) {
                $t->unsignedBigInteger('price_group_id');
                $t->unsignedBigInteger('variation_id');
                $t->decimal('price', 25, 4)->nullable();
                $t->json('taxes')->nullable();
                $t->primary(['price_group_id', 'variation_id']);
            });
        }

        // ── 4. sale_item_variation ───────────────────────────────────────
        // SaleItem::variations() using ItemVariation → SaleItem + Variation → sale_item_variation
        if (! Schema::hasTable('sale_item_variation')) {
            Schema::create('sale_item_variation', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('sale_item_id');
                $t->unsignedBigInteger('variation_id');
                $t->unsignedBigInteger('unit_id')->nullable();
                $t->decimal('quantity', 25, 4)->default(0);
                $t->decimal('base_quantity', 25, 4)->default(0);
                $t->decimal('price', 25, 4)->default(0);
                $t->decimal('net_price', 25, 4)->default(0);
                $t->decimal('unit_price', 25, 4)->default(0);
                $t->decimal('cost', 25, 4)->default(0);
                $t->decimal('total_cost', 25, 4)->default(0);
                $t->decimal('discount', 25, 4)->default(0);
                $t->decimal('discount_amount', 25, 4)->default(0);
                $t->decimal('total_discount_amount', 25, 4)->default(0);
                $t->json('taxes')->nullable();
                $t->decimal('tax_amount', 25, 4)->default(0);
                $t->decimal('total_tax_amount', 25, 4)->default(0);
                $t->decimal('subtotal', 25, 4)->default(0);
                $t->decimal('total', 25, 4)->default(0);
                $t->timestamps();
            });
        }

        // ── 5. sale_item_serial ──────────────────────────────────────────
        // SaleItem::serials() → SaleItem + Serial → "sale_item" < "serial" → sale_item_serial
        if (! Schema::hasTable('sale_item_serial')) {
            Schema::create('sale_item_serial', function (Blueprint $t) {
                $t->unsignedBigInteger('sale_item_id');
                $t->unsignedBigInteger('serial_id');
                $t->primary(['sale_item_id', 'serial_id']);
            });
        }

        // ── 6. purchase_item_variation ───────────────────────────────────
        // PurchaseItem::variations() → PurchaseItem + Variation → purchase_item_variation
        if (! Schema::hasTable('purchase_item_variation')) {
            Schema::create('purchase_item_variation', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('purchase_item_id');
                $t->unsignedBigInteger('variation_id');
                $t->unsignedBigInteger('unit_id')->nullable();
                $t->decimal('quantity', 25, 4)->default(0);
                $t->decimal('base_quantity', 25, 4)->default(0);
                $t->decimal('balance', 25, 4)->default(0);
                $t->decimal('cost', 25, 4)->default(0);
                $t->decimal('net_cost', 25, 4)->default(0);
                $t->decimal('unit_cost', 25, 4)->default(0);
                $t->decimal('discount', 25, 4)->default(0);
                $t->decimal('discount_amount', 25, 4)->default(0);
                $t->decimal('total_discount_amount', 25, 4)->default(0);
                $t->json('taxes')->nullable();
                $t->decimal('tax_amount', 25, 4)->default(0);
                $t->decimal('total_tax_amount', 25, 4)->default(0);
                $t->decimal('subtotal', 25, 4)->default(0);
                $t->decimal('total', 25, 4)->default(0);
                $t->timestamps();
            });
        }

        // ── 7. purchase_item_serial ──────────────────────────────────────
        // PurchaseItem::serials() → "purchase_item" < "serial" → purchase_item_serial
        if (! Schema::hasTable('purchase_item_serial')) {
            Schema::create('purchase_item_serial', function (Blueprint $t) {
                $t->unsignedBigInteger('purchase_item_id');
                $t->unsignedBigInteger('serial_id');
                $t->primary(['purchase_item_id', 'serial_id']);
            });
        }

        // ── 8. quotation_item_variation ──────────────────────────────────
        // QuotationItem::variations() → QuotationItem + Variation → quotation_item_variation
        if (! Schema::hasTable('quotation_item_variation')) {
            Schema::create('quotation_item_variation', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('quotation_item_id');
                $t->unsignedBigInteger('variation_id');
                $t->unsignedBigInteger('unit_id')->nullable();
                $t->decimal('quantity', 25, 4)->default(0);
                $t->decimal('base_quantity', 25, 4)->default(0);
                $t->decimal('price', 25, 4)->default(0);
                $t->decimal('net_price', 25, 4)->default(0);
                $t->decimal('unit_price', 25, 4)->default(0);
                $t->decimal('discount', 25, 4)->default(0);
                $t->decimal('discount_amount', 25, 4)->default(0);
                $t->decimal('total_discount_amount', 25, 4)->default(0);
                $t->json('taxes')->nullable();
                $t->decimal('tax_amount', 25, 4)->default(0);
                $t->decimal('total_tax_amount', 25, 4)->default(0);
                $t->decimal('subtotal', 25, 4)->default(0);
                $t->decimal('total', 25, 4)->default(0);
                $t->timestamps();
            });
        }

        // ── 9. return_order_item_variation ───────────────────────────────
        // ReturnOrderItem::variations() → return_order_item_variation
        if (! Schema::hasTable('return_order_item_variation')) {
            Schema::create('return_order_item_variation', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('return_order_item_id');
                $t->unsignedBigInteger('variation_id');
                $t->unsignedBigInteger('unit_id')->nullable();
                $t->decimal('quantity', 25, 4)->default(0);
                $t->decimal('base_quantity', 25, 4)->default(0);
                $t->decimal('cost', 25, 4)->default(0);
                $t->decimal('net_cost', 25, 4)->default(0);
                $t->decimal('unit_cost', 25, 4)->default(0);
                $t->decimal('price', 25, 4)->default(0);
                $t->decimal('net_price', 25, 4)->default(0);
                $t->decimal('unit_price', 25, 4)->default(0);
                $t->decimal('discount', 25, 4)->default(0);
                $t->decimal('discount_amount', 25, 4)->default(0);
                $t->decimal('total_discount_amount', 25, 4)->default(0);
                $t->json('taxes')->nullable();
                $t->decimal('tax_amount', 25, 4)->default(0);
                $t->decimal('total_tax_amount', 25, 4)->default(0);
                $t->decimal('subtotal', 25, 4)->default(0);
                $t->decimal('total', 25, 4)->default(0);
                $t->timestamps();
            });
        }

        // ── 10. return_order_item_serial ─────────────────────────────────
        if (! Schema::hasTable('return_order_item_serial')) {
            Schema::create('return_order_item_serial', function (Blueprint $t) {
                $t->unsignedBigInteger('return_order_item_id');
                $t->unsignedBigInteger('serial_id');
                $t->primary(['return_order_item_id', 'serial_id']);
            });
        }

        // ── 11. adjustment_item_variation ────────────────────────────────
        // AdjustmentItem::variations() → adjustment_item_variation
        if (! Schema::hasTable('adjustment_item_variation')) {
            Schema::create('adjustment_item_variation', function (Blueprint $t) {
                $t->unsignedBigInteger('adjustment_item_id');
                $t->unsignedBigInteger('variation_id');
                $t->decimal('quantity', 25, 4)->default(0);
                $t->primary(['adjustment_item_id', 'variation_id']);
            });
        }

        // ── 12. adjustment_item_serial ───────────────────────────────────
        if (! Schema::hasTable('adjustment_item_serial')) {
            Schema::create('adjustment_item_serial', function (Blueprint $t) {
                $t->unsignedBigInteger('adjustment_item_id');
                $t->unsignedBigInteger('serial_id');
                $t->primary(['adjustment_item_id', 'serial_id']);
            });
        }

        // ── 13. serial_transfer_item ─────────────────────────────────────
        // TransferItem::serials() → Serial + TransferItem → "serial" < "transfer_item" → serial_transfer_item
        if (! Schema::hasTable('serial_transfer_item')) {
            Schema::create('serial_transfer_item', function (Blueprint $t) {
                $t->unsignedBigInteger('serial_id');
                $t->unsignedBigInteger('transfer_item_id');
                $t->primary(['serial_id', 'transfer_item_id']);
            });
        }

        // ── 14. transfer_item_variation ──────────────────────────────────
        if (! Schema::hasTable('transfer_item_variation')) {
            Schema::create('transfer_item_variation', function (Blueprint $t) {
                $t->unsignedBigInteger('transfer_item_id');
                $t->unsignedBigInteger('variation_id');
                $t->decimal('quantity', 25, 4)->default(0);
                $t->primary(['transfer_item_id', 'variation_id']);
            });
        }
    }

    public function down(): void
    {
        // Only drop tables that we created (not product_store which was renamed from product_stores)
        $tables = [
            'price_group_product', 'price_group_variation',
            'sale_item_variation', 'sale_item_serial',
            'purchase_item_variation', 'purchase_item_serial',
            'quotation_item_variation',
            'return_order_item_variation', 'return_order_item_serial',
            'adjustment_item_variation', 'adjustment_item_serial',
            'serial_transfer_item', 'transfer_item_variation',
        ];
        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }
};
