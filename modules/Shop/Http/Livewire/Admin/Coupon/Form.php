<?php

namespace Modules\Shop\Http\Livewire\Admin\Coupon;

use Livewire\Component;
use Modules\Shop\Models\ShopCoupon;

class Form extends Component
{
    public array $form;

    public ShopCoupon $coupon;

    public function mount(?ShopCoupon $coupon)
    {
        if (auth()->guest()) {
            return redirect()->route('login');
        }

        $this->coupon = $coupon->id ? $coupon : new ShopCoupon;
        $this->coupon->active = ! $this->coupon->id ? true : (bool) $this->coupon->active;
        $this->form = $this->coupon->toArray();
    }

    public function render()
    {
        return view('shop::pages.admin.coupon.form');
    }

    public function save()
    {
        $this->validate();

        if ($this->coupon->id) {
            $this->coupon->update($this->form);
        } else {
            $this->coupon = ShopCoupon::create($this->form);
        }

        cache()->forget('shipping_methods');
        session()->flash('success', __(':model has been :action', ['model' => __('Coupon'), 'action' => $this->coupon->id ? __('updated') : __('created')]));
        $this->redirectRoute('shop.coupons');
    }

    protected function rules()
    {
        return [
            'form.code'        => 'required|alpha_dash|unique:shop_coupons,code,' . $this->coupon->id,
            'form.discount'    => 'required|numeric',
            'form.expiry_date' => 'nullable|date',
            'form.allowed'     => 'nullable|integer',
            'form.active'      => 'nullable|boolean',
        ];
    }
}
