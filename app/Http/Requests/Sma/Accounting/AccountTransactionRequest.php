<?php

namespace App\Http\Requests\Sma\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class AccountTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-account-transactions');
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'account_id' => 'required|exists:accounts,id',
            'type'       => 'required|in:debit,credit',
            'amount'     => 'required|numeric|min:0.01',
            'reference'  => 'nullable|string|max:255',
            'note'       => 'nullable|string|max:1000',
        ];
    }
}
