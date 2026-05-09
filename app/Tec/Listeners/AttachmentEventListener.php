<?php

namespace App\Tec\Listeners;

use App\Tec\Events\AttachmentEvent;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class AttachmentEventListener implements ShouldQueue
{
    use InteractsWithQueue;

    public function failed(AttachmentEvent $event, $exception)
    {
        logger()->error('AttachmentEventListener failed!', [
            'Error' => $exception,
            'model' => $event->model,
        ]);
    }

    public function handle(object $event): void
    {
        $event->model->moveAttachments($event->attachments);
    }
}
