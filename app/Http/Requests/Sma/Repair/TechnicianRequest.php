<?php

namespace App\Http\Requests\Sma\Repair;

use Illuminate\Foundation\Http\FormRequest;

class TechnicianRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('technician') ? 'update-technicians' : 'create-technicians');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $technicianId = $this->route('technician')?->id;

        return [
            'name'          => 'required|string|max:255',
            'email'         => 'nullable|email|unique:technicians,email,' . $technicianId,
            'phone'         => 'nullable|string|max:50',
            'skills'        => 'nullable|string',
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
            'name'       => 'technician name',
            'sort_order' => 'sort order',
        ];
    }
}
