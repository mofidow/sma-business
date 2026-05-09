<?php

namespace App\Tec\Services;

use Exception;
use App\Models\State;
use App\Models\Country;
use App\Models\Sma\Setting\Tax;
use App\Models\Sma\Product\Unit;
use App\Models\Sma\Product\Brand;
use App\Models\Sma\Setting\Store;
use App\Models\Sma\People\Address;
use Illuminate\Support\Facades\DB;
use App\Models\Sma\People\Customer;
use App\Models\Sma\People\Supplier;
use App\Models\Sma\Product\Product;
use Illuminate\Support\Facades\Log;
use App\Models\Sma\Product\Category;
use Illuminate\Support\Facades\Auth;

class V3ImportService
{
    protected $connection;

    protected $logs = [];

    protected $stats = [];

    public function testConnection(array $config): bool
    {
        $this->setupConnection($config);

        // Test query
        $this->connection->select('SELECT 1');

        return true;
    }

    protected function setupConnection(array $config): void
    {
        config([
            'database.connections.v3_import' => [
                'driver'    => 'mysql',
                'host'      => $config['host'],
                'port'      => $config['port'],
                'database'  => $config['database'],
                'username'  => $config['username'],
                'password'  => $config['password'] ?? '',
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix'    => '',
                'strict'    => false,
            ],
        ]);

        DB::purge('v3_import');
        $this->connection = DB::connection('v3_import');
    }

