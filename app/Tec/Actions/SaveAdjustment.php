<?php

namespace App\Tec\Actions;

use Illuminate\Support\Facades\DB;
use App\Tec\Events\AdjustmentEvent;
use App\Models\Sma\Product\Adjustment;

class SaveAdjustment
{
    /**
     * Save adjustments with relationships
     *
     * @param  array<string, string>  $input
     * @param  Adjustment  $input
     */
    public function execute(array $data, Adjustment $adjustment = new Adjustment): Adjustment
    {
        // logger()->info('Adjustment form data: ', $data);

        $oldAdjustment = null;
        if ($adjustment?->id) {
            $oldAdjustment = $adjustment->load(['items.product', 'items.variations'])->replicateQuietly();
            $oldAdjustment->id = $adjustment->id;
        }

        DB::transaction(function () use ($data, &$adjustment) {
            $items = $data['items'];
            $attachments = $data['attachments'] ?? [];
            unset($data['attachments'], $data['items']);
            $data['total_items'] = collect($items)->count();
            $data['total_quantity'] = collect($items)->sum('quantity');

            $adjustment->fill($data)->save();

            foreach ($items as $item) {
                $variations = $item['variations'] ?? null;
                unset($item['variations'], $item['old_quantity']);
                $item['quantity'] = $variations ? collect($variations)->sum('quantity') : $item['quantity'];

                if (($item['id'] ?? null) && $ii = $adjustment->items->where('id', $item['id'])->first()) {
                    $ii->update($item);
                    if ($variations ?? null) {
                        $variationIds = [];
                        $syncVariations = [];
                        foreach ($variations as $variation) {
                            $variationIds[] = $variation['id'];
                            $syncVariations[$variation['id']] = ['quantity' => $variation['quantity']];
                        }
                        $ii->variations()->sync($syncVariations);
                    }
                } else {
                    $ii = $adjustment->items()->create($item);
                    if ($variations) {
                        $syncVariations = [];
                        foreach ($variations as $variation) {
                            $syncVariations[$variation['id']] = ['quantity' => $variation['quantity']];
                        }
                        $ii->variations()->sync($syncVariations);
                    }
                }
            }

            $adjustment->saveAttachments($attachments);
        });

        $adjustment->refresh()->loadMissing(['items.product', 'items.variations']);
        event(new AdjustmentEvent($adjustment, $oldAdjustment));

        return $adjustment;
    }
}
