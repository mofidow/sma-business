<?php

namespace App\Http\Requests\Sma\Hr;

use Illuminate\Foundation\Http\FormRequest;

class LeaveTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('settings');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'          => 'required|string|max:255',
            'days_per_year' => 'required|integer|min:0',
            'is_paid'       => 'boolean',
            'carry_forward' => 'boolean',
            'description'   => 'nullable|string',
        ];
    }
}
