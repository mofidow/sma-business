<?php

namespace App\Http\Requests\Sma\Accounting;

use App\Models\Sma\Accounting\Asset;
use Illuminate\Foundation\Http\FormRequest;

class AssetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('asset') ? 'update-assets' : 'create-assets');
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'              => 'required|string|max:255',
            'code'              => 'nullable|string|max:55|unique:assets,code,' . $this->route('asset')?->id,
            'asset_category_id' => 'required|exists:asset_categories,id',
            'serial_number'     => 'nullable|string|max:255',
            'purchase_cost'     => 'nullable|numeric|min:0',
            'purchase_date'     => 'nullable|date',
            'warranty_expiry'   => 'nullable|date|after_or_equal:purchase_date',
            'condition'         => 'required|in:' . implode(',', Asset::$conditions),
            'description'       => 'nullable|string|max:1000',
            'active'            => 'nullable|boolean',
        ];
    }
}
