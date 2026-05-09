<?php

namespace Modules\Shop\Http\Livewire\Components\Cart;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Sma\Product\Stock;
use App\Models\Sma\Product\Variation;
use Modules\Shop\Models\ShopCartItem;
use Modules\Shop\Tec\Actions\FreeItem;

class View extends Component
{
    use CartItemHelper;

    #[On('cart-updated')]
    public function render()
    {
        $this->prepare();

        return view('shop::components.cart.view');
    }

    public function updateQty($productId, $qty, $variation_id = null)
    {
        $shop_settings = get_settings('general');
        $cartItem = ShopCartItem::where('cart_id', $this->cartId)->where('product_id', $productId)->whereNull('oId')->first();
        if ($variation_id) {
            $variation_stock = Stock::ofStore($shop_settings['store_id'] ?? null)->ofVariation($variation_id)->first();
            if ($variation_stock->balance < $qty) {
                $variation = Variation::without('stock')->find($variation_id);
                $this->dispatch('notify',
                    type: 'error',
                    content: __('Only :quantity quantity available for :product.', ['quantity' => (float) $variation_stock->balance, 'product' => $cartItem->product->name . ' (' . meta_array_to_string($variation->meta) . ')']),
                );

                return false;
            }

            $quantity = 0;
            $selected = $cartItem->selected;
            foreach ($selected['variations'] as &$variation) {
                if ($variation['id'] == $variation_id) {
                    $variation['quantity'] = $qty;
                }
                $quantity += $variation['quantity'];
            }
            $cartItem->update(['quantity' => $quantity, 'selected' => $selected]);
            FreeItem::update($cartItem->product_id, $quantity, $this->cartId);
        } else {
            $stock = Stock::ofStore($shop_settings['store_id'] ?? null)->ofProduct($productId)->first();

            if ($stock->balance < $qty) {
                $this->dispatch('notify',
                    type: 'error',
                    content: __('Only :quantity quantity available for :product.', ['quantity' => (float) $stock->balance, 'product' => $cartItem->product->name]),
                );

                return false;
            }

            ShopCartItem::where('cart_id', $this->cartId)->where('product_id', $productId)->whereNull('oId')->update(['quantity' => $qty]);
            FreeItem::update($productId, $qty, $this->cartId);
        }
        cache()->forget('cart' . $this->cartId);
        $this->dispatch('cart-updated', cart_items_quantity($this->cartId));
        $this->dispatch('notify',
            type: 'success',
            content: __('Cart has been updated.'),
        );
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

    public function removeAll()
    {
        ShopCartItem::where('cart_id', $this->cartId)->delete();
        cache()->forget('cart' . $this->cartId);
        $this->dispatch('cart-updated', cart_items_quantity($this->cartId));
        $this->dispatch('notify',
            type: 'success',
            content: __('Your shopping cart has been cleared.'),
        );
    }

    public function removePromoItem($productId)
    {
        ShopCartItem::where('cart_id', $this->cartId)->where('oId', $productId)->delete();
    }
}
