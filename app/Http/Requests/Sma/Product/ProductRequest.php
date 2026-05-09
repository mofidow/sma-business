<?php

namespace App\Http\Requests\Sma\Product;

use App\Tec\Rules\ExtraAttributes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('product') ? 'update-products' : 'create-products');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type'             => 'required|in:Standard,Service,Digital,Combo,Recipe',
            'name'             => 'required',
            'secondary_name'   => 'nullable',
            'code'             => 'required|string|max:20|unique:products,code,' . $this->route('product')?->id,
            'sku'              => 'nullable|string|max:36|unique:products,sku,' . $this->route('product')?->id,
            'symbology'        => 'required|in:CODE128,CODE39,EAN8,EAN13,UPC',
            'category_id'      => 'required|exists:categories,id',
            'subcategory_id'   => 'nullable',
            'brand_id'         => 'nullable',
            'unit_id'          => 'nullable',
            'sale_unit_id'     => 'nullable',
            'purchase_unit_id' => 'nullable',
            'unit_prices'      => 'nullable|array',
            'cost'             => 'required|numeric',
            'price'            => 'required|numeric',
            'min_price'        => 'nullable|numeric',
            'max_price'        => 'nullable|numeric',
            'max_discount'     => 'nullable|numeric',
            'hsn_number'       => 'nullable',
            'sac_number'       => 'nullable',
            'weight'           => 'nullable|numeric',
            'dimensions'       => 'nullable',
            'rack_location'    => 'nullable',
            'supplier_id'      => 'nullable|exists:suppliers,id',
            'supplier_part_id' => 'nullable',
            'features'         => 'nullable|string',
            'details'          => 'nullable|string',
            'file'             => 'nullable|required_if:type,Digital|mimes:zip',
            'active'           => 'nullable|boolean',
            'featured'         => 'nullable|boolean',
            'hide_in_pos'      => 'nullable|boolean',
            'hide_in_shop'     => 'nullable|boolean',
            'tax_included'     => 'nullable|boolean',
            'can_edit_price'   => 'nullable|boolean',
            'has_expiry_date'  => 'nullable|boolean',
            'dont_track_stock' => 'nullable|boolean',
            'photos'           => 'nullable|array',
            'photo'            => 'nullable|mimes:jpg,jpeg,png,avif,webp|max:2048',
            'photos.*'         => 'nullable|mimes:jpg,jpeg,png,avif,webp|max:2048',
            // 'photo'            => 'nullable|mimes:jpg,jpeg,png,avif,webp|dimensions:ratio=1/1|max:2048', // if wants to force 1:1 ratio
            // 'photos.*'         => 'nullable|mimes:jpg,jpeg,png,avif,webp|dimensions:ratio=1/1|max:2048', // if wants to force 1:1 ratio
            'video_url'      => 'nullable|url',
            'alert_quantity' => 'nullable|numeric',

            'has_variants'            => 'nullable|boolean',
            'variants'                => 'nullable|required_if:has_variants,true|array',
            'variations.*'            => 'nullable|required_if:has_variants,true|array',
            'variations.*.sku'        => 'nullable',
            'variations.*.code'       => 'required|regex:/^[A-Za-z0-9\.\_\-\:]+$/|required_if:has_variants,true',
            'variations.*.meta'       => 'nullable|array|required_if:has_variants,true',
            'variations.*.price'      => 'nullable',
            'variations.*.cost'       => 'nullable',
            'variations.*.weight'     => 'nullable',
            'variations.*.dimensions' => 'nullable',
            'has_serials'             => 'nullable|boolean',
            'serials.*'               => 'nullable|array',

            'taxes'   => 'nullable|array',
            'taxes.*' => 'nullable|exists:taxes,id',

            'products'            => 'nullable|required_if:type,Combo|array',
            'products.*.id'       => 'nullable|required_if:type,Combo',
            'products.*.quantity' => 'nullable|required_if:type,Combo',

            'recipes'              => 'nullable|required_if:type,Recipe|array',
            'recipes.*.id'         => 'nullable|required_if:type,Recipe|exists:products,id',
            'recipes.*.quantity'   => 'nullable|required_if:type,Recipe|numeric|min:0',
            'recipes.*.unit_id'    => 'nullable|exists:units,id',
            'recipes.*.sort_order' => 'nullable|integer',

            'stores.*.store_id'       => 'required',
            'stores.*.quantity'       => 'nullable|numeric',
            'stores.*.price'          => 'nullable|numeric',
            'stores.*.taxes'          => 'nullable|array',
            'stores.*.alert_quantity' => 'nullable|numeric',

            'slug'        => 'nullable|string|max:50',
            'title'       => 'nullable|string|max:60',
            'description' => 'nullable|string|max:160',
            'keywords'    => 'nullable|string|max:190',
            'noindex'     => 'nullable|boolean',
            'nofollow'    => 'nullable|boolean',

            'extra_attributes' => ['nullable', new ExtraAttributes('product')],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'photo.dimensions'        => __('The :attribute width & height must be equal (ratio 1:1).'),
            'photos.*.dimensions'     => __('The :attribute width & height must be equal (ratio 1:1).'),
            'variations.*.code.regex' => __('The :attribute should be alphanumeric and may include dots, underscores, hyphens, and colons.'),
        ];
    }

    /**
     * Get the validated data from the request.
     *
     * @param  array|int|string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if ($this->has('photo') && $this->photo) {
            $data['photo'] = Storage::disk('asset')->url($this->photo->store('/images/products', 'asset'));
            if ($this->route('product')?->id && $this->route('product')?->photo) {
                $oldPhotoPath = str_replace(Storage::disk('asset')->url(''), '', $this->route('product')->photo);
                Storage::disk('asset')->delete($oldPhotoPath);
            }
        } else {
            unset($data['photo']);
        }

        $data['title'] ??= str(strip_tags($data['name'] ?? ''))->limit(60);
        $data['description'] ??= str(strip_tags($data['details'] ?? ''))->limit(160);

        return $data;
    }
}
