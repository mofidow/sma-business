<?php

namespace App\Tec\Notifications\Order;

use Illuminate\Bus\Queueable;
use App\Models\Sma\Order\Payment;
use Illuminate\Support\Facades\URL;
use Illuminate\Notifications\Notification;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\Telegram\TelegramMessage;

class PaymentNotification extends Notification
{
    use Queueable;

    public string $siteName;

    public string $url;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Payment $payment)
    {
        $this->siteName = get_settings('site_name') ?? config('app.name');
        $this->url = URL::signedRoute('payment.show', $this->payment->id);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['mail'];

        if ($notifiable->telegram_user_id && config('services.telegram-bot-api.token')) {
            $channels[] = 'telegram';
        }

        if ($notifiable->phone && config('twilio-notification-channel.account_sid')) {
            $channels[] = TwilioChannel::class;
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Payment ' . ($this->payment->received ? 'Receipt' : 'Request') . ' from ' . $this->siteName)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line(__('Please view the payment by clicking the link below.'))
            ->action(__('View {x}', ['x' => __('Payment')]), $this->url)
            ->line(__('Thank you very much!'))
            ->salutation(str(__('Regards') . ', ' . $this->siteName)->toHtmlString());
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    /**
     * Get the Telegram representation of the notification.
     */
    public function toTelegram(object $notifiable): TelegramMessage
    {
        return TelegramMessage::create()
            ->content("*{$this->siteName}*")
            ->line('')
            ->line(__('Hello') . ' ' . $notifiable->name . '!')
            ->line(__('Please view the payment by clicking the link below.'))
            ->line('')
            ->line(__('Thank you very much!'))
            ->button(__('View {x}', ['x' => __('Payment')]), $this->url);
    }

    /**
     * Get the Twilio SMS representation of the notification.
     */
    public function toTwilio(object $notifiable): TwilioSmsMessage
    {
        return (new TwilioSmsMessage)
            ->content(__('Hello') . ' ' . $notifiable->name . "!\n" . __('Your payment from {site} is ready. View it here: {url}', ['site' => $this->siteName, 'url' => $this->url]));
    }
}
