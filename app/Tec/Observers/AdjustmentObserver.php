<?php

namespace App\Tec\Observers;

use App\Tec\Events\AdjustmentEvent;
use App\Models\Sma\Product\Adjustment;

class AdjustmentObserver
{
    /**
     * Handle the Adjustment "deleting" event.
     */
    public function deleting(Adjustment $adjustment): void
    {
        if (! $adjustment->isForceDeleting()) {
            event(new AdjustmentEvent(new Adjustment, $adjustment));
        }
    }

    /**
     * Handle the Adjustment "restored" event.
     */
    public function restored(Adjustment $adjustment): void
    {
        event(new AdjustmentEvent($adjustment));
    }
}
