<?php

namespace App\Http\Requests\Sma\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class AccountTransferRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-account-transfers');
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'from_account_id' => 'required|exists:accounts,id',
            'to_account_id'   => 'required|exists:accounts,id|different:from_account_id',
            'amount'          => 'required|numeric|min:0.01',
            'reference'       => 'nullable|string|max:255',
            'note'            => 'nullable|string|max:1000',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'to_account_id.different' => __('The source and destination accounts must be different.'),
        ];
    }
}