    public function import(array $config, array $types): array
    {
        $this->setupConnection($config);
        $this->logs = [];
        $this->stats = [];

        foreach ($types as $type) {
            $this->stats[$type] = [
                'total'   => 0,
                'created' => 0,
                'skipped' => 0,
                'failed'  => 0,
            ];

            try {
                match ($type) {
                    'warehouses' => $this->importWarehouses(),
                    'categories' => $this->importCategories(),
                    'brands'     => $this->importBrands(),
                    'units'      => $this->importUnits(),
                    'tax_rates'  => $this->importTaxRates(),
                    'customers'  => $this->importCustomers(),
                    'suppliers'  => $this->importSuppliers(),
                    'products'   => $this->importProducts(),
                };
            } catch (Exception $e) {
                $this->addLog($type, 'error', 'Import failed: ' . $e->getMessage());
                Log::error("V3 Import Error ({$type}): " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        return [
            'stats' => $this->stats,
            'logs'  => $this->logs,
        ];
    }

    protected function importWarehouses(): void
    {
        if (! ($account_id = get_settings('default_account'))) {
            throw new Exception('Default account is not set. Please set it before importing warehouses.');
        }

        $warehouses = $this->connection->table('sma_warehouses')->get();
        $this->stats['warehouses']['total'] = $warehouses->count();

        foreach ($warehouses as $warehouse) {
            try {
                // Check if store with same name exists
                $existing = Store::where('name', $warehouse->name)->first();

                if ($existing) {
                    $this->stats['warehouses']['skipped']++;
                    $this->addLog('warehouses', 'skipped', "Store '{$warehouse->name}' already exists");

                    continue;
                }

                Store::create([
                    'name'           => $warehouse->name,
                    'phone'          => $warehouse->phone,
                    'email'          => $warehouse->email,
                    'address_line_1' => strip_tags($warehouse->address),
                    'active'         => true,
                    'account_id'     => $account_id,
                ]);

                $this->stats['warehouses']['created']++;
                $this->addLog('warehouses', 'success', "Created store: {$warehouse->name}");
            } catch (Exception $e) {
                $this->stats['warehouses']['failed']++;
                $this->addLog('warehouses', 'error', "Failed to import warehouse '{$warehouse->name}': {$e->getMessage()}");
            }
        }
    }

    protected function importCategories(): void
    {
        $categories = $this->connection->table('sma_categories')->get();
        $this->stats['categories']['total'] = $categories->count();

        foreach ($categories->whereNull('parent_id') as $category) {
            try {
                // Check by name
                $existing = Category::where('name', $category->name)->first();

                if ($existing) {
                    $this->stats['categories']['skipped']++;
                    $this->addLog('categories', 'skipped', "Category '{$category->name}' already exists");

                    continue;
                }

                Category::create([
                    'name'        => $category->name,
                    'slug'        => $category->slug ?: str($category->name)->slug(),
                    'title'       => $category->name,
                    'description' => $category->description,
                    'photo'       => $category->image,
                    'active'      => true,
                ]);

                $this->stats['categories']['created']++;
                $this->addLog('categories', 'success', "Created category: {$category->name}");
            } catch (Exception $e) {
                $this->stats['categories']['failed']++;
                $this->addLog('categories', 'error', "Failed to import category '{$category->name}': {$e->getMessage()}");
            }
        }

        foreach ($categories->whereNotNull('parent_id') as $category) {
            try {
                $parent = $categories->firstWhere('id', $category->parent_id);
                $parentCategory = Category::where('name', $parent->name)->first();
                // Check by name
                $existing = Category::where('name', $category->name)->first();

                if ($existing) {
                    $this->stats['categories']['skipped']++;
                    $this->addLog('categories', 'skipped', "Category '{$category->name}' already exists");

                    continue;
                }

                Category::create([
                    'name'        => $category->name,
                    'slug'        => $category->slug ?: str($category->name)->slug(),
                    'title'       => $category->name,
                    'description' => $category->description,
                    'photo'       => $category->image,
                    'category_id' => $parentCategory?->id,
                ]);

                $this->stats['categories']['created']++;
                $this->addLog('categories', 'success', "Created category: {$category->name}");
            } catch (Exception $e) {
                $this->stats['categories']['failed']++;
                $this->addLog('categories', 'error', "Failed to import category '{$category->name}': {$e->getMessage()}");
            }
        }
    }

    protected function importBrands(): void
    {
        $brands = $this->connection->table('sma_brands')->get();
        $this->stats['brands']['total'] = $brands->count();

        foreach ($brands as $brand) {
            try {
                // Check by name
                $existing = Brand::where('name', $brand->name)->first();

                if ($existing) {
                    $this->stats['brands']['skipped']++;
                    $this->addLog('brands', 'skipped', "Brand '{$brand->name}' already exists");

                    continue;
                }

                Brand::create([
                    'name'        => $brand->name,
                    'title'       => $brand->name,
                    'slug'        => $brand->slug ?: str($brand->name)->slug(),
                    'description' => $brand->description,
                    'photo'       => $brand->image,
                    'active'      => true,
                ]);

                $this->stats['brands']['created']++;
                $this->addLog('brands', 'success', "Created brand: {$brand->name}");
            } catch (Exception $e) {
                $this->stats['brands']['failed']++;
                $this->addLog('brands', 'error', "Failed to import brand '{$brand->name}': {$e->getMessage()}");
            }
        }
    }

    protected function importUnits(): void
    {
        $units = $this->connection->table('sma_units')->get();
        $this->stats['units']['total'] = $units->count();

        foreach ($units->whereNull('base_unit') as $unit) {
            try {
                // Check by code
                $existing = Unit::where('code', $unit->code)->first();

                if ($existing) {
                    $this->stats['units']['skipped']++;
                    $this->addLog('units', 'skipped', "Unit '{$unit->name}' already exists");

                    continue;
                }

                Unit::create(['code' => $unit->code, 'name' => $unit->name]);

                $this->stats['units']['created']++;
                $this->addLog('units', 'success', "Created unit: {$unit->name}");
            } catch (Exception $e) {
                $this->stats['units']['failed']++;
                $this->addLog('units', 'error', "Failed to import unit '{$unit->name}': {$e->getMessage()}");
            }
        }

        foreach ($units->whereNotNull('base_unit') as $unit) {
            try {
                // Check by code
                $existing = Unit::where('code', $unit->code)->first();

                if ($existing) {
                    $this->stats['units']['skipped']++;
                    $this->addLog('units', 'skipped', "Unit '{$unit->name}' already exists");

                    continue;
                }

                Unit::create([
                    'name'            => $unit->name,
                    'code'            => $unit->code,
                    'unit_id'         => $unit->base_unit,
                    'operator'        => $unit->operator,
                    'operation_value' => $unit->operation_value,
                ]);

                $this->stats['units']['created']++;
                $this->addLog('units', 'success', "Created unit: {$unit->name}");
            } catch (Exception $e) {
                $this->stats['units']['failed']++;
                $this->addLog('units', 'error', "Failed to import unit '{$unit->name}': {$e->getMessage()}");
            }
        }
    }

    protected function importTaxRates(): void
    {
        $tax_rates = $this->connection->table('sma_tax_rates')->get();
        $this->stats['tax_rates']['total'] = $tax_rates->count();

        foreach ($tax_rates->whereNull('base_tax_rate') as $tax_rate) {
            try {
                // Check by code
                $existing = Tax::where('code', $tax_rate->code)->first();

                if ($existing) {
                    $this->stats['tax_rates']['skipped']++;
                    $this->addLog('taxes', 'skipped', "Tax rate '{$tax_rate->name}' already exists");

                    continue;
                }

                if ($tax_rate->type == 2 && $tax_rate->rate != 0) {
                    $this->stats['tax_rates']['skipped']++;
                    $this->addLog('taxes', 'skipped', "Tax rate '{$tax_rate->name}' is fixed and cannot be imported.");

                    continue;
                }

                Tax::create([
                    'code'   => $tax_rate->code,
                    'name'   => $tax_rate->name,
                    'rate'   => $tax_rate->rate,
                    'active' => true,
                ]);

                $this->stats['tax_rates']['created']++;
                $this->addLog('taxes', 'success', "Created tax_rate: {$tax_rate->name}");
            } catch (Exception $e) {
                $this->stats['tax_rates']['failed']++;
                $this->addLog('taxes', 'error', "Failed to import tax_rate '{$tax_rate->name}': {$e->getMessage()}");
            }
        }
    }

    protected function importProducts(): void
    {
        $products = $this->connection->table('sma_products')->cursor();
        $this->stats['products']['total'] = $products->count();

        $symbologies = [
            'code25'  => 'CODE128',
            'code128' => 'CODE128',
            'code39'  => 'CODE39',
            'ean8'    => 'EAN8',
            'ean13'   => 'EAN13',
            'upca'    => 'UPC',
            'upce'    => 'UPC',
        ];
        $types = [
            'standard' => 'Standard',
            'digital'  => 'Digital',
            'combo'    => 'Combo',
            'service'  => 'Service',
        ];

        $oldUnits = $this->connection->table('sma_units')->get();
        $oldBrands = $this->connection->table('sma_brands')->get();
        $oldTaxRates = $this->connection->table('sma_tax_rates')->get();
        $oldCategories = $this->connection->table('sma_categories')->get();
        $oldSuppliers = $this->connection->table('sma_companies')->where('group_name', 'supplier')->get();

        $taxes = Tax::all();
        $units = Unit::all();
        $brands = Brand::all();
        $categories = Category::with('children')->get();
        $suppliers = Supplier::select(['id', 'name', 'company'])->get();

        foreach ($products as $oldProduct) {
            try {
                // Check by code
                $existing = Product::where('code', $oldProduct->code)->exists();

                if ($existing) {
                    $this->stats['products']['skipped']++;
                    $this->addLog('products', 'skipped', "Product '{$oldProduct->code}' already exists");

                    continue;
                }
                $variants = $this->connection->table('sma_product_variants')->where('product_id', $oldProduct->id)->get();
                if ($variants->count() > 0) {
                    $this->stats['products']['skipped']++;
                    $this->addLog('products', 'skipped', "Product '{$oldProduct->code}' has varianrs and cannot be imported.");

                    continue;
                }

                $oldCategory = $oldCategories->firstWhere('id', $oldProduct->category_id);
                $category = $categories->firstWhere('name', $oldCategory?->name);
                $subcategory = null;
                if ($category->children && $oldProduct->subcategory_id) {
                    $oldSubcategory = $oldCategories->firstWhere('id', $oldProduct->subcategory_id);
                    $subcategory = $category->children->firstWhere('name', $oldSubcategory->name);
                }

                $oldBrand = $oldBrands->firstWhere('id', $oldProduct->brand);
                $brand = $brands->firstWhere('name', $oldBrand?->name);

                $oldUnit = $oldUnits->firstWhere('id', $oldProduct->unit);
                $unit = $units->firstWhere('name', $oldUnit?->name);

                $oldSaleUnit = $oldUnits->firstWhere('id', $oldProduct->sale_unit);
                $saleUnit = $units->firstWhere('name', $oldSaleUnit?->name);

                $oldPurchaseUnit = $oldUnits->firstWhere('id', $oldProduct->purchase_unit);
                $purchaseUnit = $units->firstWhere('name', $oldPurchaseUnit?->name);

                $oldSupplier = $oldSuppliers->firstWhere('id', $oldProduct->supplier1);
                $supplier = $suppliers->firstWhere('name', $oldSupplier?->company ?: $oldSupplier?->name);

                $product = Product::create([
                    'name'             => $oldProduct->name,
                    'title'            => $oldProduct->name,
                    'code'             => $oldProduct->code,
                    'type'             => $types[$oldProduct->type],
                    'symbology'        => $symbologies[$oldProduct->barcode_symbology] ?? 'CODE128',
                    'unit_id'          => $unit?->id,
                    'sale_unit_id'     => $saleUnit?->id,
                    'purchase_unit_id' => $purchaseUnit?->id,
                    'category_id'      => $category?->id,
                    'subcategory_id'   => $subcategory?->id,
                    'brand_id'         => $brand?->id,
                    'supplier_id'      => $supplier?->id,
                    'supplier_part_id' => $oldProduct->supplier1_part_no,
                    'cost'             => $oldProduct->cost ?? 0,
                    'price'            => $oldProduct->price ?? 0,
                    'min_price'        => $oldProduct->cost ?? 0,
                    'alert_quantity'   => $oldProduct->alert_quantity ?? 0,
                    'features'         => $oldProduct->details,
                    'description'      => $oldProduct->details,
                    'hsn_number'       => $oldProduct->hsn_code,
                    'sac_number'       => $oldProduct->hsn_code,
                    'hide_in_pos'      => $oldProduct->hide_pos,
                    'featured'         => $oldProduct->featured,
                    'secondary_name'   => $oldProduct->second_name,
                    'details'          => $oldProduct->product_details,
                    'file'             => $oldProduct->file,
                    'weight'           => $oldProduct->weight,
                    'tax_included'     => $oldProduct->tax_method == 0,
                    'photo'            => $oldProduct->image,
                    'slug'             => $oldProduct->slug ?: str($oldProduct->name)->slug(),
                    'active'           => true,
                    'dont_track_stock' => $oldProduct->track_quantity == 1 ? false : true,
                ]);

                // images

                $oldTax = $oldTaxRates->firstWhere('id', $oldProduct->tax_rate);
                $tax = $taxes->firstWhere('name', $oldTax?->name);
                $product->taxes()->attach($tax?->id);

                // Import product variations // Can't as of now, as SMA v3 stores variants and  variations differently
                // $this->importProductVariations($product, $oldProduct->id);

                // Import combo items
                if ($oldProduct->type === 'combo') {
                    $this->importComboItems($product, $oldProduct->id);
                }

                // Import warehouse stock
                $this->importProductStock($product, $oldProduct->id);

                $this->stats['products']['created']++;
                $this->addLog('products', 'success', "Created product: {$oldProduct->code} - {$oldProduct->name}");
            } catch (Exception $e) {
                $this->stats['products']['failed']++;
                logger("V3 Import Product Error ({$oldProduct->code}): " . $e->getMessage(), [
                    'trace' => $e->getTraceAsString(),
                ]);
                $this->addLog('products', 'error', "Failed to import product '{$oldProduct->code}': {$e->getMessage()}");
            }
        }
    }

    protected function importProductVariations($product, $oldProductId): void
    {
        $variants = $this->connection->table('sma_product_variants')->where('product_id', $oldProductId)->get();

        foreach ($variants as $variant) {
            try {
                $product->variations()->create([
                    'sku'   => $variant->name,
                    'cost'  => $variant->cost ?? 0,
                    'price' => $variant->price ?? 0,
                ]);

                $product->update(['has_variants' => true, 'variants' => $variant->name]);
            } catch (Exception $e) {
                $this->addLog('products', 'warning', "Failed to import variation '{$variant->name}' for product {$product->code}: {$e->getMessage()}");
            }
        }
    }

    protected function importComboItems($product, $oldProductId): void
    {
        $comboItems = $this->connection->table('sma_combo_items')->where('product_id', $oldProductId)->get();

        foreach ($comboItems as $comboItem) {
            try {
                $itemProduct = Product::where('code', $comboItem->item_code)->first();

                if ($itemProduct) {
                    $product->products()->attach($itemProduct->id, [
                        'quantity' => $comboItem->quantity,
                    ]);
                }
            } catch (Exception $e) {
                $this->addLog('products', 'warning', "Failed to import combo item for product {$product->code}: {$e->getMessage()}");
            }
        }
    }

    protected function importProductStock($product, $oldProductId): void
    {
        $warehouseProducts = $this->connection->table('sma_warehouses_products')
            ->where('product_id', $oldProductId)
            ->get();

        foreach ($warehouseProducts as $wp) {
            try {
                $warehouse = $this->connection->table('sma_warehouses')->find($wp->warehouse_id);
                if (! $warehouse) {
                    continue;
                }

                $store = Store::where('name', $warehouse->name)->first();
                if (! $store) {
                    continue;
                }

                $stock = $product->stocks()->updateOrCreate([
                    'store_id'   => $store->id,
                    'product_id' => $product->id,
                ], [
                    'rack_location'  => $product->rack_location,
                    'alert_quantity' => $product->alert_quantity,
                ]);
                $stock->setBalance($wp->quantity ?? 0, [
                    'variation_id' => null,
                    'store_id'     => $store->id,
                    'product_id'   => $product->id,
                    'description'  => 'Initial stock',
                ]);
            } catch (Exception $e) {
                $this->addLog('products', 'warning', "Failed to import stock for product {$product->code}: {$e->getMessage()}");
            }
        }
    }

    protected function importCustomers(): void
    {
        $customers = $this->connection->table('sma_companies')
            ->where('group_name', 'customer')
            ->get();

        $this->stats['customers']['total'] = $customers->count();

        foreach ($customers as $oldCustomer) {
            try {
                // Check by company name, or name+email
                $existing = Customer::where('company', $oldCustomer->company)->first();

                if (! $existing && empty($oldCustomer->company)) {
                    $existing = Customer::where('name', $oldCustomer->name)
                        ->where('email', $oldCustomer->email)
                        ->first();
                }

                if ($existing) {
                    $this->stats['customers']['skipped']++;
                    $this->addLog('customers', 'skipped', "Customer '{$oldCustomer->company}' already exists");

                    continue;
                }

                $customer = Customer::create([
                    'name'            => $oldCustomer->name,
                    'company'         => $oldCustomer->company && $oldCustomer->company != '-' ? $oldCustomer->company : $oldCustomer->name,
                    'email'           => $oldCustomer->email,
                    'phone'           => $oldCustomer->phone,
                    'address_line_1'  => strip_tags($oldCustomer->address),
                    'vat_no'          => $oldCustomer->vat_no ?: $oldCustomer->gst_no,
                    'city'            => $oldCustomer->city,
                    'postal_code'     => $oldCustomer->postal_code,
                    'state_id'        => $oldCustomer->state ? State::where('name', $oldCustomer->state)->first()?->id : null,
                    'country_id'      => $oldCustomer->country ? Country::where('name', $oldCustomer->country)->first()?->id : null,
                    'opening_balance' => $oldCustomer->deposit_amount ?? 0,
                    'user_id'         => Auth::id(),
                ]);

                // Import address
                // if ($oldCustomer->address) {
                //     $this->importAddress($customer, $oldCustomer, 'customer');
                // }

                if ($oldCustomer->award_points) {
                    $customer->awardPoints()->create([
                        'value'       => $oldCustomer->award_points,
                        'sale_id'     => null,
                        'user_id'     => null,
                        'customer_id' => $customer->id,
                    ]);
                }

                $this->stats['customers']['created']++;
                $this->addLog('customers', 'success', "Created customer: {$oldCustomer->company}");
            } catch (Exception $e) {
                $this->stats['customers']['failed']++;
                $this->addLog('customers', 'error', "Failed to import customer '{$oldCustomer->company}': {$e->getMessage()}");
            }
        }
    }

    protected function importSuppliers(): void
    {
        $suppliers = $this->connection->table('sma_companies')
            ->where('group_name', 'supplier')
            ->get();

        $this->stats['suppliers']['total'] = $suppliers->count();

        foreach ($suppliers as $oldSupplier) {
            try {
                // Check by company name, or name+email
                $existing = Supplier::where('company', $oldSupplier->company)->first();

                if (! $existing && empty($oldSupplier->company)) {
                    $existing = Supplier::where('name', $oldSupplier->name)
                        ->where('email', $oldSupplier->email)
                        ->first();
                }

                if ($existing) {
                    $this->stats['suppliers']['skipped']++;
                    $this->addLog('suppliers', 'skipped', "Supplier '{$oldSupplier->company}' already exists");

                    continue;
                }

                $supplier = Supplier::create([
                    'name'            => $oldSupplier->name,
                    'company'         => $oldSupplier->company && $oldSupplier->company != '-' ? $oldSupplier->company : $oldSupplier->name,
                    'email'           => $oldSupplier->email,
                    'phone'           => $oldSupplier->phone,
                    'address_line_1'  => strip_tags($oldSupplier->address),
                    'vat_no'          => $oldSupplier->vat_no ?: $oldSupplier->gst_no,
                    'city'            => $oldSupplier->city,
                    'postal_code'     => $oldSupplier->postal_code,
                    'state_id'        => $oldSupplier->state ? State::where('name', $oldSupplier->state)->first()?->id : null,
                    'country_id'      => $oldSupplier->country ? Country::where('name', $oldSupplier->country)->first()?->id : null,
                    'opening_balance' => 0,
                    'user_id'         => Auth::id(),
                ]);

                $this->stats['suppliers']['created']++;
                $this->addLog('suppliers', 'success', "Created supplier: {$oldSupplier->company}");
            } catch (Exception $e) {
                $this->stats['suppliers']['failed']++;
                $this->addLog('suppliers', 'error', "Failed to import supplier '{$oldSupplier->company}': {$e->getMessage()}");
            }
        }
    }

    protected function importAddress($model, $oldRecord, $type): void
    {
        try {
            Address::create([
                'customer_id'    => $model->id,
                'user_id'        => Auth::id(),
                'name'           => $oldRecord->name,
                'email'          => $oldRecord->email,
                'phone'          => $oldRecord->phone,
                'company'        => $oldRecord->company,
                'address_line_1' => $oldRecord->address,
                'city'           => $oldRecord->city,
                'postal_code'    => $oldRecord->postal_code,
                'default'        => true,
            ]);
        } catch (Exception $e) {
            $this->addLog($type, 'warning', "Failed to import address for {$oldRecord->company}: {$e->getMessage()}");
        }
    }

    protected function addLog(string $type, string $level, string $message): void
    {
        $this->logs[] = [
            'type'    => $type,
            'level'   => $level, // success, error, warning, skipped
            'message' => $message,
            'time'    => now()->toDateTimeString(),
        ];
    }
}
