<?php

namespace App\Http\Requests\Sma\Order;

use App\Models\User;
use App\Models\Sma\Order\GiftCard;
use App\Tec\Rules\ExtraAttributes;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Container\Attributes\CurrentUser;
use Illuminate\Container\Attributes\RouteParameter;

class PaymentRequest extends FormRequest
{
    public function __construct(
        #[CurrentUser] public User $user,
        #[RouteParameter('payment')] public $payment = null
    ) {}

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return $this->user->can($this->payment ? 'update-payments' : 'create-payments');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules()
    {
        return [
            'date'        => 'required|date',
            'customer_id' => 'nullable|required_if:payment_for,Customer|exists:customers,id',
            'supplier_id' => 'nullable|required_if:payment_for,Supplier|exists:suppliers,id',
            'sale_id'     => 'nullable',
            'purchase_id' => 'nullable',
            'reference'   => 'nullable|string|max:36|unique:payments,reference,' . $this->payment?->id,
            'amount'      => 'required|numeric',
            'method'      => 'required|string',
            'method_data' => 'nullable|array',
            'details'     => 'nullable',
            'payment_for' => 'required|in:Customer,Supplier',
            'cc_slip'     => 'accepted_if:method,Card Terminal',

            'extra_attributes' => ['nullable', new ExtraAttributes('payment')],
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

        // check if gift card for customer and balance
        if ($data['method'] == 'Gift Card') {
            if (! ($data['method_data']['gift_card_no'] ?? null)) {
                throw ValidationException::withMessages(['method_data.gift_card_no' => trans('validation.required', ['attribute' => __('gift card number')])]);
            }

            $giftCard = GiftCard::where('number', $data['method_data']['gift_card_no'])->first();
            if (! $giftCard) {
                throw ValidationException::withMessages(['method_data.gift_card_no' => trans('validation.exists', ['attribute' => __('gift card number')])]);
            } elseif ($giftCard->customer_id && $giftCard->customer_id != $data['customer_id']) {
                throw ValidationException::withMessages(['method_data.gift_card_no' => __('Gift card does not belong to this customer.')]);
            } elseif ($giftCard->balance < $data['amount']) {
                throw ValidationException::withMessages(['amount' => __('Gift card does not have enough balance.')]);
            }
        }

        $data['received'] = true;

        return $data;
    }
}
