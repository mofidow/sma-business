<?php

namespace App\Tec\Notifications\Import;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Maatwebsite\Excel\Events\ImportFailed;
use Illuminate\Notifications\Messages\MailMessage;
use NotificationChannels\Telegram\TelegramMessage;

class ImportHasFailedNotification extends Notification
{
    use Queueable;

    public string $siteName;

    /**
     * Create a new notification instance.
     */
    public function __construct(public ImportFailed $event, public ?string $message = null)
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

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Import Failed Notification')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line($this->message ?? __('Your import has failed.'))
            ->line(str('<pre style="white-space: pre-wrap;"><code>' . ($this->event->getException()->validator?->errors() ? $this->event->getException()->validator->errors()?->toJson() : $this->event->getException()->getMessage()) . '</code></pre>')->toHtmlString())
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
            ->line($this->message ?? __('Your import has failed.'))
            ->line(str($this->event->getException()->getMessage())->toHtmlString());
    }
}
