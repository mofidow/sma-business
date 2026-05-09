<?php

namespace App\Http\Requests\Sma\People;

use Illuminate\Foundation\Http\FormRequest;

class PriceGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('price_group') ? 'update-price-groups' : 'create-price-groups');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'    => 'required|unique:price_groups,name,' . $this->route('price_group')?->id,
            'details' => 'nullable|string',
        ];
    }
}
