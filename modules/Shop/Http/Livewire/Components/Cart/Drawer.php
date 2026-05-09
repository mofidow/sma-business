<?php

namespace Modules\Shop\Http\Livewire\Components\Cart;

use Livewire\Component;
use Livewire\Attributes\On;
use Modules\Shop\Models\ShopCartItem;

class Drawer extends Component
{
    public $cartId;

    #[On('cart-updated')]
    public function render()
    {
        // $this->prepare();

        $this->cartId = request()->cookie('cart_id');

        return view('shop::components.cart.drawer', [
            'cart_items' => ShopCartItem::where('cart_id', $this->cartId)
                ->with(['product', 'product.variations'])->get(),
        ]);
    }

    public function removeItem($productId, $variation_id = null)
    {
        if ($variation_id) {
            $cartItem = ShopCartItem::where('cart_id', $this->cartId)->where('product_id', $productId)->first();
            $quantity = 0;
            $variations = [];
            foreach ($cartItem->selected['variations'] as $variation) {
                if ($variation['id'] != $variation_id) {
                    $variations[] = $variation;
                    $quantity += $variation['quantity'];
                }
            }
            if ($quantity) {
                $selected = ['variations' => $variations];
                $cartItem->update(['quantity' => $quantity, 'selected' => $selected]);
            } else {
                $this->removePromoItem($productId);
                $cartItem->delete();
            }
        } else {
            $this->removePromoItem($productId);
            ShopCartItem::where('cart_id', $this->cartId)->where('product_id', $productId)->delete();
        }
        cache()->forget('cart' . $this->cartId);
        $this->dispatch('cart-updated', cart_items_quantity($this->cartId));
        $this->dispatch('notify',
            type: 'success',
            content: __('Item has been removed from your cart.'),
        );
    }

    public function removePromoItem($productId)
    {
        ShopCartItem::where('cart_id', $this->cartId)->where('oId', $productId)->delete();
    }

    // public function setCartId($cartId)
    // {
    //     $this->cartId = $cartId;
    //     session(['cart_id' => $this->cartId]);
    // }

    // public function removeItem($productId)
    // {
    //     ShopCartItem::where('cart_id', $this->cartId)->where('oId', $productId)->delete();
    //     ShopCartItem::where('cart_id', $this->cartId)->where('product_id', $productId)->delete();
    //     cache()->forget('cart' . $this->cartId);
    //     $this->dispatch('cart-updated', cart_items_quantity($this->cartId));
    //     $this->dispatch('notify',
    //         type: 'success',
    //         content: __('Item has been removed from your cart.'),
    //     );
    // }
}
