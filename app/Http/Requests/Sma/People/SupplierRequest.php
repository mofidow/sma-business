<?php

namespace App\Http\Requests\Sma\People;

use App\Tec\Rules\AddressState;
use App\Tec\Rules\ExtraAttributes;
use App\Models\Sma\People\Supplier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class SupplierRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('supplier') ? 'update-suppliers' : 'create-suppliers');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $require_state = get_settings('require_state');

        return [
            'name'            => 'required',
            'phone'           => 'nullable',
            'company'         => 'nullable|unique:suppliers,name,' . $this->route('supplier')?->id,
            'email'           => 'nullable|email',
            'opening_balance' => 'nullable|numeric',
            'due_limit'       => 'nullable|numeric',

            'lot_no'         => 'nullable',
            'street'         => 'nullable',
            'address_line_1' => 'nullable',
            'address_line_2' => 'nullable',
            'city'           => 'nullable',
            'postal_code'    => 'nullable',
            'state_id'       => $require_state == 1 ? [new AddressState] : 'nullable|exists:states,id',
            'country_id'     => $require_state == 1 ? 'required|exists:countries,id' : 'nullable|exists:countries,id',

            'telegram_user_id' => 'nullable|string',
            'extra_attributes' => ['nullable', new ExtraAttributes('supplier')],
        ];
    }

    /**
     * Get the validated data from the request.
     *
     * @param  array|int|string|null  $key
     * @param  mixed  $default
     * @return mixed
     */
    public function validated($key = null, $default = null)
    {
        $data = parent::validated($key, $default);

        if (! empty($data['phone'])) {
            $supplier = Supplier::where('name', $data['name'])->where('phone', $data['phone'])->first();
            if ($supplier?->id != $this->route('supplier')?->id) {
                throw ValidationException::withMessages(['name' => __('Supplier name with phone already exists')]);
            }
        } elseif (! empty($data['email'])) {
            $supplier = Supplier::where('name', $data['name'])->where('email', $data['email'])->first();
            if ($supplier?->id != $this->route('supplier')?->id) {
                throw ValidationException::withMessages(['name' => __('Supplier name with email already exists')]);
            }
        }

        $data['company'] ??= $data['name'];
        $data['state_id'] = $data['state_id'] ?? null;

        return $data;
    }
}
