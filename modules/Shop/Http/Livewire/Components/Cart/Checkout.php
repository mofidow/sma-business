<?php

namespace Modules\Shop\Http\Livewire\Components\Cart;

use Livewire\Component;
use Livewire\Attributes\On;
use Nnjeim\World\Models\Country;
use App\Models\Sma\People\Address;
use Illuminate\Support\Facades\URL;
use Modules\Shop\Models\ShopCartItem;
use Modules\Shop\Tec\Actions\AddSale;
use Modules\Shop\Tec\Actions\FreeItem;
use Modules\Shop\Models\ShopShippingMethod;

class Checkout extends Component
{
    use CartItemHelper;

    public $shop_settings;

    public $addresses;

    public $countries;

    public $shipping_country;

    public function mount()
    {
        $user = auth()->user();
        $this->addresses = collect([]);
        $this->cartId = request()->cookie('cart_id');
        $this->shop_settings = get_settings('general');
        $this->countries = Country::with('states:id,name,country_id')->get();
        // if ($user && ! $user->customer_id) {
        //     $this->dispatch('notify',
        //         type: 'error',
        //         content: __('Employees are not allowed to place order from shop. If you are not staff member, please contact site admin.'),
        //     );

        //     return false;
        // } else
        if ($user && $user->customer_id && (! $user->customer?->country_id || ! $user->customer?->state_id)) {
            session()->flash('error', __('Please update your billing address first.'));

            return to_route('shop.billing');
        } elseif (! $user && ! ($this->shop_settings['guest_checkout'] ?? null)) {
            session()->flash('error', __('Please login to make order.'));

            return to_route('shop.signin');
        }

        $this->addresses = Address::ofUser($user->id)->get();
        if ($this->addresses->isNotEmpty()) {
            $this->form['address_id'] = $this->addresses->count() == 1 ? $this->addresses->first()->id : $this->addresses->where('default', 1)->first()?->id;
        }
    }

    public function render()
    {
        $user = auth()->user();
        if ($user && $user->customer_id && (! $user->customer->country_id || ! $user->customer->state_id)) {
            session()->flash('error', __('Please update your billing address first.'));

            return to_route('shop.billing');
        } elseif (! $user && ! ($this->shop_settings['guest_checkout'] ?? null)) {
            session()->flash('error', __('Please login to make order.'));

            return to_route('shop.signin');
        }

        $this->coupon = session('coupon', []);
        $this->form['coupon_code'] = session('coupon_code', null);
        $this->form['shop_shipping_method_id'] = session('shop_shipping_method_id', null);
        $this->shipping_methods = cache()->rememberForever('shipping_methods', fn () => ShopShippingMethod::all());
        $this->shipping_method = $this->shipping_methods->where('id', $this->form['shop_shipping_method_id'])->first();
        $this->available_shipping = $this->shipping_methods;

        if ($this->form['address_id']) {
            $address = $this->addresses->where('id', $this->form['address_id'])->first();
            $this->available_shipping = $this->shipping_methods->filter(function ($method) use ($address) {
                if ($address->state && $method->state && $address->state == $method->state) {
                    return true;
                } elseif ($address->country && $method->country && $address->country == $method->country) {
                    return true;
                }

                return ! $method->country;
            });
            if ($this->available_shipping && ! $this->available_shipping->where('id', $this->form['shop_shipping_method_id'])->first()) {
                $this->shipping_method = null;
                $this->form['shop_shipping_method_id'] = null;
                session()->forget('shipping_method');
                session()->forget('shop_shipping_method_id');
            }
        }

        $this->cart_items = ShopCartItem::where('cart_id', $this->cartId)->with(['product', 'product.variations'])->get();

        $this->prepare();

        return view('shop::components.cart.checkout');
    }

    #[On('address-added')]
    public function addressAdded()
    {
        $this->addresses = Address::ofUser()->get();
        if ($this->addresses->isNotEmpty()) {
            $this->form['address_id'] = $this->addresses->count() == 1 ? $this->addresses->first()->id : $this->addresses->where('default', 1)->first()->id;
        }
    }

    #[On('cart-updated')]
    public function cartUpdated()
    {
        $this->prepare();
    }

    #[On('goto-orders')]
    public function gotoOrders()
    {
        return to_route('shop.orders');
    }

    public function updated()
    {
        $this->shipping_country = $this->form['address']['country_id'] ? $this->countries->firstWhere('id', $this->form['address']['country_id']) : null;

        if ($this->form['address_id']) {
            $address = $this->addresses->where('id', $this->form['address_id'])->first();
            if ($address) {
                $this->available_shipping = $this->shipping_methods->filter(function ($method) use ($address) {
                    if ($address->state && $method->state && $address->state == $method->state) {
                        return true;
                    } elseif ($address->country && $method->country && $address->country == $method->country) {
                        return true;
                    }

                    return ! $method->country;
                });
                if ($this->form['shop_shipping_method_id'] && ! $this->available_shipping->where('id', $this->form['shop_shipping_method_id'])->first()) {
                    $this->form['shop_shipping_method_id'] = null;
                }
            }
        }
        if ($this->form['shop_shipping_method_id']) {
            $this->shipping_method = $this->available_shipping->where('id', $this->form['shop_shipping_method_id'])->first();
            session(['shipping_method' => $this->shipping_method ?? []]);
            session(['shop_shipping_method_id' => $this->form['shop_shipping_method_id']]);
        } else {
            $this->shipping_method = null;
            session()->forget('shipping_method');
            session()->forget('shop_shipping_method_id');
        }
    }

