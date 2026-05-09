<?php

namespace App\Http\Requests\Sma\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class AccountRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('account') ? 'update-accounts' : 'create-accounts');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'details'         => 'nullable',
            'type'            => 'nullable|max:55',
            'account_type_id' => 'required|exists:account_types,id',
            'title'           => 'required|max:55|unique:accounts,title,' . $this->route('account')?->id,
            'reference'       => 'nullable|max:255',
            'opening_balance' => 'required|numeric',
            'active'          => 'nullable|boolean',
            'offline'         => 'nullable|boolean',
            'fees'            => 'nullable|boolean',
            'fixed'           => 'nullable|numeric',
            'percentage'      => 'nullable|numeric',
            'apply_to'        => 'nullable|in:Credit,Debit,Both',
        ];
    }
}
