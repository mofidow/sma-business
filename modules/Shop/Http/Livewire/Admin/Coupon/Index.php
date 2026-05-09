<?php

namespace Modules\Shop\Http\Livewire\Admin\Coupon;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Shop\Models\ShopCoupon;

class Index extends Component
{
    use WithPagination;

    public function mount()
    {
        if (auth()->guest()) {
            return redirect()->route('login');
        }
    }

    public function render()
    {
        $coupons = ShopCoupon::paginate()->withQueryString();

        return view('shop::pages.admin.coupon.index', ['coupons' => $coupons]);
    }

    public function removeCoupon($id)
    {
        ShopCoupon::findOrFail($id)->delete();
        $this->dispatch('notify',
            type: 'success',
            content: __(':model has been :action', ['model' => __('Coupon'), 'action' => __('deleted')]),
        );
    }
}
