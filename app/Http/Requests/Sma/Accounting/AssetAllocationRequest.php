<?php

namespace App\Http\Requests\Sma\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class AssetAllocationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('asset_allocation') ? 'update-asset-allocations' : 'create-asset-allocations');
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'asset_id'       => 'required|exists:assets,id',
            'allocated_to'   => 'required|exists:users,id',
            'allocated_date' => 'required|date',
            'return_date'    => 'nullable|date|after_or_equal:allocated_date',
            'note'           => 'nullable|string|max:1000',
        ];
    }
}
