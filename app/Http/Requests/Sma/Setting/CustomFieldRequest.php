<?php

namespace App\Http\Requests\Sma\Setting;

use App\Models\Sma\Setting\CustomField;
use Illuminate\Foundation\Http\FormRequest;

class CustomFieldRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('custom_field') ? 'update-custom-fields' : 'create-custom-fields');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'                 => 'required|max:50|unique:custom_fields,name,' . $this->route('custom_field')?->id,
            'type'                 => 'required|in:' . implode(',', CustomField::$types),
            'options'              => 'nullable|required_if:type,select,checkbox,radio|array',
            'models'               => 'required|array|min:1',
            'models.*'             => 'required|in:' . implode(',', CustomField::$models),
            'order_no'             => 'nullable|numeric',
            'is_required'          => 'nullable|boolean',
            'show_on_details_view' => 'nullable|boolean',
            'details'              => 'nullable',
        ];
    }
}
