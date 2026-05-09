<?php

namespace App\Tec\Actions;

use App\Tec\Events\PurchaseEvent;
use App\Models\Sma\Order\Purchase;
use App\Models\Sma\Product\Serial;
use Illuminate\Support\Facades\DB;

class SavePurchase
{
    /**
     * Save purchases with relationships
     *
     * @param  array<string, string>  $input
     * @param  Purchase  $input
     */
    public function execute(array $data, Purchase $purchase = new Purchase): Purchase
    {
        // logger()->info('Purchase form data: ', $data);

        $oldPurchase = null;
        if ($purchase?->id) {
            $oldPurchase = $purchase->load([
                'store', 'supplier', 'items.product', 'items.variations',
            ])->replicateQuietly();
            $oldPurchase->id = $purchase->id;
        }

        DB::transaction(function () use ($data, &$purchase) {
            $items = $data['items'];
            $attachments = $data['attachments'] ?? [];
            unset($data['attachments'], $data['items']);

            $purchase->fill($data)->save();

            foreach ($items as $item) {
                $taxes = $item['taxes'] ?? [];
                $serials = $item['serials'] ?? null;
                $variations = $item['variations'] ?? null;
                unset($item['taxes'], $item['serials'], $item['variations'], $item['old_quantity'], $item['tax_included']);
                $item['quantity'] = $variations ? collect($variations)->sum('quantity') : $item['quantity'];

                if (($item['id'] ?? null) && $ii = $purchase->items->where('id', $item['id'])->first()) {
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
                    $this->syncPurchaseSerials($ii, $serials, $purchase);
                } else {
                    $ii = $purchase->items()->create($item);
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
                    $this->syncPurchaseSerials($ii, $serials, $purchase);
                }
            }

            $purchase->saveAttachments($attachments);
        });

        $purchase->refresh()->loadMissing([
            'store', 'supplier', 'items.product', 'items.variations',
        ]);
        event(new PurchaseEvent($purchase, $oldPurchase));

        return $purchase;
    }

    /**
     * Sync serials for a purchase item.
     */
    private function syncPurchaseSerials($purchaseItem, ?array $serials, Purchase $purchase): void
    {
        if ($serials === null) {
            return;
        }

        // Remove old serials for this item
        Serial::where('purchase_item_id', $purchaseItem->id)->delete();
        $purchaseItem->serials()->detach();

        // Create new serials
        $serialIds = [];
        foreach ($serials as $serialNumber) {
            $serial = Serial::create([
                'number'           => $serialNumber,
                'purchase_id'      => $purchase->id,
                'purchase_item_id' => $purchaseItem->id,
                'product_id'       => $purchaseItem->product_id,
            ]);
            $serialIds[] = $serial->id;
        }

        $purchaseItem->serials()->sync($serialIds);
    }
}
