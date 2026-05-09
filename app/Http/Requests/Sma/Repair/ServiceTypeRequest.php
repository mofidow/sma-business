<?php

namespace App\Http\Requests\Sma\Repair;

use Illuminate\Foundation\Http\FormRequest;

class ServiceTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('service_type') ? 'update-service-types' : 'create-service-types');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'          => 'required|string|max:255|unique:service_types,name',
            'description'   => 'nullable|string',
            'active'        => 'nullable|boolean',
            'sort_order'    => 'nullable|integer|min:0',
            'custom_fields' => 'nullable|array',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name'       => 'service type name',
            'sort_order' => 'sort order',
        ];
    }
}
