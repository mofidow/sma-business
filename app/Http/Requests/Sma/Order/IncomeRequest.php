<?php

namespace App\Http\Requests\Sma\Order;

use App\Tec\Rules\ExtraAttributes;
use Illuminate\Foundation\Http\FormRequest;

class IncomeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return $this->user()->can($this->route('income') ? 'update-incomes' : 'create-incomes');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'details'     => 'nullable',
            'date'        => 'required|date',
            'reference'   => 'nullable|string|max:36',
            'amount'      => 'required|numeric|min:0.01',
            'account_id'  => 'nullable|exists:accounts,id',
            'customer_id' => 'nullable|exists:customers,id',

            'extra_attributes' => ['nullable', new ExtraAttributes('income')],
        ];
    }
}
