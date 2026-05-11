<?php

namespace App\Tec\Actions;

use Illuminate\Support\Facades\DB;
use App\Models\Sma\Order\Quotation;

class SaveQuotation
{
    /**
     * Save quotations with relationships
     *
     * @param  array<string, string>  $input
     * @param  Quotation  $input
     */
    public function execute(array $data, Quotation $quotation = new Quotation): Quotation
    {
        // logger()->info('Quotation form data: ', $data);

        DB::transaction(function () use ($data, &$quotation) {
            $items = $data['items'];
            $attachments = $data['attachments'] ?? [];
            unset($data['attachments'], $data['items'], $data['total_cost']);

            // OrderCalculator outputs item-level naming; parent table uses different names
            $data['sub_total']      = $data['subtotal'] ?? $data['sub_total'] ?? 0;
            $data['total_tax']      = $data['total_tax_amount'] ?? $data['total_tax'] ?? 0;
            $data['total_discount'] = $data['total_discount_amount'] ?? $data['total_discount'] ?? 0;
            unset($data['subtotal'], $data['total_tax_amount'], $data['total_discount_amount'],
                  $data['total'], $data['total_items'], $data['total_quantity']);

            $quotation->fill($data)->save();

            foreach ($items as $item) {
                $taxes = $item['taxes'] ?? [];
                $variations = $item['variations'] ?? null;
                unset($item['taxes'],$item['variations'], $item['cost'], $item['total_cost'], $item['tax_included']);
                $item['quantity'] = $variations ? collect($variations)->sum('quantity') : $item['quantity'];

                if (($item['id'] ?? null) && $ii = $quotation->items->where('id', $item['id'])->first()) {
                    $ii->update($item);
                    if ($variations ?? null) {
                        $variationIds = [];
                        $syncVariations = [];
                        foreach ($variations as $variation) {
                            $id = $variation['id'];
                            $variationIds[] = $id;
                            unset($variation['id'], $variation['cost'], $variation['tax_included']);
                            $syncVariations[$id] = $variation;
                        }
                        $ii->variations()->sync($syncVariations);
                    }
                    $ii->taxes()->sync($taxes);
                } else {
                    $ii = $quotation->items()->create($item);
                    if ($variations) {
                        $syncVariations = [];
                        foreach ($variations as $variation) {
                            $id = $variation['id'];
                            unset($variation['id'], $variation['cost'], $variation['tax_included']);
                            $syncVariations[$id] = $variation;
                        }
                        $ii->variations()->sync($syncVariations);
                    }
                    $ii->taxes()->sync($taxes);
                }
            }

            $quotation->saveAttachments($attachments);
        });

        return $quotation;
    }
}
