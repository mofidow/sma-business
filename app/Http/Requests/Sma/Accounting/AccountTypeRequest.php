<?php

namespace App\Http\Requests\Sma\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class AccountTypeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('account_type') ? 'update-account-types' : 'create-account-types');
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'        => 'required|string|max:255|unique:account_types,name,' . $this->route('account_type')?->id,
            'code'        => 'nullable|string|max:55|unique:account_types,code,' . $this->route('account_type')?->id,
            'description' => 'nullable|string|max:500',
            'active'      => 'nullable|boolean',
        ];
    }
}
