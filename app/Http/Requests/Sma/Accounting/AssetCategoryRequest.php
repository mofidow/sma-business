<?php

namespace App\Http\Requests\Sma\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class AssetCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('asset_category') ? 'update-asset-categories' : 'create-asset-categories');
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255|unique:asset_categories,name,' . $this->route('asset_category')?->id,
            'description' => 'nullable|string|max:500',
            'active'      => 'nullable|boolean',
        ];
    }
}
