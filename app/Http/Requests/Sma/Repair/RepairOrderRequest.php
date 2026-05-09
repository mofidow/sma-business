<?php

namespace App\Http\Requests\Sma\Repair;

use App\Tec\Rules\ExtraAttributes;
use Illuminate\Foundation\Http\FormRequest;

class RepairOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('repair_order') ? 'update-repair-orders' : 'create-repair-orders');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id'         => 'required|exists:customers,id',
            'service_type_id'     => 'required|exists:service_types,id',
            'technician_id'       => 'nullable|exists:users,id',
            'serial_no'           => 'nullable|string|max:255',
            'problem_description' => 'required|string',
            'device_password'     => 'nullable|string|max:255',
            'device_pattern'      => 'nullable|string|max:255',
            'device_condition'    => 'required|in:excellent,good,fair,poor',
            'reference'           => 'nullable',
            'custom_fields'       => 'nullable|array',
            'technician_comment'  => 'nullable|string',
            'price'               => 'required|numeric|min:0',
            'actual_cost'         => 'nullable|numeric|min:0',
            'tax_amount'          => 'nullable|numeric|min:0',
            'tax_included'        => 'nullable|boolean',
            'taxes'               => 'nullable|array',
            'taxes.*'             => 'exists:taxes,id',
            'received_date'       => 'nullable|date',
            'due_date'            => 'nullable|date|after_or_equal:received_date',
            'status'              => 'nullable|in:pending,in_progress,waiting_parts,completed,delivered,cancelled',
            'priority'            => 'nullable|in:low,normal,high,urgent',
            'internal_notes'      => 'nullable|string',
            'customer_notes'      => 'nullable|string',
            'attachments'         => 'nullable|array',
            'attachments.*'       => 'file|max:2048',

            'extra_attributes' => ['nullable', new ExtraAttributes('repair')],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'customer_id'         => 'customer',
            'service_type_id'     => 'service type',
            'technician_id'       => 'technician',
            'problem_description' => 'problem description',
            'device_condition'    => 'device condition',
            'price'               => 'estimated cost',
            'actual_cost'         => 'actual cost',
        ];
    }
}
