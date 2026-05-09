<?php

namespace App\Tec\Notifications\Order;

use Illuminate\Bus\Queueable;
use App\Models\Sma\Order\Quotation;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use NotificationChannels\Twilio\TwilioChannel;
use NotificationChannels\Twilio\TwilioSmsMessage;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\Telegram\TelegramMessage;

class QuotationSignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $siteName;

    /**
     * Create a new notification instance.
     */
    public function __construct(public Quotation $quotation)
    {
        $this->siteName = get_settings('site_name') ?? config('app.name');
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
            ->subject(__('Quotation Approved - {x}', ['x' => $this->quotation->reference]))
            ->greeting(__('Hello') . ' ' . $notifiable->name . '!')
            ->line(__('Good news! Your quotation has been approved and signed by the customer.'))
            ->line(__('Customer') . ': ' . ($this->quotation->signed_by_name ?? $this->quotation->customer?->name))
            ->line(__('Quotation Reference') . ': ' . $this->quotation->reference)
            ->line(__('Signed at') . ': ' . $this->quotation->signed_at)
            ->action(__('View {x}', ['x' => __('Quotation')]), route('quotations.index', ['id' => $this->quotation->id]))
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
            'quotation_id' => $this->quotation->id,
            'reference'    => $this->quotation->reference,
            'signed_by'    => $this->quotation->signed_by_name,
            'signed_at'    => $this->quotation->signed_at?->toISOString(),
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
            ->line(__('Your quotation has been approved and signed by the customer.'))
            ->line(__('Customer') . ': ' . ($this->quotation->signed_by_name ?? $this->quotation->customer?->name))
            ->line(__('Reference') . ': ' . $this->quotation->reference)
            ->line('')
            ->line(__('Thank you very much!'))
            ->button(__('View {x}', ['x' => __('Quotation')]), route('quotations.index', ['id' => $this->quotation->id]));
    }

    /**
     * Get the Twilio SMS representation of the notification.
     */
    public function toTwilio(object $notifiable): TwilioSmsMessage
    {
        return (new TwilioSmsMessage)
            ->content(
                __('Hello') . ' ' . $notifiable->name . "!\n" .
                __('Your quotation {ref} has been approved and signed by {customer}.', [
                    'ref'      => $this->quotation->reference,
                    'customer' => $this->quotation->signed_by_name ?? $this->quotation->customer?->name,
                ])
            );
    }
}
