<?php

namespace App\Tec\Services;

use App\Models\Sma\Product\Product;

class OrderCalculator
{
    // Calculate order and prepare data
    public static function calculate(array $data, $calc_on = 'price')
    {
        $coupon = $data['coupon'] ?? null;

        $itemIds = collect($data['items'])->pluck('product_id');
        $products = Product::with('unit.subunits')
            ->whereIn('id', $itemIds)->get(['id', 'tax_included', 'unit_id']);

        $items = collect($data['items'])->transform(function ($item) use ($products, $calc_on, $coupon) {
            $product = $products->where('id', $item['product_id'])->first();

            $item['product_unit'] = $product->unit;
            $item['tax_included'] = $product->tax_included;

            if ($coupon) {
                $item['discount'] = $coupon->discount . '%';
            }
            $item = OrderItemCalculator::calculateTotal($item, $calc_on);

            return $item;
        });

        $data['total'] = format_decimal($items->sum('total'));
        $data['total_items'] = format_decimal($items->count());
        $data['subtotal'] = format_decimal($items->sum('subtotal'));
        $data['total_quantity'] = format_decimal_qty($items->sum('base_quantity'));
        $data['total_tax_amount'] = format_decimal($items->sum('total_tax_amount'));
        $data['total_discount_amount'] = format_decimal($items->sum('total_discount_amount'));
        $data['grand_total'] = format_decimal($items->sum('total')) + ($data['shipping'] ?? 0);
        $data['items'] = $items->all();

        if ($calc_on == 'price') {
            $data['total_cost'] = $items->sum('total_cost');
        }

        if ($coupon) {
            $data['shop_coupon_id'] = $coupon->id;
        }

        return $data;
    }
}
