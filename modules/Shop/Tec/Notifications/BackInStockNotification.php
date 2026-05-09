<?php

namespace Modules\Shop\Tec\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Modules\Shop\Models\ShopBackInStockSubscription;

class BackInStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ShopBackInStockSubscription $subscription) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $settings = get_settings('general');
        $siteName = $settings['name'] ?? config('app.name');
        $product = $this->subscription->product;

        return (new MailMessage)
            ->subject(__(':product is back in stock!', ['product' => $product->name]))
            ->greeting(__('Good news!'))
            ->line(__('The product you subscribed to is now back in stock:'))
            ->line("**{$product->name}**")
            ->action(__('Shop Now'), route('shop.product', $product->slug))
            ->line(__('Hurry, limited stock available!'))
            ->line(__('Thank you!'))
            ->salutation(str(__('Regards') . ', ' . $siteName)->toHtmlString());
    }

    public function toArray($notifiable): array
    {
        return [];
    }
}
