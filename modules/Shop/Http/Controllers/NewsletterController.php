<?php

namespace Modules\Shop\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Shop\Models\NewsletterSubscriber;

class NewsletterController extends Controller
{
    public function confirm(Request $request)
    {
        $subscriber = NewsletterSubscriber::where('email', $request->email)->first();
        if (! $subscriber) {
            return to_route('shop.home')
                ->with('error', __('No subscription found for this email.'));
        }

        $subscriber->subscribed_at = now();
        $subscriber->unsubscribed_at = null;
        $subscriber->save();

        return to_route('shop.home')
            ->with('success', __('Thank you for confirming your newsletter subscription!'));
    }

    public function unsubscribe(Request $request)
    {
        $subscriber = NewsletterSubscriber::where('email', $request->email)->first();
        if (! $subscriber) {
            return to_route('shop.home')
                ->with('error', __('No subscription found for this email.'));
        }

        $subscriber->unsubscribed_at = now();
        $subscriber->save();

        return to_route('shop.home')
            ->with('success', __('You have successfully unsubscribed from the newsletter.'));
    }
}
