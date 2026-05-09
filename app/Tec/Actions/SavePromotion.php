<?php

namespace App\Tec\Actions;

use App\Models\Sma\Product\Promotion;

class SavePromotion
{
    /**
     * Save transfers with relationships
     *
     * @param  array<string, string>  $input
     * @param  Promotion  $input
     */
    public function execute(array $data, Promotion $promotion = new Promotion): Promotion
    {
        $stores = $data['stores'] ?? [];
        $products = $data['products'] ?? [];
        $categories = $data['categories'] ?? [];
        unset($data['categories'], $data['products'], $data['stores']);

        if ($data['product_id_to_buy'] ?? null) {
            $products[] = $data['product_id_to_buy'];
        }

        $promotion->fill($data)->save();
        $promotion->products()->sync($products);
        $promotion->categories()->sync($categories);
        $promotion->stores()->sync($stores);

        return $promotion;
    }
}
