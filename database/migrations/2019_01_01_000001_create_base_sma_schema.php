<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the original SMA tables that existed before the 2025+ ALTER migrations.
 * Every table uses Schema::hasTable() guards so re-runs are safe.
 *
 * Tables deliberately NOT created here (each has its own CREATE migration):
 * promotion_store, halls, tables, recipes, technicians, service_types,
 * repair_orders, repair_order_attachments, repair_order_tax,
 * account_types, account_transfers, asset_categories, assets,
 * asset_maintenances, asset_allocations, incomes, account_transactions,
 * employees, leave_types, leaves, attendances, payrolls, payslips,
 * payslip_items, claims, cost_allocations, credit_installments
 *
 * Columns deliberately NOT included (each is added by its own ALTER migration):
 * hash (added 2025-10-29), vat_no (2025-11-20), telegram_user_id (2026-01-30),
 * references/account_id/account_type_id (2026-01-29/02-21/02-20),
 * hall_id/table_id/reference_number/guests/notes in sales (2026-02-11),
 * type in products changed to enum (2026-02-11),
 * signature/signed_by_name/signed_at in quotations (2026-04-09),
 * return_payment_amount/return_payment_method in return_orders (2026-04-04),
 * is_credit/credit_status in sales (2026-05-09),
 * gift_card details/use_award_points/award_points (2026-01-12),
 * gift_card_id in award_points (2026-01-12)
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Spatie Permissions ────────────────────────────────────────
        if (!Schema::hasTable('permissions')) {
            Schema::create('permissions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('guard_name')->default('web');
                $table->timestamps();
                $table->unique(['name', 'guard_name']);
            });
        }

        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->string('guard_name')->default('web');
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['name', 'guard_name']);
            });
        }

        if (!Schema::hasTable('model_has_permissions')) {
            Schema::create('model_has_permissions', function (Blueprint $table) {
                $table->unsignedBigInteger('permission_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->index(['model_id', 'model_type']);
                $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
                $table->primary(['permission_id', 'model_id', 'model_type']);
            });
        }

        if (!Schema::hasTable('model_has_roles')) {
            Schema::create('model_has_roles', function (Blueprint $table) {
                $table->unsignedBigInteger('role_id');
                $table->string('model_type');
                $table->unsignedBigInteger('model_id');
                $table->index(['model_id', 'model_type']);
                $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
                $table->primary(['role_id', 'model_id', 'model_type']);
            });
        }

        if (!Schema::hasTable('role_has_permissions')) {
            Schema::create('role_has_permissions', function (Blueprint $table) {
                $table->unsignedBigInteger('permission_id');
                $table->unsignedBigInteger('role_id');
                $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
                $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
                $table->primary(['permission_id', 'role_id']);
            });
        }

        // ── Core Laravel tables ───────────────────────────────────────
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id')->nullable();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedBigInteger('supplier_id')->nullable();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->string('phone')->nullable();
                $table->string('username')->nullable()->unique();
                $table->string('locale')->nullable();
                $table->boolean('employee')->nullable()->default(0);
                $table->boolean('active')->default(1);
                $table->json('bulk_actions')->nullable();
                $table->boolean('can_be_impersonated')->nullable()->default(0);
                $table->string('two_factor_secret')->nullable();
                $table->text('two_factor_recovery_codes')->nullable();
                $table->string('profile_photo_path')->nullable();
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }

        if (!Schema::hasTable('jobs')) {
            Schema::create('jobs', function (Blueprint $table) {
                $table->id();
                $table->string('queue')->index();
                $table->longText('payload');
                $table->unsignedTinyInteger('attempts');
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
        }

        if (!Schema::hasTable('failed_jobs')) {
            Schema::create('failed_jobs', function (Blueprint $table) {
                $table->id();
                $table->string('uuid')->unique();
                $table->text('connection');
                $table->text('queue');
                $table->longText('payload');
                $table->longText('exception');
                $table->timestamp('failed_at')->useCurrent();
            });
        }

        if (!Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('type');
                $table->morphs('notifiable');
                $table->text('data');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        // ── Settings ──────────────────────────────────────────────────
        if (!Schema::hasTable('settings')) {
            Schema::create('settings', function (Blueprint $table) {
                $table->string('tec_key')->primary();
                $table->longText('value')->nullable();
                $table->timestamps();
            });
        }

        // ── Stores ────────────────────────────────────────────────────
        // Note: vat_no added by 2025_11_20 migration
        // Note: references added by 2026_01_29 migration
        // Note: telegram_user_id added by 2026_01_30 migration
        if (!Schema::hasTable('stores')) {
            Schema::create('stores', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('account_id')->nullable();
                $table->unsignedBigInteger('country_id')->nullable();
                $table->unsignedBigInteger('state_id')->nullable();
                $table->string('name');
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->boolean('active')->default(1);
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
            });
        }

        // ── Price & Customer Groups ───────────────────────────────────
        if (!Schema::hasTable('price_groups')) {
            Schema::create('price_groups', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('customer_groups')) {
            Schema::create('customer_groups', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('price_group_id')->nullable();
                $table->string('name');
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
            });
        }

        // ── Customers ─────────────────────────────────────────────────
        // Note: vat_no added by 2025_11_20 migration
        // Note: telegram_user_id added by 2026_01_30 migration
        // Note: points column NOT included (removed by 2026_01_12 migration)
        if (!Schema::hasTable('customers')) {
            Schema::create('customers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_group_id')->nullable();
                $table->unsignedBigInteger('price_group_id')->nullable();
                $table->unsignedBigInteger('country_id')->nullable();
                $table->unsignedBigInteger('state_id')->nullable();
                $table->string('name');
                $table->string('company')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->decimal('opening_balance', 25, 4)->default(0);
                $table->decimal('due_limit', 25, 4)->nullable()->default(0);
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ── Suppliers ─────────────────────────────────────────────────
        // Note: vat_no added by 2026_02_01 migration
        // Note: telegram_user_id added by 2026_01_30 migration
        if (!Schema::hasTable('suppliers')) {
            Schema::create('suppliers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('country_id')->nullable();
                $table->unsignedBigInteger('state_id')->nullable();
                $table->string('name');
                $table->string('company')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->decimal('opening_balance', 25, 4)->default(0);
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('addresses')) {
            Schema::create('addresses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('store_id')->nullable();
                $table->unsignedBigInteger('country_id')->nullable();
                $table->unsignedBigInteger('state_id')->nullable();
                $table->string('name');
                $table->string('phone')->nullable();
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->string('zip_code')->nullable();
                $table->string('city')->nullable();
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ── Taxes & Units ─────────────────────────────────────────────
        if (!Schema::hasTable('taxes')) {
            Schema::create('taxes', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->decimal('percentage', 8, 4);
                $table->boolean('active')->default(1);
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('units')) {
            Schema::create('units', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->string('code')->unique();
                $table->string('name');
                $table->decimal('operator_value', 10, 4)->nullable()->default(1);
                $table->string('operator')->nullable();
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
            });
        }

        // ── Accounts ──────────────────────────────────────────────────
        // Note: account_type_id added by 2026_02_20 migration
        // Note: type column changed by 2026_02_20 migration
        if (!Schema::hasTable('accounts')) {
            Schema::create('accounts', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('type')->nullable();
                $table->text('details')->nullable();
                $table->decimal('opening_balance', 25, 4)->default(0);
                $table->boolean('active')->default(1);
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ── Categories & Brands ───────────────────────────────────────
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->boolean('active')->default(1);
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('brands')) {
            Schema::create('brands', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->boolean('active')->default(1);
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ── Products ──────────────────────────────────────────────────
        // Note: type is string here; changed to enum by 2026_02_11 migration
        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->unsignedBigInteger('subcategory_id')->nullable();
                $table->unsignedBigInteger('brand_id')->nullable();
                $table->unsignedBigInteger('supplier_id')->nullable();
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->string('sku')->nullable()->unique();
                $table->string('code')->unique();
                $table->string('barcode')->nullable()->index();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->decimal('cost', 25, 4)->nullable()->default(0);
                $table->string('type')->nullable()->default('Standard');
                $table->boolean('active')->default(1);
                $table->boolean('dont_track_stock')->default(0);
                $table->json('variants')->nullable();
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('variations')) {
            Schema::create('variations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->string('sku')->nullable()->unique();
                $table->string('name')->nullable();
                $table->json('meta')->nullable();
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('product_stores')) {
            Schema::create('product_stores', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('store_id');
                $table->decimal('price', 25, 4)->nullable()->default(0);
                $table->decimal('quantity', 25, 4)->nullable()->default(0);
                $table->decimal('alert_quantity', 25, 4)->nullable();
                $table->json('taxes')->nullable();
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
                $table->unique(['product_id', 'store_id']);
            });
        }

        if (!Schema::hasTable('product_product')) {
            Schema::create('product_product', function (Blueprint $table) {
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('combo_id');
                $table->decimal('quantity', 25, 4)->default(1);
                $table->primary(['product_id', 'combo_id']);
            });
        }

        if (!Schema::hasTable('product_tax')) {
            Schema::create('product_tax', function (Blueprint $table) {
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('tax_id');
                $table->primary(['product_id', 'tax_id']);
            });
        }

        if (!Schema::hasTable('stocks')) {
            Schema::create('stocks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('variation_id')->nullable();
                $table->unsignedBigInteger('store_id');
                $table->decimal('alert_quantity', 25, 4)->nullable();
                $table->timestamps();
                $table->softDeletes();
                $table->unique(['product_id', 'store_id', 'variation_id']);
            });
        }

        if (!Schema::hasTable('serials')) {
            Schema::create('serials', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('purchase_id')->nullable();
                $table->string('number')->unique();
                $table->boolean('sold')->default(0);
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('tracks')) {
            Schema::create('tracks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->unsignedBigInteger('variation_id')->nullable();
                $table->unsignedBigInteger('store_id');
                $table->nullableMorphs('trackable');
                $table->nullableMorphs('reference');
                $table->decimal('value', 25, 4)->default(0);
                $table->text('description')->nullable();
                $table->timestamps();
                $table->index(['product_id', 'store_id']);
            });
        }

        if (!Schema::hasTable('unit_prices')) {
            Schema::create('unit_prices', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('unit_id');
                $table->morphs('priceable');
                $table->decimal('price', 25, 4)->default(0);
                $table->timestamps();
            });
        }

        // ── Stock Counts ──────────────────────────────────────────────
        if (!Schema::hasTable('stock_counts')) {
            Schema::create('stock_counts', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('store_id');
                $table->unsignedBigInteger('user_id');
                $table->string('reference')->nullable()->unique();
                $table->date('date');
                $table->text('notes')->nullable();
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('stock_count_items')) {
            Schema::create('stock_count_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('stock_count_id');
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('variation_id')->nullable();
                $table->decimal('counted_quantity', 25, 4)->default(0);
                $table->decimal('system_quantity', 25, 4)->default(0);
                $table->timestamps();
            });
        }

        // ── POS Registers ─────────────────────────────────────────────
        if (!Schema::hasTable('registers')) {
            Schema::create('registers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('store_id');
                $table->decimal('opened_amount', 25, 4)->nullable();
                $table->decimal('closed_amount', 25, 4)->nullable();
                $table->unsignedBigInteger('closed_by')->nullable();
                $table->timestamp('closed_at')->nullable();
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ── POS Orders (separate from sales) ──────────────────────────
        // Note: hall_id, table_id, reference_number, guests, notes added by 2026_02_11 migration
        // Note: qr_fields added by 2026_02_22 migration
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('store_id');
                $table->unsignedBigInteger('register_id')->nullable();
                $table->string('number')->unique();
                $table->string('customer_name')->nullable();
                $table->string('source')->nullable()->default('pos');
                $table->string('status')->nullable()->default('pending');
                $table->string('reference')->nullable();
                $table->json('data')->nullable();
                $table->string('order_for')->nullable();
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ── Sales ─────────────────────────────────────────────────────
        // Note: hash added by 2025_10_29 migration
        // Note: hall_id, table_id, reference_number, guests, notes added by 2026_02_11 migration
        // Note: is_credit, credit_status added by 2026_05_09 migration
        if (!Schema::hasTable('sales')) {
            Schema::create('sales', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->nullable()->index();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('store_id');
                $table->unsignedBigInteger('register_id')->nullable();
                $table->unsignedBigInteger('address_id')->nullable();
                $table->string('reference')->nullable()->unique();
                $table->date('date')->index();
                $table->date('due_date')->nullable();
                $table->decimal('sub_total', 25, 4)->default(0);
                $table->decimal('total_discount', 25, 4)->default(0);
                $table->decimal('total_tax', 25, 4)->default(0);
                $table->decimal('grand_total', 25, 4)->default(0);
                $table->decimal('rounding', 25, 4)->default(0);
                $table->decimal('paid', 25, 4)->nullable()->default(0);
                $table->boolean('pos')->default(0);
                $table->boolean('shop')->default(0);
                $table->json('extra_attributes')->nullable();
                $table->json('fiscal_service_response')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ── Sale Items ────────────────────────────────────────────────
        // Note: promotions column added by 2025_08_29 migration
        if (!Schema::hasTable('sale_items')) {
            Schema::create('sale_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sale_id');
                $table->unsignedBigInteger('product_id')->nullable();
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->unsignedBigInteger('register_id')->nullable();
                $table->unsignedBigInteger('store_id');
                $table->decimal('quantity', 25, 4)->default(0);
                $table->decimal('base_quantity', 25, 4)->default(0);
                $table->decimal('price', 25, 4)->default(0);
                $table->decimal('net_price', 25, 4)->default(0);
                $table->decimal('unit_price', 25, 4)->default(0);
                $table->decimal('cost', 25, 4)->nullable();
                $table->decimal('total_cost', 25, 4)->nullable();
                $table->decimal('discount', 25, 4)->default(0);
                $table->decimal('discount_amount', 25, 4)->default(0);
                $table->decimal('total_discount_amount', 25, 4)->default(0);
                $table->json('taxes')->nullable();
                $table->decimal('tax_amount', 25, 4)->default(0);
                $table->decimal('total_tax_amount', 25, 4)->default(0);
                $table->decimal('subtotal', 25, 4)->default(0);
                $table->decimal('total', 25, 4)->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sale_item_tax')) {
            Schema::create('sale_item_tax', function (Blueprint $table) {
                $table->unsignedBigInteger('sale_item_id');
                $table->unsignedBigInteger('tax_id');
                $table->primary(['sale_item_id', 'tax_id']);
            });
        }

        // ── Purchases ─────────────────────────────────────────────────
        // Note: hash added by 2025_10_29 migration
        if (!Schema::hasTable('purchases')) {
            Schema::create('purchases', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('supplier_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('store_id');
                $table->unsignedBigInteger('register_id')->nullable();
                $table->string('reference')->nullable()->unique();
                $table->date('date');
                $table->decimal('sub_total', 25, 4)->default(0);
                $table->decimal('total_discount', 25, 4)->default(0);
                $table->decimal('total_tax', 25, 4)->default(0);
                $table->decimal('grand_total', 25, 4)->default(0);
                $table->decimal('paid', 25, 4)->nullable()->default(0);
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('purchase_items')) {
            Schema::create('purchase_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('purchase_id');
                $table->unsignedBigInteger('product_id')->nullable();
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->unsignedBigInteger('store_id');
                $table->decimal('quantity', 25, 4)->default(0);
                $table->decimal('base_quantity', 25, 4)->default(0);
                $table->decimal('balance', 25, 4)->nullable();
                $table->decimal('cost', 25, 4)->default(0);
                $table->decimal('net_cost', 25, 4)->default(0);
                $table->decimal('unit_cost', 25, 4)->default(0);
                $table->decimal('discount', 25, 4)->default(0);
                $table->decimal('discount_amount', 25, 4)->default(0);
                $table->decimal('total_discount_amount', 25, 4)->default(0);
                $table->json('taxes')->nullable();
                $table->decimal('tax_amount', 25, 4)->default(0);
                $table->decimal('total_tax_amount', 25, 4)->default(0);
                $table->decimal('subtotal', 25, 4)->default(0);
                $table->decimal('total', 25, 4)->default(0);
                $table->date('expiry_date')->nullable();
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
            });
        }

        // ── Payments ──────────────────────────────────────────────────
        // Note: hash added by 2025_10_29 migration
        // Note: method column type changed by 2025_08_29 migration
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->nullable()->index();
                $table->unsignedBigInteger('supplier_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('store_id');
                $table->unsignedBigInteger('register_id')->nullable();
                $table->string('reference')->nullable()->unique();
                $table->date('date')->index();
                $table->decimal('amount', 25, 4);
                $table->string('method')->nullable();
                $table->string('payment_for')->nullable();
                $table->boolean('received')->nullable()->default(0);
                $table->json('method_data')->nullable();
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('payables')) {
            Schema::create('payables', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('payment_id');
                $table->morphs('payable');
                $table->decimal('amount', 25, 4)->nullable();
                $table->timestamps();
                $table->index(['payable_id', 'payable_type']);
            });
        }

        // ── Quotations ────────────────────────────────────────────────
        // Note: hash added by 2025_10_29 migration
        // Note: signature, signed_by_name, signed_at added by 2026_04_09 migration
        if (!Schema::hasTable('quotations')) {
            Schema::create('quotations', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('store_id');
                $table->string('reference')->nullable()->unique();
                $table->date('date');
                $table->decimal('sub_total', 25, 4)->default(0);
                $table->decimal('total_discount', 25, 4)->default(0);
                $table->decimal('total_tax', 25, 4)->default(0);
                $table->decimal('grand_total', 25, 4)->default(0);
                $table->timestamp('emailed_at')->nullable();
                $table->timestamp('converted_at')->nullable();
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('quotation_items')) {
            Schema::create('quotation_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('quotation_id');
                $table->unsignedBigInteger('product_id')->nullable();
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->unsignedBigInteger('store_id');
                $table->decimal('quantity', 25, 4)->default(0);
                $table->decimal('base_quantity', 25, 4)->default(0);
                $table->decimal('price', 25, 4)->default(0);
                $table->decimal('net_price', 25, 4)->default(0);
                $table->decimal('unit_price', 25, 4)->default(0);
                $table->decimal('discount', 25, 4)->default(0);
                $table->decimal('discount_amount', 25, 4)->default(0);
                $table->decimal('total_discount_amount', 25, 4)->default(0);
                $table->json('taxes')->nullable();
                $table->decimal('tax_amount', 25, 4)->default(0);
                $table->decimal('total_tax_amount', 25, 4)->default(0);
                $table->decimal('subtotal', 25, 4)->default(0);
                $table->decimal('total', 25, 4)->default(0);
                $table->timestamps();
            });
        }

        // ── Return Orders ─────────────────────────────────────────────
        // Note: hash added by 2025_10_29 migration
        // Note: return_payment_amount, return_payment_method added by 2026_04_04 migration
        if (!Schema::hasTable('return_orders')) {
            Schema::create('return_orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sale_id')->nullable();
                $table->unsignedBigInteger('purchase_id')->nullable();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedBigInteger('supplier_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('store_id');
                $table->unsignedBigInteger('register_id')->nullable();
                $table->string('reference')->nullable()->unique();
                $table->string('type');
                $table->date('date');
                $table->decimal('sub_total', 25, 4)->default(0);
                $table->decimal('total_discount', 25, 4)->default(0);
                $table->decimal('total_tax', 25, 4)->default(0);
                $table->decimal('grand_total', 25, 4)->default(0);
                $table->json('extra_attributes')->nullable();
                $table->json('fiscal_service_response')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('return_order_items')) {
            Schema::create('return_order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('return_order_id');
                $table->unsignedBigInteger('product_id')->nullable();
                $table->unsignedBigInteger('unit_id')->nullable();
                $table->unsignedBigInteger('store_id');
                $table->decimal('quantity', 25, 4)->default(0);
                $table->decimal('base_quantity', 25, 4)->default(0);
                $table->decimal('price', 25, 4)->default(0);
                $table->decimal('net_price', 25, 4)->default(0);
                $table->decimal('unit_price', 25, 4)->default(0);
                $table->decimal('cost', 25, 4)->nullable();
                $table->decimal('net_cost', 25, 4)->nullable();
                $table->decimal('unit_cost', 25, 4)->nullable();
                $table->decimal('discount', 25, 4)->default(0);
                $table->decimal('discount_amount', 25, 4)->default(0);
                $table->decimal('total_discount_amount', 25, 4)->default(0);
                $table->json('taxes')->nullable();
                $table->decimal('tax_amount', 25, 4)->default(0);
                $table->decimal('total_tax_amount', 25, 4)->default(0);
                $table->decimal('subtotal', 25, 4)->default(0);
                $table->decimal('total', 25, 4)->default(0);
                $table->timestamps();
            });
        }

        // ── Deliveries ────────────────────────────────────────────────
        // Note: hash added by 2025_10_29 migration
        if (!Schema::hasTable('deliveries')) {
            Schema::create('deliveries', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sale_id');
                $table->unsignedBigInteger('customer_id');
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('store_id');
                $table->unsignedBigInteger('address_id')->nullable();
                $table->string('reference')->nullable()->unique();
                $table->date('date');
                $table->text('details')->nullable();
                $table->timestamp('delivered_at')->nullable();
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ── Expenses ──────────────────────────────────────────────────
        // Note: hash added by 2025_10_29 migration
        // Note: account_id added by 2026_02_21 migration
        if (!Schema::hasTable('expenses')) {
            Schema::create('expenses', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('supplier_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('store_id');
                $table->unsignedBigInteger('register_id')->nullable();
                $table->string('reference')->nullable()->unique();
                $table->date('date');
                $table->decimal('amount', 25, 4);
                $table->text('details')->nullable();
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ── Stock Transfers ───────────────────────────────────────────
        // Note: hash added by 2025_10_29 migration
        // Note: account_transfers is a SEPARATE table created by 2026_02_20 migration
        if (!Schema::hasTable('transfers')) {
            Schema::create('transfers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('store_id');
                $table->unsignedBigInteger('to_store_id');
                $table->string('reference')->nullable()->unique();
                $table->string('type')->nullable();
                $table->date('date');
                $table->text('details')->nullable();
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('transfer_items')) {
            Schema::create('transfer_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('transfer_id');
                $table->unsignedBigInteger('product_id');
                $table->decimal('quantity', 25, 4)->default(0);
                $table->timestamps();
            });
        }

        // ── Adjustments ───────────────────────────────────────────────
        if (!Schema::hasTable('adjustments')) {
            Schema::create('adjustments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('store_id');
                $table->string('reference')->nullable()->unique();
                $table->string('type')->nullable();
                $table->date('date');
                $table->text('details')->nullable();
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('adjustment_items')) {
            Schema::create('adjustment_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('adjustment_id');
                $table->unsignedBigInteger('product_id');
                $table->unsignedBigInteger('store_id');
                $table->decimal('quantity', 25, 4)->default(0);
                $table->timestamps();
            });
        }

        // ── Gift Cards & Award Points ──────────────────────────────────
        // Note: details, use_award_points, award_points added by 2026_01_12 migration
        if (!Schema::hasTable('gift_cards')) {
            Schema::create('gift_cards', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('store_id');
                $table->string('number')->unique();
                $table->decimal('amount', 25, 4)->default(0);
                $table->date('expiry_date')->nullable();
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // Note: gift_card_id added by 2026_01_12 migration
        if (!Schema::hasTable('award_points')) {
            Schema::create('award_points', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sale_id')->nullable();
                $table->unsignedBigInteger('customer_id');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('store_id');
                $table->integer('value')->default(0);
                $table->text('details')->nullable();
                $table->timestamps();
            });
        }

        // ── Promotions ────────────────────────────────────────────────
        // Note: promotion_store table created by 2026_02_02 migration
        if (!Schema::hasTable('promotions')) {
            Schema::create('promotions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('product_id_to_buy')->nullable();
                $table->unsignedBigInteger('product_id_to_get')->nullable();
                $table->string('name');
                $table->string('type')->default('simple');
                $table->string('code')->nullable();
                $table->text('description')->nullable();
                $table->decimal('discount_value', 15, 4)->nullable();
                $table->string('discount_type')->nullable();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->boolean('active')->default(1);
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('promotion_category')) {
            Schema::create('promotion_category', function (Blueprint $table) {
                $table->unsignedBigInteger('promotion_id');
                $table->unsignedBigInteger('category_id');
                $table->primary(['promotion_id', 'category_id']);
            });
        }

        if (!Schema::hasTable('promotion_product')) {
            Schema::create('promotion_product', function (Blueprint $table) {
                $table->unsignedBigInteger('promotion_id');
                $table->unsignedBigInteger('product_id');
                $table->primary(['promotion_id', 'product_id']);
            });
        }

        // ── Custom Fields ─────────────────────────────────────────────
        if (!Schema::hasTable('custom_fields')) {
            Schema::create('custom_fields', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->string('name');
                $table->string('type')->default('text');
                $table->json('models')->nullable();
                $table->json('options')->nullable();
                $table->integer('order_no')->default(0);
                $table->boolean('required')->default(0);
                $table->boolean('active')->default(1);
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        // ── Activity Log (Spatie) ─────────────────────────────────────
        if (!Schema::hasTable('activity_log')) {
            Schema::create('activity_log', function (Blueprint $table) {
                $table->id();
                $table->string('log_name')->nullable();
                $table->text('description');
                $table->nullableMorphs('subject');
                $table->string('event')->nullable();
                $table->nullableMorphs('causer');
                $table->json('properties')->nullable();
                $table->uuid('batch_uuid')->nullable();
                $table->timestamp('created_at')->nullable()->index();
                $table->timestamp('updated_at')->nullable();
            });
        }

        // ── QR Orders ─────────────────────────────────────────────────
        if (!Schema::hasTable('qr_orders')) {
            Schema::create('qr_orders', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('table_id')->nullable();
                $table->unsignedBigInteger('hall_id')->nullable();
                $table->unsignedBigInteger('store_id');
                $table->string('number')->unique();
                $table->string('customer_name')->nullable();
                $table->string('status')->default('pending');
                $table->json('items')->nullable();
                $table->json('extra_attributes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'qr_orders', 'activity_log', 'custom_fields',
            'promotion_product', 'promotion_category', 'promotions',
            'award_points', 'gift_cards',
            'adjustment_items', 'adjustments', 'transfer_items', 'transfers',
            'expenses', 'deliveries', 'return_order_items', 'return_orders',
            'quotation_items', 'quotations', 'payables', 'payments',
            'purchase_items', 'purchases', 'sale_item_tax', 'sale_items', 'sales',
            'orders', 'registers',
            'stock_count_items', 'stock_counts', 'unit_prices',
            'tracks', 'serials', 'stocks',
            'product_tax', 'product_product', 'product_stores', 'variations', 'products',
            'brands', 'categories', 'accounts',
            'units', 'taxes', 'addresses', 'suppliers', 'customers',
            'customer_groups', 'price_groups', 'stores', 'settings',
            'notifications', 'failed_jobs', 'jobs', 'sessions',
            'password_reset_tokens', 'users',
            'role_has_permissions', 'model_has_roles', 'model_has_permissions',
            'roles', 'permissions',
        ];
        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }
};
