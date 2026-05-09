<?php

namespace App\Http\Requests\Sma\Setting;

use Illuminate\Foundation\Http\FormRequest;

class TaxRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('store') ? 'update-stores' : 'create-stores');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'        => 'required|max:55|unique:taxes,name,' . $this->route('tax')?->id,
            'code'        => 'nullable|max:10|unique:taxes,code,' . $this->route('tax')?->id,
            'rate'        => 'required|numeric',
            'same'        => 'boolean',
            'state'       => 'boolean',
            'compound'    => 'boolean',
            'recoverable' => 'boolean',
            'number'      => 'nullable',
            'details'     => 'nullable',
        ];
    }
}
