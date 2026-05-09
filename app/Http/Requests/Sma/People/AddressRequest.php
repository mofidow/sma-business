<?php

namespace App\Http\Requests\Sma\People;

use App\Tec\Rules\AddressState;
use App\Models\Sma\People\Address;
use App\Tec\Rules\ExtraAttributes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class AddressRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('address') ? 'update-addresses' : 'create-addresses');
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
            'name'        => 'required',
            'phone'       => 'required',
            'company'     => 'nullable',
            'email'       => 'nullable|email',
            'customer_id' => 'required|exists:customers,id',

            'lot_no'         => 'nullable',
            'street'         => 'nullable',
            'address_line_1' => 'required',
            'address_line_2' => 'nullable',
            'city'           => 'required',
            'postal_code'    => 'required',
            'state_id'       => [new AddressState],
            'country_id'     => 'required|exists:countries,id',

            'extra_attributes' => ['nullable', new ExtraAttributes('address')],
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
            $address = Address::where('name', $data['name'])
                ->where('phone', $data['phone'])->where('customer_id', $data['customer_id'])->first();
            if ($address?->id != $this->route('address')?->id && $address?->address_line_1 == $this->route('address')?->address_line_1) {
                throw ValidationException::withMessages(['name' => __('Address name with phone already exists')]);
            }
        } elseif (! empty($data['email'])) {
            $address = Address::where('name', $data['name'])
                ->where('email', $data['email'])->where('customer_id', $data['customer_id'])->first();
            if ($address?->id != $this->route('address')?->id && $address?->address_line_1 == $this->route('address')?->address_line_1) {
                throw ValidationException::withMessages(['name' => __('Address name with email already exists')]);
            }
        }

        $data['state_id'] = $data['state_id'] ?: null;

        return $data;
    }
}
