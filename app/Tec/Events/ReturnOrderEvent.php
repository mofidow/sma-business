<?php

namespace App\Tec\Events;

use App\Models\Sma\Order\ReturnOrder;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class ReturnOrderEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public ReturnOrder $return_order,
        public ?ReturnOrder $oldReturnOrder = null,
    ) {}
}
