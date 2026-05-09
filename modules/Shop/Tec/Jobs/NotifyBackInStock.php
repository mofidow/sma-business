<?php

namespace Modules\Shop\Tec\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Notification;
use Modules\Shop\Models\ShopBackInStockSubscription;
use Modules\Shop\Tec\Notifications\BackInStockNotification;

class NotifyBackInStock implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $productId, public ?int $variationId = null) {}

    public function handle(): void
    {
        ShopBackInStockSubscription::where('product_id', $this->productId)
            ->where('variation_id', $this->variationId)
            ->whereNull('notified_at')
            ->each(function (ShopBackInStockSubscription $subscription) {
                Notification::route('mail', $subscription->email)
                    ->notify(new BackInStockNotification($subscription));

                $subscription->update(['notified_at' => now()]);
            });
    }
}
