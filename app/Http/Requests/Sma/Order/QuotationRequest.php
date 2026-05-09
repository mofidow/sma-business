<?php

namespace App\Http\Requests\Sma\Order;

use App\Tec\Rules\ExtraAttributes;
use App\Tec\Rules\ProductVariation;
use App\Tec\Services\OrderCalculator;
use Illuminate\Foundation\Http\FormRequest;

class QuotationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('quotation') ? 'update-quotations' : 'create-quotations');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date'        => 'required|date',
            'reference'   => 'nullable|max:50|unique:quotations,reference,' . $this->route('quotation')?->id,
            'customer_id' => 'required|exists:customers,id',

            'details'       => 'nullable',
            'attachments'   => 'nullable|array',
            'attachments.*' => 'mimes:' . ((get_settings('attachment_exts') ?? null) ?: 'jpg,png,pdf,xlsx,docx,zip'),

            'items'                         => 'required|array|min:1',
            'items.*.id'                    => 'nullable',
            'items.*.discount'              => 'nullable',
            'items.*.quantity'              => 'required|numeric|min:0',
            'items.*.unit_id'               => 'nullable|exists:units,id',
            'items.*.price'                 => 'required|numeric|min:0',
            'items.*.taxes'                 => 'nullable|array',
            'items.*.product_id'            => 'required|exists:products,id',
            'items.*.variations'            => new ProductVariation,
            'items.*.variations.*.id'       => new ProductVariation,
            'items.*.variations.*.quantity' => 'required_with:items.*.variations|numeric|min:0',
            'items.*.variations.*.unit_id'  => 'nullable|exists:units,id',
            'items.*.variations.*.price'    => 'nullable',
            'items.*.variations.*.discount' => 'nullable',
            'items.*.variations.*.taxes'    => 'nullable|array',

            'extra_attributes' => new ExtraAttributes('quotation'),
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

        return OrderCalculator::calculate($data, 'price');
    }
}
