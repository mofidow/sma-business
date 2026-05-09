<?php

namespace App\Tec\Imports;

use App\Models\User;
use App\Models\Sma\Product\Unit;
use App\Models\Sma\Product\Brand;
use App\Models\Sma\People\Supplier;
use App\Models\Sma\Product\Product;
use App\Models\Sma\Product\Category;
use App\Models\Sma\Setting\CustomField;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\PersistRelations;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use App\Http\Requests\Sma\Product\ProductRequest;

class ProductImport implements PersistRelations, ToModel, WithBatchInserts, WithChunkReading, WithHeadingRow, WithUpserts, WithValidation
{
    use Importable;

    public $units;

    public $brands;

    public $categories;

    public $customFields;

    public $booleanFields = [
        'featured', 'hide_in_pos', 'hide_in_shop', 'tax_included',
        'can_edit_price', 'has_expiry_date', 'dont_track_stock',
        'has_serials', 'has_variants', 'noindex', 'nofollow',
    ];

    public function __construct(public User $user)
    {
        $this->units = Unit::all();
        $this->brands = Brand::all();
        $this->categories = Category::all();
        $this->customFields = CustomField::ofModel('product')->get();
    }

    public function model(array $row)
    {
        $product = new Product([
            'type'             => $row['type'],
            'name'             => $row['name'],
            'secondary_name'   => $row['secondary_name'],
            'code'             => (string) $row['code'],
            'symbology'        => $row['symbology'],
            'category_id'      => $row['category_id'],
            'subcategory_id'   => $row['subcategory_id'],
            'brand_id'         => $row['brand_id'],
            'unit_id'          => $row['unit_id'],
            'sale_unit_id'     => $row['sale_unit_id'],
            'purchase_unit_id' => $row['purchase_unit_id'],

            'cost'         => (float) $row['cost'],
            'price'        => (float) $row['price'],
            'min_price'    => (float) $row['min_price'],
            'max_price'    => (float) $row['max_price'],
            'max_discount' => (float) $row['max_discount'],

            'hsn_number'    => $row['hsn_number'],
            'sac_number'    => $row['sac_number'],
            'weight'        => (float) $row['weight'],
            'dimensions'    => $row['dimensions'],
            'rack_location' => $row['rack_location'],

            'supplier_id'      => $row['supplier_id'],
            'supplier_part_id' => $row['supplier_part_id'],

            'featured'         => $row['featured'] == 'yes',
            'hide_in_pos'      => $row['hide_in_pos'] == 'yes',
            'hide_in_shop'     => $row['hide_in_shop'] == 'yes',
            'tax_included'     => $row['tax_included'] == 'yes',
            'has_serials'      => $row['has_serials'] == 'yes',
            'can_edit_price'   => $row['can_edit_price'] == 'yes',
            'has_expiry_date'  => $row['has_expiry_date'] == 'yes',
            'dont_track_stock' => $row['dont_track_stock'] == 'yes',

            'video_url'      => $row['video_url'],
            'alert_quantity' => $row['alert_quantity'],
            'has_variants'   => $row['has_variants'] == 'yes',
            'variants'       => $row['variants'], // ? json_encode($row['variants']) : null,
            'slug'           => $row['slug'],
            'title'          => $row['title'],
            'keywords'       => $row['keywords'],
            'noindex'        => $row['noindex'] == 'yes',
            'nofollow'       => $row['nofollow'] == 'yes',
            'description'    => $row['description'],
            'details'        => $row['details'],

            'sku' => ulid(),
        ]);

        if ($row['photo'] ?? null) {
            $product->photo = $row['photo'];
        }
        if ($row['photos'] ?? null) {
            $row['photos'] = array_map('trim', explode(',', $row['photos']));
            foreach ($row['photos'] as $key => $photo) {
                logger()->info('Set photos #' . $key . ' to ' . $photo);
            }
        }

        return $product;
    }

    public function prepareForValidation($row, $index)
    {
        $row['unit_code'] ??= null;
        $row['sale_unit'] ??= null;
        $row['purchase_unit'] ??= null;
        $row['category_name'] ??= null;
        $row['brand_name'] ??= null;
        $row['subcategory_name'] ??= null;
        $row['supplier_company'] ??= null;

        $row['slug'] ??= str($row['name'])->slug()->toString();
        $row['title'] ??= str($row['name'])->title()->toString();

        $unit = $this->units->where('code', $row['unit_code'])->first();
        $row['brand_id'] = $this->brands->where('name', $row['brand_name'])->first()?->id;
        $row['category_id'] = $this->categories->where('name', $row['category_name'])->first()?->id;
        $row['subcategory_id'] = $this->categories->where('name', $row['subcategory_name'])->first()?->id;

        $row['unit_id'] = $unit?->id;
        $row['sale_unit_id'] = $unit?->subunits?->where('name', $row['sale_unit'])->first()?->id;
        $row['purchase_unit_id'] = $unit?->subunits?->where('name', $row['purchase_unit'])->first()?->id;

        if ($row['has_variants'] == 'yes') {
            $variants = [];
            $variants_str = array_map('trim', explode('|', $row['variants']));
            foreach ($variants_str as $variant) {
                $variant = array_map('trim', explode(':', $variant));
                $variants[] = [
                    'name'    => $variant[0],
                    'options' => array_map('trim', explode(',', $variant[1])),
                ];
            }
            $row['variants'] = $variants;
        } else {
            $row['variants'] = null;
        }

        $row['supplier_id'] = $row['supplier_company'] ? Supplier::select(['id', 'name'])->where('company', $row['supplier_company'])->first()?->id : null;

        foreach ($this->booleanFields as $field) {
            $row[$field] = $row[$field] == 'yes';
        }

        if ($this->customFields) {
            // $row['extra_attributes'] = [];
            $row['extra_attributes'] = array_map('trim', explode(',', $row['extra_attributes']));

            foreach ($row['extra_attributes'] as $key => $value) {
                $attr = array_map('trim', explode(':', $value));
                $row['extra_attributes'][$attr[0]] = $attr[1];
                unset($row['extra_attributes'][$key]);
            }

            foreach ($this->customFields as $field) {
                $row['extra_attributes'][$field->name] ??= '';
            }
        }

        // logger()->info('Prepared row #' . $index, $row);

        return $row;
    }

    public function rules(): array
    {
        $rules = (new ProductRequest)->rules();
        $rules['code'] = 'required|string|max:55';
        $rules['photo'] = 'nullable|url';
        $rules['photos'] = 'nullable';
        $rules['photos.*'] = 'nullable|url';

        return $rules;
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function uniqueBy()
    {
        return ['code'];
    }
}
