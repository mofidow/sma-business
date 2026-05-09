<?php

namespace App\Http\Requests\Sma\Accounting;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Sma\Accounting\AssetMaintenance;

class AssetMaintenanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('asset_maintenance') ? 'update-asset-maintenances' : 'create-asset-maintenances');
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'asset_id'   => 'required|exists:assets,id',
            'title'      => 'required|string|max:255',
            'type'       => 'required|in:' . implode(',', AssetMaintenance::$types),
            'start_date' => 'required|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
            'cost'       => 'nullable|numeric|min:0',
            'note'       => 'nullable|string|max:1000',
            'status'     => 'required|in:' . implode(',', AssetMaintenance::$statuses),
        ];
    }
}
