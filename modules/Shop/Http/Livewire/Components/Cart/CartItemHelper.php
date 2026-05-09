<?php

namespace Modules\Shop\Http\Livewire\Components\Cart;

use Modules\Shop\Models\ShopCoupon;
use App\Tec\Services\OrderCalculator;
use Modules\Shop\Models\ShopCartItem;
use Modules\Shop\Models\ShopShippingMethod;

trait CartItemHelper
{
    public $available_shipping;

    public $cart_items;

    public $cartId;

    public $coupon;

    public $data = [];

    public $form = [
        'details'    => '',
        'address_id' => '',
        'address'    => [
            'name'           => '',
            'phone'          => '',
            'company'        => '',
            'email'          => '',
            'lot_no'         => '',
            'street'         => '',
            'address_line_1' => '',
            'address_line_2' => '',
            'city'           => '',
            'postal_code'    => '',
            'state_id'       => '',
            'country_id'     => '',
        ],
        'coupon_code'             => '',
        'shop_shipping_method_id' => '',
        'payment_method'          => '',
    ];

    public $shipping_method;

    public $shipping_methods;

    public function mount()
    {
        $this->coupon = session('coupon', []);
        $this->available_shipping = collect([]);
        $this->cartId = request()->cookie('cart_id');
        $this->form['coupon_code'] = session('coupon_code', null);
        $this->form['shop_shipping_method_id'] = session('shop_shipping_method_id', null);
        $this->shipping_methods = cache()->rememberForever('shipping_methods', fn () => ShopShippingMethod::all());
        $this->shipping_method = $this->shipping_methods->where('id', $this->form['shop_shipping_method_id'])->first();
        $this->available_shipping = $this->shipping_methods;

        $this->cart_items = ShopCartItem::where('cart_id', $this->cartId)->with(['product', 'product.variations'])->get();
    }

    public function prepare()
    {
        if (request()->hasCookie('cart_id')) {
            $this->cartId = request()->cookie('cart_id');
        }

        $this->cart_items = $this->prepareItems();
    }

    public function prepareItems()
    {
        if (auth()->user() && $this->cartId) {
            ShopCartItem::ofUser()->update(['cart_id' => $this->cartId]);
        }
        $cart_items = ShopCartItem::select(['product_id', 'cart_id', 'quantity', 'user_id', 'selected', 'oId'])
            ->with('product:id,code,name,slug,price,photo,tax_included', 'product.taxes', 'product.priceGroup')->ofCart($this->cartId)->get();
        foreach ($cart_items as $cart_item) {
            $selected = ['variations' => []];
            if ($cart_item->product->variations->isNotEmpty()) {
                if ($cart_item->selected['variations'] ?? []) {
                    foreach ($cart_item->selected['variations'] as $sv) {
                        $variation = $cart_item->product->variations->where('id', $sv['id'])->first();
                        $variation->available = $variation->quantity;
                        $variation->quantity = $sv['quantity'];
                        $variation->price = $sv['price'] ?? 0;
                        $selected['variations'][] = $variation;
                    }
                }
            }
            $cart_item->product->oId = $cart_item->oId;
        }

        $items = $cart_items->map(function ($cart_item) {
            $item = $cart_item->product->toArray();
            $item['taxes'] = $cart_item->product->taxes?->pluck('id');
            $item['selected'] = $cart_item->selected;
            $item['product_id'] = $cart_item->product_id;
            $item['quantity'] = $cart_item->quantity;
            $item['cart_id'] = $cart_item->cart_id;
            $item['variations'] = $cart_item->selected['variations'] ?? [];
            $item['cost'] = 0;

            return $item;
        })->all();

        $this->data = OrderCalculator::calculate(['items' => $items, 'coupon' => $this->coupon]);

        return $cart_items;
    }

    public function applyCoupon($remove = false)
    {
        if ($remove) {
            $this->form['coupon_code'] = '';
            session()->forget('coupon');
            session()->forget('coupon_code');

            $this->dispatch('notify',
                type: 'success',
                content: __('Coupon has been removed.')
            );
        } else {
            if ($this->form['coupon_code']) {
                $this->coupon = ShopCoupon::where('code', $this->form['coupon_code'])->first();
                if ($this->coupon && ($this->coupon->expiry_date ? now()->parse($this->coupon->expiry_date)?->isPast() : false)) {
                    $this->form['coupon_code'] = '';
                    $this->dispatch('notify',
                        type: 'error',
                        content: __('Coupon has expired.')
                    );
                } elseif ($this->coupon) {
                    session(['coupon' => $this->coupon]);
                    // cache()->forget('cart' . $this->cartId);
                    session(['coupon_code' => $this->form['coupon_code']]);
                    $this->prepareItems();
                    $this->dispatch('notify',
                        type: 'success',
                        content: __('Coupon has been applied.')
                    );
                } else {
                    $this->form['coupon_code'] = '';
                    $this->dispatch('notify',
                        type: 'error',
                        content: __('Coupon not found, please try again with correct code.')
                    );
                }
            } else {
                session()->forget('coupon');
                session()->forget('coupon_code');
                $this->dispatch('notify',
                    type: 'error',
                    content: __('Please provide coupon code.'),
                );
            }
        }
    }
}
