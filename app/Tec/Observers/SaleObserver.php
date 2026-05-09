<?php

namespace App\Tec\Observers;

use App\Tec\Events\SaleEvent;
use App\Models\Sma\Order\Sale;

class SaleObserver
{
    /**
     * Handle the Sale "deleting" event.
     */
    public function deleting(Sale $sale): void
    {
        if (! $sale->isForceDeleting()) {
            $sale->user->deletePoints($sale->id);
            $sale->customer->deletePoints($sale->id);
            $sale->loadMissing([
                'customer',
                'items' => fn ($query) => $query->withTrashed()->with(['product', 'variations']),
            ]);
            event(new SaleEvent(new Sale, $sale));
        }
    }

    /**
     * Handle the Sale "restored" event.
     */
    public function restored(Sale $sale): void
    {
        event(new SaleEvent($sale));
    }
}
