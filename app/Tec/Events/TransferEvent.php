<?php

namespace App\Tec\Events;

use App\Models\Sma\Product\Transfer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class TransferEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public Transfer $transfer,
        public ?Transfer $oldTransfer = null,
    ) {}
}
