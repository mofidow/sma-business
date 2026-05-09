<?php

namespace App\Http\Requests\Sma\Order;

use App\Tec\Rules\ExtraAttributes;
use App\Tec\Rules\ProductVariation;
use App\Tec\Services\OrderCalculator;
use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('purchase') ? 'update-purchases' : 'create-purchases');
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
            'reference'   => 'nullable|max:50|unique:purchases,reference,' . $this->route('purchase')?->id,
            'supplier_id' => 'required|exists:suppliers,id',
            // 'status'      => 'required|in:Pending,Ordered,Received',

            'details'       => 'nullable',
            'attachments'   => 'nullable|array',
            'attachments.*' => 'mimes:' . ((get_settings('attachment_exts') ?? null) ?: 'jpg,png,pdf,xlsx,docx,zip'),

            'items'                         => 'required|array|min:1',
            'items.*.id'                    => 'nullable',
            'items.*.discount'              => 'nullable',
            'items.*.quantity'              => 'required|numeric|min:0',
            'items.*.unit_id'               => 'nullable|exists:units,id',
            'items.*.cost'                  => 'required|numeric|min:0',
            'items.*.taxes'                 => 'nullable|array',
            'items.*.discount'              => 'nullable',
            'items.*.old_quantity'          => 'nullable|numeric',
            'items.*.batch_no'              => 'nullable',
            'items.*.expiry_date'           => 'nullable|date',
            'items.*.serials'               => 'nullable|array',
            'items.*.serials.*'             => 'required|string|max:190',
            'items.*.product_id'            => 'required|exists:products,id',
            'items.*.variations'            => new ProductVariation,
            'items.*.variations.*.id'       => new ProductVariation,
            'items.*.variations.*.quantity' => 'required_with:items.*.variations|numeric|min:0',
            'items.*.variations.*.unit_id'  => 'nullable|exists:units,id',
            'items.*.variations.*.cost'     => 'nullable',
            'items.*.variations.*.discount' => 'nullable',
            'items.*.variations.*.taxes'    => 'nullable|array',

            'extra_attributes' => new ExtraAttributes('purchase'),
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

        $data['status'] = 'Received';

        return OrderCalculator::calculate($data, 'cost');
    }
}
