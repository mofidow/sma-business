<?php

namespace App\Tec\Observers;

use App\Tec\Events\PurchaseEvent;
use App\Models\Sma\Order\Purchase;

class PurchaseObserver
{
    /**
     * Handle the Purchase "deleting" event.
     */
    public function deleting(Purchase $purchase): void
    {
        if (! $purchase->isForceDeleting()) {
            $oldPurchase = $purchase->load([
                'store', 'supplier', 'items.product', 'items.variations',
            ])->replicateQuietly();
            $oldPurchase->id = $purchase->id;
            event(new PurchaseEvent(new Purchase, $oldPurchase));

            $purchase->items->each->forceDelete();
            $purchase->payments->each->forceDelete();
            $purchase->returnOrders->each->forceDelete();
        }
    }

    /**
     * Handle the Purchase "restored" event.
     */
    public function restored(Purchase $purchase): void
    {
        event(new PurchaseEvent($purchase));
    }
}
