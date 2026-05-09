<?php

namespace App\Tec\Observers;

use App\Models\Sma\Setting\Store;
use App\Models\Sma\Product\Variation;

class VariationObserver
{
    /**
     * Handle the Variation "created" event.
     */
    public function created(Variation $variation): void
    {
        $stores = Store::pluck('id');
        foreach ($stores as $store) {
            $variation->getStock($store);
        }
    }

    /**
     * Handle the Variation "updated" event.
     */
    public function updated(Variation $variation): void
    {
        //
    }

    /**
     * Handle the Variation "deleted" event.
     */
    public function deleted(Variation $variation): void
    {
        //
    }

    /**
     * Handle the Variation "restored" event.
     */
    public function restored(Variation $variation): void
    {
        //
    }

    /**
     * Handle the Variation "force deleted" event.
     */
    public function forceDeleted(Variation $variation): void
    {
        //
    }
}
