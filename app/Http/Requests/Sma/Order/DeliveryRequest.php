<?php

namespace App\Http\Requests\Sma\Order;

use App\Tec\Rules\ExtraAttributes;
use Illuminate\Foundation\Http\FormRequest;

class DeliveryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return $this->user()->can($this->route('delivery') ? 'update-deliveries' : 'create-deliveries');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'date'         => 'required|date',
            'reference'    => 'nullable|string|max:36',
            'sale_id'      => 'required|exists:sales,id',
            'address_id'   => 'required|exists:addresses,id',
            'customer_id'  => 'required|exists:customers,id',
            'delivered'    => 'nullable|boolean',
            'delivered_at' => 'nullable|required_if:delivered,true|date',
            'delivered_by' => 'nullable|required_if:delivered,true|string',
            'received_by'  => 'nullable|required_if:delivered,true|string',

            'details'       => 'nullable',
            'attachments'   => 'nullable|array',
            'attachments.*' => 'mimes:' . ((get_settings('attachment_exts') ?? null) ?: 'jpg,png,pdf,xlsx,docx,zip'),

            'extra_attributes' => ['nullable', new ExtraAttributes('delivery')],
        ];
    }
}
