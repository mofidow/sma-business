<?php

namespace App\Http\Requests\Sma\Product;

use Illuminate\Foundation\Http\FormRequest;

class UnitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('unit') ? 'update-units' : 'create-units');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'            => 'required|unique:units,name,' . $this->route('unit')?->id,
            'code'            => 'bail|required|alpha_num|max:20|unique:units,code,' . $this->route('unit')?->id,
            'details'         => 'nullable',
            'operator'        => 'nullable|required_unless:unit_id,null|in:+,-,*,/',
            'operation_value' => 'nullable|required_unless:unit_id,null|numeric',
            'unit_id'         => 'nullable|exists:units,id',
        ];
    }
}
