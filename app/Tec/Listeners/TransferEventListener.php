<?php

namespace App\Tec\Listeners;

use App\Tec\Events\TransferEvent;

class TransferEventListener
{
    /**
     * Handle the event.
     */
    public function handle(TransferEvent $event): void
    {
        // logger()->info('TransferEvent called.', ['transfer' => $event->transfer->toArray(), 'oldTransfer' => $event->oldTransfer?->toArray()]);

        if ($event->oldTransfer) {
            $this->setStock($event->oldTransfer, true, __('Resetting'));
        }

        $this->setStock($event->transfer, false, __('Syncing'));
    }

    private function setStock($transfer, $reverse = false, $action = 'Syncing')
    {
        foreach ($transfer->items as $item) {
            if ($item->product->type == 'Standard') {
                $this->setProductStock($item->product, $item, $transfer, $reverse, $action);
            } elseif ($item->product->type == 'Combo') {
                foreach ($item->product->products as $product) {
                    $item->quantity = $item->quantity * $product->pivot->quantity;
                    $this->setProductStock($product, $item, $transfer, $reverse, $action);
                }
            } elseif ($item->product->type == 'Recipe') {
                foreach ($item->product->recipes as $recipe) {
                    $item->quantity = $item->quantity * $recipe->quantity;
                    $this->setProductStock($recipe->ingredient, $item, $transfer, $reverse, $action);
                }
            }
        }
    }

    private function setProductStock($product, $item, $transfer, $reverse, $action)
    {
        if ($item->variations->count()) {
            foreach ($item->variations as $variation) {
                // Update from store stock
                $variation->adjustStock($reverse ? 'increase' : 'decrease', $variation->pivot->quantity, [
                    'reference'   => $transfer,
                    'store_id'    => $transfer->store_id,
                    'description' => __('{a} {x} quantity for {id} item {i}', [
                        'a'  => $action,
                        'x'  => __('variation'),
                        'id' => '<a class="link" href="' . route('transfers.index', ['id' => $transfer->id], false) . '">' . __('transfer') . ' ' . $transfer->id . '</a>',
                        'i'  => $item->product->name . '), variation ' . $variation->id . ' (' . $variation->code . ')',
                    ]),
                ]);

                // Update to store stock
                $variation->adjustStock($reverse ? 'decrease' : 'increase', $variation->pivot->quantity, [
                    'reference'   => $transfer,
                    'store_id'    => $transfer->to_store_id,
                    'description' => __('{a} {x} quantity for {id} item {i}', [
                        'a'  => $action,
                        'x'  => __('variation'),
                        'id' => '<a class="link" href="' . route('transfers.index', ['id' => $transfer->id], false) . '">' . __('transfer') . ' ' . $transfer->id . '</a>',
                        'i'  => $item->product->name . '), variation ' . $variation->id . ' (' . $variation->code . ')',
                    ]),
                ]);
            }
        }

        // Update from store stock
        $product->adjustStock($reverse ? 'increase' : 'decrease', $item->quantity, [
            'reference'   => $transfer,
            'store_id'    => $transfer->store_id,
            'description' => __('{a} {x} quantity for {id} item {i}', [
                'a'  => $action,
                'x'  => __('product'),
                'id' => '<a class="link" href="' . route('transfers.index', ['id' => $transfer->id], false) . '">' . __('transfer') . ' ' . $transfer->id . '</a>',
                'i'  => $item->id . ' (' . $item->product->name . ')',
            ]),
        ]);

        // Update to store stock
        $product->adjustStock($reverse ? 'decrease' : 'increase', $item->quantity, [
            'reference'   => $transfer,
            'store_id'    => $transfer->to_store_id,
            'description' => __('{a} {x} quantity for {id} item {i}', [
                'a'  => $action,
                'x'  => __('product'),
                'id' => '<a class="link" href="' . route('transfers.index', ['id' => $transfer->id], false) . '">' . __('transfer') . ' ' . $transfer->id . '</a>',
                'i'  => $item->id . ' (' . $item->product->name . ')',
            ]),
        ]);
    }
}
