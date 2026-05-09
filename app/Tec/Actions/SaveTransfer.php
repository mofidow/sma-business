<?php

namespace App\Tec\Actions;

use App\Tec\Events\TransferEvent;
use Illuminate\Support\Facades\DB;
use App\Models\Sma\Product\Transfer;

class SaveTransfer
{
    /**
     * Save transfers with relationships
     *
     * @param  array<string, string>  $input
     * @param  Transfer  $input
     */
    public function execute(array $data, Transfer $transfer = new Transfer): Transfer
    {
        // logger()->info('Transfer form data: ', $data);

        $oldTransfer = null;
        if ($transfer?->id) {
            $oldTransfer = $transfer->load(['items.product', 'items.variations'])->replicateQuietly();
            $oldTransfer->id = $transfer->id;
        }

        DB::transaction(function () use ($data, &$transfer) {
            $items = $data['items'];
            $attachments = $data['attachments'] ?? [];
            unset($data['attachments'], $data['items']);
            $data['total_items'] = collect($items)->count();
            $data['total_quantity'] = collect($items)->sum('quantity');

            $transfer->fill($data)->save();

            foreach ($items as $item) {
                $variations = $item['variations'] ?? null;
                unset($item['variations'], $item['old_quantity']);
                $item['quantity'] = $variations ? collect($variations)->sum('quantity') : $item['quantity'];

                if (($item['id'] ?? null) && $ii = $transfer->items->where('id', $item['id'])->first()) {
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
                    $ii = $transfer->items()->create($item);
                    if ($variations) {
                        $syncVariations = [];
                        foreach ($variations as $variation) {
                            $syncVariations[$variation['id']] = ['quantity' => $variation['quantity']];
                        }
                        $ii->variations()->sync($syncVariations);
                    }
                }
            }

            $transfer->saveAttachments($attachments);
        });

        $transfer->refresh()->loadMissing(['items.product', 'items.variations']);
        event(new TransferEvent($transfer, $oldTransfer));

        return $transfer;
    }
}
