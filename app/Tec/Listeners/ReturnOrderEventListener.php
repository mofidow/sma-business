<?php

namespace App\Tec\Listeners;

use App\Models\Sma\Order\Sale;
use App\Tec\Events\ReturnOrderEvent;
use App\Tec\Services\CostAllocationService;

class ReturnOrderEventListener
{
    public function __construct(public CostAllocationService $costAllocationService) {}

    /**
     * Handle the event.
     */
    public function handle(ReturnOrderEvent $event): void
    {
        // logger()->info('ReturnOrderEvent called.', ['return_order' => $event->return_order->toArray(), 'oldReturnOrder' => $event->oldReturnOrder?->toArray()]);

        if ($event->oldReturnOrder) {
            $this->setStock($event->oldReturnOrder, $event->oldReturnOrder->type == 'Sale', __('Resetting'));

            if ($event->oldReturnOrder->type == 'Sale') {
                $this->costAllocationService->reverseSaleReturnCosting($event->oldReturnOrder);

                $event->oldReturnOrder->customer?->increaseBalance($event->oldReturnOrder->grand_total, [
                    'reference'   => $event->oldReturnOrder,
                    'description' => __('Reset balance for {return_order}', ['sale' => '<a class="link" href="' . route('return_orders.index', ['id' => $event->oldReturnOrder->id], false) . '">' . (__('Return Order') . ' #' . $event->oldReturnOrder->id) . '</a>']),
                ]);
                $event->oldReturnOrder->store->account?->decreaseBalance($event->oldReturnOrder->grand_total, [
                    'reference'   => $event->oldReturnOrder,
                    'description' => __('Reset balance for {return_order}', ['sale' => '<a class="link" href="' . route('return_orders.index', ['id' => $event->oldReturnOrder->id], false) . '">' . (__('Return Order') . ' #' . $event->oldReturnOrder->id) . '</a>']),
                ]);

                // if ($event->oldReturnOrder->return_payment_amount > 0 && $event->oldReturnOrder->sale_id) {
                //     $oldSale = Sale::withoutGlobalScopes()->find($event->oldReturnOrder->sale_id);
                //     $event->oldReturnOrder->customer?->decreaseBalance($event->oldReturnOrder->return_payment_amount, [
                //         'reference'   => $event->oldReturnOrder,
                //         'description' => __('Reset return payment for {return_order}', ['return_order' => '<a class="link" href="' . route('return_orders.index', ['id' => $event->oldReturnOrder->id], false) . '">' . (__('Return Order') . ' #' . $event->oldReturnOrder->id) . '</a>']),
                //     ]);
                //     $event->oldReturnOrder->store->account?->decreaseBalance($event->oldReturnOrder->return_payment_amount, [
                //         'reference'   => $event->oldReturnOrder,
                //         'description' => __('Reset return payment for {return_order}', ['return_order' => '<a class="link" href="' . route('return_orders.index', ['id' => $event->oldReturnOrder->id], false) . '">' . (__('Return Order') . ' #' . $event->oldReturnOrder->id) . '</a>']),
                //     ]);
                //     $oldSale?->updateQuietly(['paid' => $oldSale->paid + $event->oldReturnOrder->return_payment_amount]);
                // }
            } elseif ($event->oldReturnOrder->type == 'Purchase') {
                $this->costAllocationService->reversePurchaseReturnCosting($event->oldReturnOrder);

                $event->oldReturnOrder->supplier?->increaseBalance($event->oldReturnOrder->grand_total, [
                    'reference'   => $event->oldReturnOrder,
                    'description' => __('Reset balance for {return_order}', ['sale' => '<a class="link" href="' . route('return_orders.index', ['id' => $event->oldReturnOrder->id], false) . '">' . (__('Return Order') . ' #' . $event->oldReturnOrder->id) . '</a>']),
                ]);
                $event->oldReturnOrder->store->account?->increaseBalance($event->oldReturnOrder->grand_total, [
                    'reference'   => $event->oldReturnOrder,
                    'description' => __('Reset balance for {return_order}', ['sale' => '<a class="link" href="' . route('return_orders.index', ['id' => $event->oldReturnOrder->id], false) . '">' . (__('Return Order') . ' #' . $event->oldReturnOrder->id) . '</a>']),
                ]);
            }
        }

        $this->setStock($event->return_order, $event->return_order->type == 'Purchase', __('Syncing'));

        if ($event->return_order->type == 'Sale') {
            $this->costAllocationService->handleSaleReturnCosting($event->return_order);

            $event->return_order->customer?->decreaseBalance($event->return_order->grand_total, [
                'reference'   => $event->return_order,
                'description' => __('Sync balance for {return_order}', ['sale' => '<a class="link" href="' . route('return_orders.index', ['id' => $event->return_order->id], false) . '">' . (__('Return Order') . ' #' . $event->return_order->id) . '</a>']),
            ]);
            $event->return_order->store->account?->increaseBalance($event->return_order->grand_total, [
                'reference'   => $event->return_order,
                'description' => __('Sync balance for {return_order}', ['sale' => '<a class="link" href="' . route('return_orders.index', ['id' => $event->return_order->id], false) . '">' . (__('Return Order') . ' #' . $event->return_order->id) . '</a>']),
            ]);

            // if ($event->return_order->return_payment_amount > 0 && $event->return_order->sale_id) {
            //     $newSale = Sale::withoutGlobalScopes()->find($event->return_order->sale_id);
            //     $event->return_order->customer?->increaseBalance($event->return_order->return_payment_amount, [
            //         'reference'   => $event->return_order,
            //         'description' => __('Sync return payment for {return_order}', ['return_order' => '<a class="link" href="' . route('return_orders.index', ['id' => $event->return_order->id], false) . '">' . (__('Return Order') . ' #' . $event->return_order->id) . '</a>']),
            //     ]);
            //     $event->return_order->store->account?->decreaseBalance($event->return_order->return_payment_amount, [
            //         'reference'   => $event->return_order,
            //         'description' => __('Sync return payment for {return_order}', ['return_order' => '<a class="link" href="' . route('return_orders.index', ['id' => $event->return_order->id], false) . '">' . (__('Return Order') . ' #' . $event->return_order->id) . '</a>']),
            //     ]);
            //     $newSale?->updateQuietly(['paid' => max(0, $newSale->paid - $event->return_order->return_payment_amount)]);
            // }
        } elseif ($event->return_order->type == 'Purchase') {
            $this->costAllocationService->handlePurchaseReturnCosting($event->return_order);

            $event->return_order->supplier?->decreaseBalance($event->return_order->grand_total, [
                'reference'   => $event->return_order,
                'description' => __('Sync balance for {return_order}', ['sale' => '<a class="link" href="' . route('return_orders.index', ['id' => $event->return_order->id], false) . '">' . (__('Return Order') . ' #' . $event->return_order->id) . '</a>']),
            ]);
            $event->return_order->store->account?->decreaseBalance($event->return_order->grand_total, [
                'reference'   => $event->return_order,
                'description' => __('Sync balance for {return_order}', ['sale' => '<a class="link" href="' . route('return_orders.index', ['id' => $event->return_order->id], false) . '">' . (__('Return Order') . ' #' . $event->return_order->id) . '</a>']),
            ]);
        }
    }

