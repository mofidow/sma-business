<?php

namespace App\Tec\Events;

use App\Models\Sma\Order\Sale;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class SaleEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Sale $sale,
        public ?Sale $oldSale = null,
    ) {}
}
