<?php

namespace Modules\Shop\Tec\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ContactEmail extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contact;

    public function __construct($contact)
    {
        $this->contact = json_decode(json_encode($contact));
    }

    public function toMail($notifiable)
    {
        $message = explode("\n", strip_tags($this->contact->message));

        $mail = (new MailMessage)
            ->greeting('Hello,')
            ->from('noreply@tec.sh', 'SMA Contact Form')
            ->replyTo($this->contact->email, $this->contact->name)
            ->subject($this->contact->subject)
            ->line(__('You have received this message from :name <:email>', ['name' => $this->contact->name, 'email' => $this->contact->email]))
            ->with('------');

        foreach ($message as $line) {
            $mail->line($line);
        }

        $mail->with('------');

        return $mail;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }
}
