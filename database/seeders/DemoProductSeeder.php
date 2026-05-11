<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Seeds demo categories, brands, units, and 36 products for Rozana Somalia.
 * Run: php8.4 artisan db:seed --class=DemoProductSeeder
 *
 * Re-runnable: all inserts use slug/code uniqueness checks.
 */
class DemoProductSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        // ── Units ──────────────────────────────────────────────────────────
        $units = [];
        foreach ([
            ['name' => 'Piece',    'code' => 'PC',  'operator' => '*', 'operator_value' => 1],
            ['name' => 'Kilogram', 'code' => 'KG',  'operator' => '*', 'operator_value' => 1],
            ['name' => 'Litre',    'code' => 'L',   'operator' => '*', 'operator_value' => 1],
            ['name' => 'Pack',     'code' => 'PK',  'operator' => '*', 'operator_value' => 1],
            ['name' => 'Bottle',   'code' => 'BTL', 'operator' => '*', 'operator_value' => 1],
            ['name' => 'Box',      'code' => 'BOX', 'operator' => '*', 'operator_value' => 1],
        ] as $u) {
            $existing = DB::table('units')->where('code', $u['code'])->first();
            if (! $existing) {
                $units[$u['code']] = DB::table('units')->insertGetId(array_merge(
                    $u, ['unit_id' => null, 'extra_attributes' => json_encode([]), 'created_at' => $now, 'updated_at' => $now]
                ));
            } else {
                $units[$u['code']] = $existing->id;
            }
        }

        // ── Categories ─────────────────────────────────────────────────────
        $catData = [
            'Electronics', 'Clothing & Apparel', 'Food & Beverages',
            'Home & Kitchen', 'Beauty & Personal Care', 'Sports & Outdoors',
            'Books & Stationery', 'Health & Wellness',
        ];
        $cats = [];
        foreach ($catData as $catName) {
            $slug = Str::slug($catName);
            $row = DB::table('categories')->where('slug', $slug)->first();
            if (! $row) {
                $cats[$catName] = DB::table('categories')->insertGetId([
                    'name'             => $catName,
                    'slug'             => $slug,
                    'active'           => 1,
                    'category_id'      => null,
                    'extra_attributes' => json_encode([]),
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);
            } else {
                $cats[$catName] = $row->id;
            }
        }

        // ── Brands ─────────────────────────────────────────────────────────
        $brandData = [
            'Samsung', 'Apple', 'Huawei', 'Nike', 'Adidas',
            'Nestlé', 'Unilever', 'Rozana', 'Generic', 'Phillips',
        ];
        $brands = [];
        foreach ($brandData as $brandName) {
            $slug = Str::slug($brandName);
            $row = DB::table('brands')->where('slug', $slug)->first();
            if (! $row) {
                $brands[$brandName] = DB::table('brands')->insertGetId([
                    'name'             => $brandName,
                    'slug'             => $slug,
                    'active'           => 1,
                    'extra_attributes' => json_encode([]),
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ]);
            } else {
                $brands[$brandName] = $row->id;
            }
        }

        // ── Store (use first available) ────────────────────────────────────
        $storeId = DB::table('stores')->value('id') ?? 1;

        // ── Product catalog ────────────────────────────────────────────────
        // [name, category, brand, unitCode, cost, price, featured, description]
        $catalog = [
            // Electronics
            ['Samsung Galaxy A15',        'Electronics',          'Samsung',   'PC',  120,   149.99, true,  'Mid-range Android smartphone with large display and long battery life.'],
            ['Samsung 32" LED Smart TV',  'Electronics',          'Samsung',   'PC',  180,   229.99, false, 'Full HD LED smart TV with built-in Wi-Fi and streaming apps.'],
            ['Apple AirPods Pro',         'Electronics',          'Apple',     'PC',  180,   249.99, true,  'Active noise-cancelling wireless earbuds with spatial audio.'],
            ['Huawei FreeBuds 5i',        'Electronics',          'Huawei',    'PC',   60,    89.99, false, 'Wireless earbuds with noise cancellation and long battery life.'],
            ['Phillips LED Bulb 10W',     'Electronics',          'Phillips',  'PC',    2.5,   5.99, false, 'Energy-saving LED bulb, warm white, 10W equivalent to 75W.'],
            ['Phillips Extension Cord 3m','Electronics',          'Phillips',  'PC',    8,    14.99, false, '3-socket extension cord with surge protection.'],

            // Clothing & Apparel
            ['Nike Air Max Sneakers',     'Clothing & Apparel',   'Nike',      'PC',   55,    89.99, true,  'Lightweight running shoes with Air Max cushioning technology.'],
            ['Adidas Classic T-Shirt',    'Clothing & Apparel',   'Adidas',    'PC',   12,    24.99, false, 'Classic cotton sports T-shirt, available in multiple colours.'],
            ['Nike Dri-FIT Training Shorts','Clothing & Apparel', 'Nike',      'PC',   18,    34.99, false, 'Moisture-wicking training shorts for men.'],
            ['Adidas Track Jacket',       'Clothing & Apparel',   'Adidas',    'PC',   28,    54.99, false, 'Slim-fit track jacket with zip pockets.'],
            ['Rozana Hijab Set of 3',     'Clothing & Apparel',   'Rozana',    'PK',    9,    19.99, false, 'Premium cotton hijab set in three colours.'],
            ['Rozana Premium Abaya',      'Clothing & Apparel',   'Rozana',    'PC',   22,    44.99, true,  'Elegant embroidered abaya, multiple sizes available.'],

            // Food & Beverages
            ['Nestlé Milo 400g',          'Food & Beverages',     'Nestlé',    'PC',    3.5,   6.99, false, 'Chocolate malt energy drink, 400g tin.'],
            ['Nescafé Classic 200g',      'Food & Beverages',     'Nestlé',    'PC',    5,     9.99, false, 'Instant coffee granules, rich taste, 200g jar.'],
            ['Basmati Rice 5kg',          'Food & Beverages',     'Generic',   'KG',    5,     8.99, true,  'Premium aged basmati rice, fragrant long grain.'],
            ['Sunflower Cooking Oil 2L',  'Food & Beverages',     'Unilever',  'BTL',   3,     5.99, false, 'Pure sunflower cooking oil, 2-litre bottle.'],
            ['Spaghetti Pasta 500g',      'Food & Beverages',     'Generic',   'PC',    1,     2.49, false, 'Durum wheat semolina spaghetti, 500g pack.'],
            ['Tomato Paste 400g',         'Food & Beverages',     'Nestlé',    'PC',    1.2,   2.99, false, 'Concentrated tomato paste, 400g tin.'],

            // Home & Kitchen
            ['Stainless Steel Pot Set 5pc','Home & Kitchen',      'Generic',   'PC',   20,    39.99, false, '5-piece stainless steel cookware set, suitable for all hobs.'],
            ['Non-stick Frying Pan 28cm', 'Home & Kitchen',       'Phillips',  'PC',   12,    24.99, false, 'Heavy-gauge non-stick frying pan with heat-resistant handle.'],
            ['Vacuum Thermos Flask 1L',   'Home & Kitchen',       'Generic',   'PC',    8,    15.99, false, 'Stainless steel vacuum flask, keeps drinks hot or cold 24h.'],
            ['Phillips Electric Kettle 1.7L','Home & Kitchen',    'Phillips',  'PC',   18,    34.99, true,  'Rapid-boil electric kettle, automatic shut-off.'],
            ['Queen Size Bed Sheet Set',  'Home & Kitchen',       'Rozana',    'PC',   14,    29.99, false, '100% cotton bed sheet set, queen size with pillowcases.'],
            ['Hand Towel Set 4-Pack',     'Home & Kitchen',       'Rozana',    'PK',    8,    16.99, false, 'Soft absorbent cotton hand towels, 4-pack assorted colours.'],

            // Beauty & Personal Care
            ['Dove Body Wash 500ml',      'Beauty & Personal Care','Unilever', 'BTL',   3.5,   7.99, false, 'Moisturising body wash with moisturising cream, 500ml.'],
            ['Clear Anti-Dandruff Shampoo 400ml','Beauty & Personal Care','Unilever','BTL',4,  8.99, false, 'Anti-dandruff shampoo for men and women, 400ml.'],
            ['Rozana Oud Perfume 50ml',   'Beauty & Personal Care','Rozana',   'BTL',  12,    29.99, true,  'Authentic Oud fragrance, long-lasting, 50ml spray bottle.'],
            ['Daily Moisturiser SPF 30',  'Beauty & Personal Care','Generic',  'PC',    6,    14.99, false, 'Daily moisturiser with SPF 30 sun protection, 50ml.'],

            // Sports & Outdoors
            ['Adidas Football Size 5',    'Sports & Outdoors',    'Adidas',    'PC',   15,    29.99, false, 'Match-quality football, FIFA approved, size 5.'],
            ['Premium Yoga Mat 6mm',      'Sports & Outdoors',    'Generic',   'PC',   10,    22.99, false, 'Non-slip yoga mat, 6mm thick, carrying strap included.'],
            ['Sports Water Bottle 750ml', 'Sports & Outdoors',    'Generic',   'BTL',   3,     8.99, false, 'BPA-free sports water bottle with flip-top lid, 750ml.'],

            // Books & Stationery
            ['Ruled Notebook A4 200pg',   'Books & Stationery',   'Generic',   'PC',    1.5,   3.99, false, 'Ruled notebook, A4, 200 pages, durable laminated cover.'],
            ['Ballpoint Pens 10-Pack',    'Books & Stationery',   'Generic',   'PK',    1,     2.99, false, 'Smooth-writing blue ballpoint pens, 10-pack.'],

            // Health & Wellness
            ['Vitamin C 500mg 60 Tablets','Health & Wellness',    'Generic',   'PC',    5,    12.99, false, 'Immune support Vitamin C supplement, 500mg, 60 tablets.'],
            ['Paracetamol 500mg 20 Tablets','Health & Wellness',  'Generic',   'PC',    1,     2.99, false, 'Pain and fever relief, 500mg paracetamol, 20 tablets.'],
            ['Hand Sanitiser Gel 500ml',  'Health & Wellness',    'Unilever',  'BTL',   2.5,   5.99, false, '70% alcohol hand sanitiser gel, 500ml pump bottle.'],
        ];

        $createdCount = 0;

        foreach ($catalog as [$name, $catName, $brandName, $unitCode, $cost, $price, $featured, $description]) {
            $slug = Str::slug($name);

            if (DB::table('products')->where('slug', $slug)->exists()) {
                continue;
            }

            // Unique product code
            do {
                $code = strtoupper(Str::random(2) . rand(1000, 9999));
            } while (DB::table('products')->where('code', $code)->exists());

            $productId = DB::table('products')->insertGetId([
                'name'             => $name,
                'slug'             => $slug,
                'code'             => $code,
                'sku'              => strtolower(Str::ulid()),
                'description'      => $description,
                'cost'             => $cost,
                'price'            => $price,
                'type'             => 'Standard',
                'active'           => 1,
                'featured'         => $featured ? 1 : 0,
                'category_id'      => $cats[$catName] ?? null,
                'brand_id'         => $brands[$brandName] ?? null,
                'unit_id'          => $units[$unitCode] ?? null,
                'tax_included'     => 0,
                'has_serials'      => 0,
                'has_variants'     => 0,
                'dont_track_stock' => 0,
                'hide_in_shop'     => 0,
                'extra_attributes' => json_encode([]),
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);

            // Create a stock ledger entry for this product in the store
            $stockId = DB::table('stocks')->insertGetId([
                'product_id'     => $productId,
                'variation_id'   => null,
                'store_id'       => $storeId,
                'alert_quantity' => 5,
                'created_at'     => $now,
                'updated_at'     => $now,
            ]);

            // Record the initial balance of 50 units via the tracks ledger
            DB::table('tracks')->insert([
                'product_id'       => $productId,
                'variation_id'     => null,
                'store_id'         => $storeId,
                'trackable_type'   => 'App\Models\Sma\Product\Stock',
                'trackable_id'     => $stockId,
                'reference_type'   => null,
                'reference_id'     => null,
                'value'            => 50,
                'description'      => 'Opening stock — demo seed',
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);

            $createdCount++;
        }

        $this->command?->info("DemoProductSeeder: {$createdCount} products seeded with 50 units stock each.");
        $this->command?->info('Categories: ' . count($cats) . ' | Brands: ' . count($brands) . ' | Unit types: ' . count($units));
    }
}
