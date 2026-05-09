<?php

namespace App\Http\Requests\Sma\Product;

use Illuminate\Foundation\Http\FormRequest;

class PromotionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('promotion') ? 'update-promotions' : 'create-promotions');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'              => 'required|max:50|unique:promotions,name,' . $this->route('promotion')?->id,
            'type'              => 'bail|required|in:simple,advance,BXGY,SXGD',
            'start_date'        => 'nullable|date',
            'end_date'          => 'nullable|date',
            'active'            => 'nullable',
            'discount'          => 'nullable|numeric|required_if:type,simple,advance,SXGD',
            'discount_method'   => 'nullable|in:fixed,percentage',
            'show_on_receipt'   => 'nullable',
            'details'           => 'nullable|string',
            'amount_to_spend'   => 'nullable|numeric',
            'product_id_to_buy' => 'nullable',
            'quantity_to_buy'   => 'nullable|numeric',
            'product_id_to_get' => 'nullable',
            'quantity_to_get'   => 'nullable|numeric',
            'times_to_apply'    => 'nullable',
            'coupon_code'       => 'nullable',
            'products'          => 'nullable|array',
            'categories'        => 'nullable|array',
            'stores'            => 'nullable|array',
        ];
    }
}
