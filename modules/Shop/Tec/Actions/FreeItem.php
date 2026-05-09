<?php

namespace Modules\Shop\Tec\Actions;

use App\Models\Sma\Product\Promotion;
use Modules\Shop\Models\ShopCartItem;
use Modules\Shop\Http\Livewire\Components\Cart\Add;

class FreeItem
{
    public static function check($product, $freeItem)
    {
        $promotion = Promotion::valid()->where('product_id_to_buy', $product->product_id)->where('product_id_to_get', $freeItem->product_id)->where('type', 'BXGY')->first();

        if ($promotion) {
            return $promotion->quantity_to_buy <= $product->quantity && $freeItem->quantity == floor($product->quantity / $promotion->quantity_to_buy);
        }

        return false;
    }

    public static function update($productId, $quantity, $cartId = null)
    {
        if (! $cartId) {
            $cartId = session('cart_id');
        }
        $promotions = Promotion::valid()->where('product_id_to_buy', $productId)->where('type', 'BXGY')->get();
        if ($promotions->isNotEmpty()) {
            $freeItems = ShopCartItem::where('cart_id', $cartId)->where('oId', $productId)->get();
            foreach ($promotions as $promotion) {
                if ($freeItems->isNotEmpty() && $freeItem = $freeItems->where('product_id', $promotion->product_id_to_get)->first()) {
                    if ($promotion->quantity_to_buy <= $quantity) {
                        $freeItem->update(['quantity' => floor($quantity / $promotion->quantity_to_buy)]);
                    } elseif ($promotion->product_id_to_get == $freeItem->product_id) {
                        $freeItem->delete();
                    }
                } elseif ($promotion->quantity_to_buy <= $quantity) {
                    $addCom = new Add;
                    $addCom->add($promotion->product_id_to_get, floor($quantity / $promotion->quantity_to_buy), $productId)->submit();
                }
            }
        }
    }
}
