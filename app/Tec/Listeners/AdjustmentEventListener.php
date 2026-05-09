<?php

namespace App\Tec\Listeners;

use App\Tec\Events\AdjustmentEvent;

class AdjustmentEventListener
{
    /**
     * Handle the event.
     */
    public function handle(AdjustmentEvent $event): void
    {
        // logger()->info('AdjustmentEvent called.', ['adjustment' => $event->adjustment->toArray(), 'oldAdjustment' => $event->oldAdjustment?->toArray()]);

        if ($event->oldAdjustment) {
            $this->setStock($event->oldAdjustment, $event->oldAdjustment->type == 'Addition', __('Resetting'));
        }

        $this->setStock($event->adjustment, $event->adjustment->type != 'Addition', __('Syncing'));
    }

    private function setStock($adjustment, $reverse = false, $action = 'Syncing')
    {
        foreach ($adjustment->items as $item) {
            if ($item->product->type == 'Standard') {
                $this->setProductStock($item->product, $item, $adjustment, $reverse, $action);
            } elseif ($item->product->type == 'Combo') {
                foreach ($item->product->products as $product) {
                    $item->quantity = $item->quantity * $product->pivot->quantity;
                    $this->setProductStock($product, $item, $adjustment, $reverse, $action);
                }
            } elseif ($item->product->type == 'Recipe') {
                foreach ($item->product->recipes as $recipe) {
                    $item->quantity = $item->quantity * $recipe->quantity;
                    $this->setProductStock($recipe->ingredient, $item, $adjustment, $reverse, $action);
                }
            }
        }
    }

    private function setProductStock($product, $item, $adjustment, $reverse, $action)
    {
        if ($item->variations->count()) {
            foreach ($item->variations as $variation) {
                $variation->adjustStock($reverse ? 'decrease' : 'increase', $variation->pivot->quantity, [
                    'reference'   => $adjustment,
                    'store_id'    => $adjustment->store_id,
                    'description' => __('{a} {x} quantity for {id} item {i}', [
                        'a'  => $action,
                        'x'  => __('variation'),
                        'id' => '<a class="link" href="' . route('adjustments.index', ['id' => $adjustment->id], false) . '">' . __('adjustment') . ' ' . $adjustment->id . '</a>',
                        'i'  => $item->product->name . '), variation ' . $variation->id . ' (' . $variation->code . ')',
                    ]),
                ]);
            }
        }

        $product->adjustStock($reverse ? 'decrease' : 'increase', $item->quantity, [
            'reference'   => $adjustment,
            'store_id'    => $adjustment->store_id,
            'description' => __('{a} {x} quantity for {id} item {i}', [
                'a'  => $action,
                'x'  => __('product'),
                'id' => '<a class="link" href="' . route('adjustments.index', ['id' => $adjustment->id], false) . '">' . __('adjustment') . ' ' . $adjustment->id . '</a>',
                'i'  => $item->id . ' (' . $item->product->name . ')',
            ]),
        ]);

        // $stock = $product->getStock($adjustment->store_id)->first();

        // $stock->{$reverse ? 'decreaseBalance' : 'increaseBalance'}($item->quantity, ['reference' => $item, 'description' => __('Product quantity sync for adjustment item.')]);
    }
}
