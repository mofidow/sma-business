<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── shop_currencies ───────────────────────────────────────────
        if (! Schema::hasTable('shop_currencies')) {
            Schema::create('shop_currencies', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('currency_id')->nullable();
                $t->decimal('exchange_rate', 15, 6)->default(1);
                $t->boolean('show_at_end')->default(0);
                $t->boolean('auto_update')->default(0);
                $t->timestamps();
            });
        }

        // ── shop_coupons ──────────────────────────────────────────────
        if (! Schema::hasTable('shop_coupons')) {
            Schema::create('shop_coupons', function (Blueprint $t) {
                $t->id();
                $t->string('code')->unique();
                $t->decimal('discount', 15, 4)->default(0);
                $t->boolean('allowed')->default(1);
                $t->boolean('active')->default(1);
                $t->timestamp('expiry_date')->nullable();
                $t->timestamps();
            });
        }

        // ── couponables (morph pivot for ShopCoupon) ──────────────────
        if (! Schema::hasTable('couponables')) {
            Schema::create('couponables', function (Blueprint $t) {
                $t->unsignedBigInteger('shop_coupon_id');
                $t->morphs('couponable');

                $t->foreign('shop_coupon_id')->references('id')->on('shop_coupons')->cascadeOnDelete();
            });
        }

        // ── shop_pages ────────────────────────────────────────────────
        if (! Schema::hasTable('shop_pages')) {
            Schema::create('shop_pages', function (Blueprint $t) {
                $t->id();
                $t->string('slug')->unique();
                $t->string('title');
                $t->text('description')->nullable();
                $t->longText('body')->nullable();
                $t->boolean('active')->default(1);
                $t->timestamps();
            });
        }

        // ── shop_cart_items ───────────────────────────────────────────
        // cart_id is a cookie UUID string, not a FK to a carts table
        if (! Schema::hasTable('shop_cart_items')) {
            Schema::create('shop_cart_items', function (Blueprint $t) {
                $t->id();
                $t->string('cart_id')->nullable()->index();
                $t->unsignedBigInteger('user_id')->nullable();
                $t->unsignedBigInteger('product_id');
                $t->integer('quantity')->default(1);
                $t->json('selected')->nullable();
                $t->string('oId')->nullable();
                $t->timestamps();

                $t->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                $t->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            });
        }

        // ── shop_reviews ──────────────────────────────────────────────
        if (! Schema::hasTable('shop_reviews')) {
            Schema::create('shop_reviews', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('product_id');
                $t->unsignedBigInteger('user_id');
                $t->unsignedTinyInteger('rating')->default(5);
                $t->text('comment')->nullable();
                $t->timestamps();

                $t->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
                $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }

        // ── shop_wishlists ────────────────────────────────────────────
        if (! Schema::hasTable('shop_wishlists')) {
            Schema::create('shop_wishlists', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id');
                $t->unsignedBigInteger('product_id');
                $t->timestamps();

                $t->unique(['user_id', 'product_id']);
                $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $t->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            });
        }

        // ── shop_shipping_methods ─────────────────────────────────────
        if (! Schema::hasTable('shop_shipping_methods')) {
            Schema::create('shop_shipping_methods', function (Blueprint $t) {
                $t->id();
                $t->string('provider_name');
                $t->decimal('rate', 15, 4)->default(0);
                $t->unsignedBigInteger('country_id')->nullable();
                $t->unsignedBigInteger('state_id')->nullable();
                $t->boolean('active')->default(1);
                $t->timestamps();
            });
        }

        // ── shop_recent_views ─────────────────────────────────────────
        // ip_address stored as binary(16) via inet_pton()
        if (! Schema::hasTable('shop_recent_views')) {
            Schema::create('shop_recent_views', function (Blueprint $t) {
                $t->id();
                $t->unsignedBigInteger('user_id')->nullable();
                $t->unsignedBigInteger('product_id');
                $t->binary('ip_address')->nullable();
                $t->timestamps();

                $t->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                $t->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            });
        }

        // ── newsletter_subscribers ────────────────────────────────────
        if (! Schema::hasTable('newsletter_subscribers')) {
            Schema::create('newsletter_subscribers', function (Blueprint $t) {
                $t->id();
                $t->string('email')->unique();
                $t->timestamp('subscribed_at')->nullable();
                $t->timestamps();
            });
        }
    }

    public function down(): void {}
};
