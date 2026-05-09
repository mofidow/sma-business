<?php

namespace App\Tec\Actions;

use Illuminate\Support\Facades\DB;
use App\Tec\Events\ReturnOrderEvent;
use App\Models\Sma\Order\ReturnOrder;
use Plugins\FiscalServices\FiscalServiceJob;

class SaveReturnOrder
{
    /**
     * Save return_orders with relationships
     *
     * @param  array<string, string>  $input
     * @param  ReturnOrder  $input
     */
    public function execute(array $data, ReturnOrder $return_order = new ReturnOrder): ReturnOrder
    {
        // logger()->info('ReturnOrder form data: ', $data);

        $oldReturnOrder = null;
        if ($return_order?->id) {
            $oldReturnOrder = $return_order->load([
                'store', 'customer', 'supplier', 'items.product', 'items.variations',
            ])->replicateQuietly();
            $oldReturnOrder->id = $return_order->id;
        }

        DB::transaction(function () use ($data, &$return_order) {
            $items = $data['items'];
            $attachments = $data['attachments'] ?? [];
            unset($data['attachments'], $data['items']);

            $return_order->fill($data)->save();

            foreach ($items as $item) {
                $taxes = $item['taxes'] ?? [];
                $variations = $item['variations'] ?? null;
                unset($item['taxes'],$item['variations'], $item['old_quantity'], $item['tax_included']);
                $item['quantity'] = $variations ? collect($variations)->sum('quantity') : $item['quantity'];

                if (($item['id'] ?? null) && $ii = $return_order->items->where('id', $item['id'])->first()) {
                    $ii->update($item);
                    if ($variations ?? null) {
                        $variationIds = [];
                        $syncVariations = [];
                        foreach ($variations as $variation) {
                            $id = $variation['id'];
                            $variationIds[] = $id;
                            unset($variation['id'], $variation['old_quantity'], $variation['tax_included']);
                            $syncVariations[$id] = $variation;
                        }
                        $ii->variations()->sync($syncVariations);
                    }
                    $ii->taxes()->sync($taxes);
                } else {
                    $ii = $return_order->items()->create($item);
                    if ($variations) {
                        $syncVariations = [];
                        foreach ($variations as $variation) {
                            $id = $variation['id'];
                            unset($variation['id'], $variation['old_quantity'], $variation['tax_included']);
                            $syncVariations[$id] = $variation;
                        }
                        $ii->variations()->sync($syncVariations);
                    }
                    $ii->taxes()->sync($taxes);
                }
            }

            $return_order->saveAttachments($attachments);
        });

        $return_order->refresh()->loadMissing([
            'store', 'customer', 'supplier', 'items.product', 'items.variations',
        ]);
        event(new ReturnOrderEvent($return_order, $oldReturnOrder));

        if ($return_order->type == 'Sale') {
            FiscalServiceJob::dispatchNewReturnSale($return_order);
        }

        return $return_order;
    }
}