    public function submit()
    {
        $this->dispatch('goto-top');
        $this->validate();

        // $this->dispatch('notify',
        //     type: 'success',
        //     content: __('WIP: This page is still under development.'),
        // );

        $settings = get_settings('general');
        $guest_checkout = $settings['guest_checkout'];
        if (! $guest_checkout && my_unpaid_shop_orders() >= $settings['max_unpaid_orders']) {
            $this->dispatch('notify',
                type: 'error',
                content: __('You have reached maximum unpaid orders limit. Please settle your unpaid orders first by visiting orders menu.'),
            );
            // $this->confirm(__('Settle unpaid order'), [
            //     'cancelButtonText'  => __('No'),
            //     'onConfirmed'       => 'gotoOrders',
            //     'confirmButtonText' => __('Go to Orders'),
            //     'text'              => __('You have reached maximum unpaid orders limit.'),
            // ]);

            return false;
        }

        $this->prepareItems();
        if ($this->form['payment_method'] && ($this->form['payment_method'] == 'Balance' || $this->form['payment_method'] == 'Cash on Delivery')) {
            $user = auth()->user();
            if (! $user) {
                $this->addError('form.payment_method', __('Please login to use this payment method.'));
                $this->dispatch('notify',
                    type: 'error',
                    content: __('Please login to use this payment method.'),
                );

                return false;
            }
            if ($this->form['payment_method'] == 'Balance') {
                $balance = $user->customer?->balance ?? 0;
                if ($balance < $this->data['grand_total']) {
                    $this->addError('form.payment_method', __('You do not have balance to make this payment.'));
                    $this->dispatch('notify',
                        type: 'error',
                        content: __('You do not have balance to make this payment.'),
                    );

                    return false;
                }
            }
        }

        if ($freeItems = $this->cart_items->whereNotNull('oId')->where('oId', '!=', '')) {
            foreach ($freeItems as $freeItem) {
                $mainItem = $this->cart_items->where('product_id', $freeItem->oId)->first();
                $valid = FreeItem::check($mainItem, $freeItem);

                if (! $valid) {
                    ShopCartItem::where('product_id', $freeItem->product_id)->where('oId', $freeItem->oId)->delete();

                    // $this->addError('form.payment_method', __('Promotion has expired or changed for free item named :item and removed from cart.', ['item' => $freeItem->item->name]));
                    $this->dispatch('notify',
                        type: 'error',
                        content: __('Promotion has expired or changed for free item named :item and removed from cart.', ['item' => $freeItem->item->name]),
                    );

                    return false;
                    // return to_route('shop.cart')->with('error', __('Promotion has expired or changed for free item named :item and removed from cart.', ['item' => $freeItem->item->name]));
                }
            }
        }

        $sale = AddSale::fromCart($this->form, $this->cart_items, $this->shipping_methods);
        if ($sale->payment_method == 'PayPal') {
            session()->flash('success', __('Sale has been saved, we are redirecting you to PayPal.'));
            $this->dispatch('notify',
                type: 'success',
                content: __('Sale has been saved, we are redirecting you to PayPal.'),
            );
        } else {
            session()->flash('success', __('Sale has been saved, please proceed to payment.'));
            $this->dispatch('notify',
                type: 'success',
                content: __('Sale has been saved, please proceed to payment.'),
            );
        }

        if ($payment = $sale->directPendingPayments()->first()) {
            if (auth()->guest()) {
                $url = URL::signedRoute('shop.payment.guest', [
                    'type'    => 'pay',
                    'id'      => $payment->id,
                    'hash'    => $payment->hash,
                    'gateway' => $sale->payment_method,
                ]);

                return redirect()->away($url);
            }

            return to_route('shop.payment', [
                'type'    => 'pay',
                'id'      => $payment->id,
                'gateway' => $sale->payment_method,
            ]);
        }

        if (auth()->guest()) {
            $url = URL::signedRoute('shop.order.guest', ['id' => $sale->id, 'hash' => $sale->hash]);

            return redirect()->away($url);
        }

        return to_route('shop.order', $sale->id);
    }

    protected function rules()
    {
        if (auth()->user()) {
            $rules = [
                'form.address_id'              => 'required|exists:addresses,id',
                'form.shop_shipping_method_id' => 'required|exists:shop_shipping_methods,id',
                'form.payment_method'          => 'required|in:PayPal,Credit Card,Balance,Cash on Delivery,Others',
            ];
        } else {
            $rules = [
                'form.address.first_name'      => 'required|string',
                'form.address.last_name'       => 'required|string',
                'form.address.email'           => 'nullable|email',
                'form.address.phone'           => 'required',
                'form.address.address'         => 'required',
                'form.address.country'         => 'required',
                'form.address.state'           => 'required',
                'form.address.city'            => 'required',
                'form.address.postal_code'     => 'required',
                'form.shop_shipping_method_id' => 'required|exists:shipping_methods,id',
                'form.payment_method'          => 'required|in:PayPal,Credit Card,Balance,Cash on Delivery,Others',
            ];
        }

        return $rules;
    }
}
