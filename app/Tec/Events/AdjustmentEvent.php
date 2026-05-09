<?php

namespace App\Tec\Events;

use App\Models\Sma\Product\Adjustment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class AdjustmentEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Adjustment $adjustment,
        public ?Adjustment $oldAdjustment = null,
    ) {}
}
