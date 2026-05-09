<?php

namespace App\Http\Requests\Sma\People;

use Illuminate\Foundation\Http\FormRequest;

class CustomerGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('customer') ? 'update-customers' : 'create-customers');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'              => 'required|unique:customer_groups,name,' . $this->route('customer_group')?->id,
            'discount'          => 'required|min:0|numeric',
            'apply_as_discount' => 'nullable|boolean',
            'details'           => 'nullable|string',
            'price_group_id'    => 'nullable|exists:price_groups,id',
        ];
    }
}
