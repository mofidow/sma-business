<?php

namespace App\Http\Requests\Sma\Hr;

use Illuminate\Foundation\Http\FormRequest;

class ClaimRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('claim') ? 'update-claims' : 'create-claims');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'employee_id' => 'required|exists:employees,id',
            'store_id'    => 'required|exists:stores,id',
            'date'        => 'required|date',
            'title'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount'      => 'required|numeric|min:0',
            'notes'       => 'nullable|string',
        ];
    }
}
