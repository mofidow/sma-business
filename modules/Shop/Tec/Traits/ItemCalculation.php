<?php

namespace Modules\Shop\Tec\Traits;

use Modules\MPS\Actions\TaxAction;
use Modules\MPS\Actions\DiscountAction;

trait ItemCalculation
{
    public function doCalculation($qty, $selected, array $address = [])
    {
        $user = auth()->user();
        $v = ['net_price' => 0, 'discount_amount' => 0, 'tax_amount' => 0, 'total_tax_amount' => 0, 'total_discount_amount' => 0, 'applicable_taxes' => []];

        $this->price = $this->shopStock->isNotEmpty() && $this->shopStock->first()->price ? $this->shopStock->first()->price : $this->getPrice();
        $this->applicable_taxes = applicable_taxes($this->getTaxes(), $user ? $user->customer?->state : ($address ? $address['state'] : null));

        if (isset($selected['variations']) && ! empty($selected['variations'])) {
            foreach ($selected['variations'] as &$sv) {
                $vv = ['discount_amount' => 0, 'tax_amount' => 0, 'total_tax_amount' => 0, 'total_discount_amount' => 0];
                if (! $this->oId) {
                    $variantPrice = $sv->getPrice() ?? $this->price;
                    $vv['discount_amount'] = $this->applyDiscount($variantPrice, $qty);
                    $vv['net_price'] = $variantPrice - $vv['discount_amount'];
                    $vv['taxes'] = (new TaxAction)->calculate($this->applicable_taxes ?? false, $vv['net_price'], $sv['quantity'], $this->tax_included, true);

                    $vv['tax_amount'] += $vv['taxes'] ? formatDecimal($vv['taxes']->sum('amount')) : 0;
                    $vv['total_tax_amount'] += $vv['tax_amount'] ? formatDecimal($vv['tax_amount'] * $sv['quantity']) : 0;
                    $vv['total_discount_amount'] += $vv['discount_amount'] ? formatDecimal($vv['discount_amount'] * $sv['quantity']) : 0;
                }

                $sv['calculation'] = collect($vv);
                $v['net_price'] += $vv['net_price'];
                $v['tax_amount'] += $vv['tax_amount'];
                $v['total_tax_amount'] += $vv['total_tax_amount'];
                $v['total_discount_amount'] += $vv['total_discount_amount'];
            }
        } elseif ($this->portions && $this->portions->isNotEmpty()) {
            foreach ($this->portions as $sp) {
                $vv = ['discount_amount' => 0, 'tax_amount' => 0, 'total_tax_amount' => 0, 'total_discount_amount' => 0];
                if (! $this->oId) {
                    $portionPrice = $sp->getPrice() ?? $this->price;
                    $vv['discount_amount'] = $this->applyDiscount($portionPrice, $qty);
                    $vv['net_price'] = $portionPrice - $vv['discount_amount'];
                    $vv['taxes'] = (new TaxAction)->calculate($this->applicable_taxes ?? false, $vv['net_price'], 1, $this->tax_included, true);

                    $vv['tax_amount'] += $vv['taxes'] ? formatDecimal($vv['taxes']->sum('amount')) : 0;
                    $vv['total_tax_amount'] += $vv['tax_amount'] ? formatDecimal($vv['tax_amount']) : 0;
                    $vv['total_discount_amount'] += $vv['discount_amount'] ? formatDecimal($vv['discount_amount']) : 0;
                }

                $sp->calculation = collect($vv);
                $v['net_price'] += $vv['net_price'];
                $v['tax_amount'] += $vv['tax_amount'];
                $v['total_tax_amount'] += $vv['total_tax_amount'];
                $v['total_discount_amount'] += $vv['total_discount_amount'];
            }
        } elseif (! $this->oId) {
            $v['discount_amount'] = $this->applyDiscount($this->price, $qty);
            $v['net_price'] = $this->price - $v['discount_amount'];
            $v['taxes'] = (new TaxAction)->calculate($this->applicable_taxes ?? false, $v['net_price'], $qty, $this->tax_included, true);

            $v['tax_amount'] += $v['taxes'] ? formatDecimal($v['taxes']->sum('amount')) : 0;
            $v['total_tax_amount'] += $v['tax_amount'] ? formatDecimal($v['tax_amount'] * $qty) : 0;
            $v['total_discount_amount'] += $v['discount_amount'] ? formatDecimal($v['discount_amount'] * $qty) : 0;
        }
        $this->selected = $selected;
        $this->calculation = collect($v);

        return $this;
    }

    protected function applyDiscount($price, $qty)
    {
        $discount_amount = 0;
        if ($this->validPromotions->isNotEmpty()) {
            foreach ($this->validPromotions as $promotion) {
                if ($promotion->type === 'simple') {
                    $discount_amount += (new DiscountAction)->calculate($promotion->discount, $price);
                } elseif ($promotion->type === 'advance' && $promotion->quantity_to_buy <= $qty) {
                    $discount_amount += (new DiscountAction)->calculate($promotion->discount, $price - $discount_amount);
                    // } elseif ($promotion->type == 'SXGD') {
                    // $promotion->amount_to_spend
                    // $discount_amount += (new DiscountAction)->calculate($promotion->discount, $price - $discount_amount);
                }
            }
            $this->allPromotions = $this->validPromotions;
        }
        foreach ($this->categories as $category) {
            foreach ($category->validPromotions as $promotion) {
                if ($promotion->type === 'simple') {
                    $discount_amount += (new DiscountAction)->calculate($promotion->discount, $price);
                } elseif ($promotion->type === 'advance' && $promotion->quantity_to_buy <= $qty) {
                    $discount_amount += (new DiscountAction)->calculate($promotion->discount, $price - $discount_amount);
                    // } elseif ($promotion->type == 'SXGD') {
                    // $promotion->amount_to_spend
                    // $discount_amount += (new DiscountAction)->calculate($promotion->discount, $price - $discount_amount);
                }
            }
            $this->allPromotions = $this->validPromotions->merge($category->validPromotions);
        }

        // Shop Coupon
        $coupon = session('coupon', []);
        if ($coupon) {
            $discount_amount += (new DiscountAction)->calculate($coupon->discount, $this->price);
        }

        return $discount_amount;
    }
}
