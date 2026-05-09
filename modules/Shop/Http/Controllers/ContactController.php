<?php

namespace Modules\Shop\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Notification;
use Modules\Shop\Tec\Notifications\ContactEmail;

class ContactController extends Controller
{
    public function send(Request $request)
    {
        $v = $request->validate([
            'name'    => 'required|string|max:50',
            'subject' => 'required|string|max:70',
            'email'   => 'required|email',
            'message' => 'required|string|max:10000',
        ]);

        $v['subject'] = htmlspecialchars($v['subject']);
        $v['message'] = htmlspecialchars($v['message']);
        Notification::route('mail', get_settings('email'))->notify(new ContactEmail($v));

        return back()->with('success', __('Thank you for message! We will get back to you shortly.'));
    }
}
