<?php

namespace App\Tec\Observers;

use App\Tec\Events\TransferEvent;
use App\Models\Sma\Product\Transfer;

class TransferObserver
{
    /**
     * Handle the Transfer "deleting" event.
     */
    public function deleting(Transfer $transfer): void
    {
        if (! $transfer->isForceDeleting()) {
            event(new TransferEvent(new Transfer, $transfer));
        }
    }

    /**
     * Handle the Transfer "restored" event.
     */
    public function restored(Transfer $transfer): void
    {
        event(new TransferEvent($transfer));
    }
}
