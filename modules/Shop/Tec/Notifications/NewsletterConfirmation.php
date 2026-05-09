<?php

namespace Modules\Shop\Tec\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\URL;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Shop\Models\NewsletterSubscriber;
use Illuminate\Notifications\Messages\MailMessage;

class NewsletterConfirmation extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public NewsletterSubscriber $subscriber) {}

    public function toMail($notifiable)
    {
        $settings = get_settings('general');

        $url = URL::signedRoute('newsletters.confirm', ['id' => $this->subscriber->id, 'email' => $this->subscriber->email]);

        return (new MailMessage)
            ->greeting(__('Hello {x},', ['x' => $this->subscriber->name]))
            ->from('noreply@tec.sh', $settings['name'] ?? 'SMA Newsletter')
            ->replyTo($this->subscriber->email, $this->subscriber->name)
            ->subject(__('Confirm Newsletter Subscription'))
            ->line(__('You have subscribed to the newsletter with the email: :email', ['email' => $this->subscriber->email]))
            ->line(__('Please confirm your subscription by clicking the button below:'))
            ->action(__('Confirm Subscription'), $url)
            ->line(__('If you did not subscribe to the newsletter, you can ignore this email.'))
            ->line(__('Thank you!'));
    }

    public function via($notifiable)
    {
        return ['mail'];
    }
}
