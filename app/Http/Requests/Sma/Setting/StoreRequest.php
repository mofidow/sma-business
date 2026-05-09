<?php

namespace App\Http\Requests\Sma\Setting;

use App\Tec\Rules\AddressState;
use App\Tec\Rules\ExtraAttributes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can($this->route('store') ? 'update-stores' : 'create-stores');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name'           => 'required|string|unique:stores,name,' . $this->route('store')?->id,
            'email'          => 'required|email',
            'phone'          => 'required',
            'color'          => 'nullable',
            'header'         => 'nullable|string|max:1000',
            'footer'         => 'nullable|string|max:1000',
            'logo'           => 'nullable|mimes:jpg,jpeg,png,avif,webp|max:500',
            'price_group_id' => 'nullable|exists:price_groups,id',
            'account_id'     => 'bail|required|exists:accounts,id',
            'accounts'       => 'nullable|array',

            'lot_no'         => 'nullable',
            'street'         => 'nullable',
            'address_line_1' => 'nullable',
            'address_line_2' => 'nullable',
            'city'           => 'nullable',
            'postal_code'    => 'nullable',
            'state_id'       => [new AddressState],
            'country_id'     => 'required|exists:countries,id',
            'active'         => 'nullable|boolean',

            'telegram_user_id' => 'nullable|string',
            'extra_attributes' => ['nullable', new ExtraAttributes('store')],
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

        if ($this->has('logo') && $this->logo) {
            $data['logo'] = Storage::disk('asset')->url($this->logo->store('/images', 'asset'));
            if ($this->route('store')?->id && $this->route('store')?->logo) {
                Storage::disk('asset')->delete($this->route('store')->logo);
            }
        } else {
            unset($data['logo']);
        }

        $data['state_id'] = $data['state_id'] ?: null;

        return $data;
    }
}
