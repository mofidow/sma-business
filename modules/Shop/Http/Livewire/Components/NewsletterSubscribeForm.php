<?php

namespace Modules\Shop\Http\Livewire\Components;

use Livewire\Component;
use Illuminate\Support\Facades\Notification;
use Modules\Shop\Models\NewsletterSubscriber;
use Modules\Shop\Tec\Notifications\NewsletterConfirmation;

class NewsletterSubscribeForm extends Component
{
    public $name;

    public $email;

    protected $rules = [
        'name'  => 'required|string|max:150',
        'email' => 'required|email|max:150',
    ];

    public function render()
    {
        return view('shop::components.shared.newsletter-subscribe-form');
    }

    public function subscribe()
    {
        $this->validate();

        if (demo()) {
            $this->dispatch('notify',
                type: 'error',
                content: __('This feature is disabled on demo.'),
            );

            return back();
        }

        $this->email = strtolower($this->email);

        $subscribed = NewsletterSubscriber::where('email', $this->email)->first();
        if ($subscribed && ! $subscribed->unsubscribed_at) {
            $this->dispatch('notify',
                type: 'success',
                content: __('You are already subscribed to the newsletter.'),
            );

            return back();
        }

        $subscribed = NewsletterSubscriber::updateOrCreate(
            ['email' => $this->email],
            [
                'name'            => $this->name,
                'email'           => $this->email,
                'subscribed_at'   => null,
                'unsubscribed_at' => now(),
            ]
        );

        Notification::route('mail', $this->email)->notify(new NewsletterConfirmation($subscribed));
        $this->dispatch('notify',
            type: 'success',
            content: __('We have sent you a confirmation email. Please check your inbox to confirm your subscription.'),
        );
    }
}
