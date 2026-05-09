<?php

namespace App\Tec\Events;

use App\Models\Sma\Order\Purchase;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class PurchaseEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Purchase $purchase,
        public ?Purchase $oldPurchase = null,
    ) {}
}
