<?php

namespace Modules\Shop\Http\Livewire\Components\Cart;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\Shop\Models\ShopWishlist;

class Wishlist extends Component
{
    use WithPagination;

    public function render()
    {
        return view('shop::components.cart.wishlist', [
            'products' => ShopWishlist::with(
                'product:id,code,name,price,photo,description,slug'
            )->ofUser()->latest()->paginate()->withQueryString(),
        ]);
    }

    public function removeProduct($id)
    {
        ShopWishlist::ofUser()->where('id', $id)->delete();
        $this->dispatch('notify',
            type: 'success',
            content: __('Product has been removed from your wishlist.'),
        );
    }

    public function removeAll()
    {
        ShopWishlist::ofUser()->delete();
        $this->dispatch('notify',
            type: 'success',
            content: __('Your wishlist has been cleared.'),
        );
    }
}
