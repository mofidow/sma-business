<?php

namespace Modules\Shop\Http\Livewire\Admin\ShippingMethod;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Shop\Models\ShopShippingMethod;

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
        $shipping_methods = ShopShippingMethod::paginate()->withQueryString();

        return view('shop::pages.admin.shipping_method.index', ['shipping_methods' => $shipping_methods]);
    }

    public function removeShippingMethod($id)
    {
        ShopShippingMethod::findOrFail($id)->delete();
        $this->dispatch('notify',
            type: 'success',
            content: __(':model has been :action', ['model' => __('Shipping Method'), 'action' => __('deleted')]),
        );
    }
}
