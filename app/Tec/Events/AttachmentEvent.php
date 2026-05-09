<?php

namespace App\Tec\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

class AttachmentEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public $model, public $attachments) {}
}