    private function setStock($return_order, $reverse = false, $action = 'Syncing')
    {
        foreach ($return_order->items as $item) {
            if ($item->product->type == 'Standard') {
                $this->setProductStock($item->product, $item, $return_order, $reverse, $action);
            } elseif ($item->product->type == 'Combo') {
                foreach ($item->product->products as $product) {
                    $item->quantity = $item->quantity * $product->pivot->quantity;
                    $this->setProductStock($product, $item, $return_order, $reverse, $action);
                }
            } elseif ($item->product->type == 'Recipe') {
                foreach ($item->product->recipes as $recipe) {
                    $item->quantity = $item->quantity * $recipe->quantity;
                    $this->setProductStock($recipe->ingredient, $item, $return_order, $reverse, $action);
                }
            }
        }
    }

    private function setProductStock($product, $item, $return_order, $reverse, $action)
    {
        if ($item->variations->count()) {
            foreach ($item->variations as $variation) {
                $variation->adjustStock($reverse ? 'decrease' : 'increase', $variation->pivot->base_quantity, [
                    'reference'   => $return_order,
                    'store_id'    => $return_order->store_id,
                    'description' => __('{a} {x} quantity for {id} item {i}', [
                        'a'  => $action,
                        'x'  => __('variation'),
                        'id' => '<a class="link" href="' . route('return_orders.index', ['id' => $return_order->id], false) . '">' . __('Return Order') . ' ' . $return_order->id . '</a>',
                        'i'  => $item->product->name . '), variation ' . $variation->id . ' (' . $variation->code . ')',
                    ]),
                ]);
            }
        }

        $product->adjustStock($reverse ? 'decrease' : 'increase', $item->base_quantity, [
            'reference'   => $return_order,
            'store_id'    => $return_order->store_id,
            'description' => __('{a} {x} quantity for {id} item {i}', [
                'a'  => $action,
                'x'  => __('product'),
                'id' => '<a class="link" href="' . route('return_orders.index', ['id' => $return_order->id], false) . '">' . __('Return Order') . ' ' . $return_order->id . '</a>',
                'i'  => $item->id . ' (' . $item->product->name . ')',
            ]),
        ]);
    }
}
