<?php

namespace Modules\Shop\Http\Livewire\Components\Cart;

use Livewire\Component;
use Modules\Shop\Models\ShopWishlist;

class AddToWishlist extends Component
{
    public $productId;

    public $style;

    public function mount($productId, $style = 'icon')
    {
        $this->style = $style;
        $this->productId = $productId;
    }

    public function render()
    {
        return view('shop::components.cart.add-to-wishlist');
    }

    public function submit()
    {
        if (auth()->guest()) {
            return redirect()->route('login');
        }

        $data = ['product_id' => $this->productId, 'user_id' => auth()->id()];
        if (! ShopWishlist::where($data)->exists()) {
            ShopWishlist::create($data);
        }

        $this->dispatch('notify',
            type: 'success',
            content: __('Product has been added to your wishlist.'),
        );
    }
}
