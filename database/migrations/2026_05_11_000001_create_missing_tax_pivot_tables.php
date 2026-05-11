<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── sale_tax pivot ────────────────────────────────────────────
        if (! Schema::hasTable('sale_tax')) {
            Schema::create('sale_tax', function (Blueprint $t) {
                $t->unsignedBigInteger('sale_id');
                $t->unsignedBigInteger('tax_id');
                $t->primary(['sale_id', 'tax_id']);
                $t->foreign('sale_id')->references('id')->on('sales')->cascadeOnDelete();
                $t->foreign('tax_id')->references('id')->on('taxes')->cascadeOnDelete();
            });
        }

        // ── purchase_tax pivot ────────────────────────────────────────
        if (! Schema::hasTable('purchase_tax')) {
            Schema::create('purchase_tax', function (Blueprint $t) {
                $t->unsignedBigInteger('purchase_id');
                $t->unsignedBigInteger('tax_id');
                $t->primary(['purchase_id', 'tax_id']);
                $t->foreign('purchase_id')->references('id')->on('purchases')->cascadeOnDelete();
                $t->foreign('tax_id')->references('id')->on('taxes')->cascadeOnDelete();
            });
        }

        // ── purchase_item_tax pivot ───────────────────────────────────
        if (! Schema::hasTable('purchase_item_tax')) {
            Schema::create('purchase_item_tax', function (Blueprint $t) {
                $t->unsignedBigInteger('purchase_item_id');
                $t->unsignedBigInteger('tax_id');
                $t->primary(['purchase_item_id', 'tax_id']);
                $t->foreign('purchase_item_id')->references('id')->on('purchase_items')->cascadeOnDelete();
                $t->foreign('tax_id')->references('id')->on('taxes')->cascadeOnDelete();
            });
        }

        // ── quotation_tax pivot ───────────────────────────────────────
        if (! Schema::hasTable('quotation_tax')) {
            Schema::create('quotation_tax', function (Blueprint $t) {
                $t->unsignedBigInteger('quotation_id');
                $t->unsignedBigInteger('tax_id');
                $t->primary(['quotation_id', 'tax_id']);
                $t->foreign('quotation_id')->references('id')->on('quotations')->cascadeOnDelete();
                $t->foreign('tax_id')->references('id')->on('taxes')->cascadeOnDelete();
            });
        }

        // ── quotation_item_tax pivot ──────────────────────────────────
        if (! Schema::hasTable('quotation_item_tax')) {
            Schema::create('quotation_item_tax', function (Blueprint $t) {
                $t->unsignedBigInteger('quotation_item_id');
                $t->unsignedBigInteger('tax_id');
                $t->primary(['quotation_item_id', 'tax_id']);
                $t->foreign('quotation_item_id')->references('id')->on('quotation_items')->cascadeOnDelete();
                $t->foreign('tax_id')->references('id')->on('taxes')->cascadeOnDelete();
            });
        }

        // ── return_order_tax pivot ────────────────────────────────────
        if (! Schema::hasTable('return_order_tax')) {
            Schema::create('return_order_tax', function (Blueprint $t) {
                $t->unsignedBigInteger('return_order_id');
                $t->unsignedBigInteger('tax_id');
                $t->primary(['return_order_id', 'tax_id']);
                $t->foreign('return_order_id')->references('id')->on('return_orders')->cascadeOnDelete();
                $t->foreign('tax_id')->references('id')->on('taxes')->cascadeOnDelete();
            });
        }

        // ── return_order_item_tax pivot ───────────────────────────────
        if (! Schema::hasTable('return_order_item_tax')) {
            Schema::create('return_order_item_tax', function (Blueprint $t) {
                $t->unsignedBigInteger('return_order_item_id');
                $t->unsignedBigInteger('tax_id');
                $t->primary(['return_order_item_id', 'tax_id']);
                $t->foreign('return_order_item_id')->references('id')->on('return_order_items')->cascadeOnDelete();
                $t->foreign('tax_id')->references('id')->on('taxes')->cascadeOnDelete();
            });
        }

        // ── payments: add sale_id and purchase_id ─────────────────────
        // PaymentRequest submits these; PaymentObserver uses them to link payments to orders
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $t) {
                if (! Schema::hasColumn('payments', 'sale_id')) {
                    $t->unsignedBigInteger('sale_id')->nullable();
                }
                if (! Schema::hasColumn('payments', 'purchase_id')) {
                    $t->unsignedBigInteger('purchase_id')->nullable();
                }
            });
        }

        // ── sales: add order_number (links POS order to sales invoice) ─
        if (Schema::hasTable('sales')) {
            Schema::table('sales', function (Blueprint $t) {
                if (! Schema::hasColumn('sales', 'order_number')) {
                    $t->string('order_number')->nullable();
                }
            });
        }
    }

    public function down(): void {}
};
