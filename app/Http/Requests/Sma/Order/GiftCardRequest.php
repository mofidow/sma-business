<?php

namespace App\Http\Requests\Sma\Order;

use App\Models\Sma\People\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class GiftCardRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return $this->user()->can($this->route('gift_card') ? 'update-gift_cards' : 'create-gift_cards');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'number'           => 'required|max:16|unique:gift_cards,number,' . $this->route('gift_card')?->id,
            'amount'           => $this->route('gift_card')?->id ? 'nullable' : 'required',
            'expiry_date'      => 'nullable|date:Y-m-d',
            'customer_id'      => 'nullable|exists:customers,id',
            'details'          => 'nullable',
            'award_points'     => 'nullable|numeric|min:1',
            'use_award_points' => 'nullable|boolean',
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
        $data = $this->validator->validated();

        if ($this->route('gift_card')?->id) {
            unset($data['amount'], $data['balance'], $data['award_points'], $data['use_award_points']);
        } elseif (($data['customer_id'] ?? null) && ($data['award_points'] ?? null) && ($data['use_award_points'] ?? null)) {
            $customer = Customer::find($data['customer_id']);
            if ($customer && $customer->points < $data['award_points']) {
                throw ValidationException::withMessages([
                    'award_points' => __('The award points cannot be more than customer points.'),
                ]);
            }
        }

        return $data;
    }
}
