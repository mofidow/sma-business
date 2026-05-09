<?php

namespace App\Http\Controllers\Sma\Setting;

use Inertia\Inertia;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MailController extends Controller
{
    public function index()
    {
        return Inertia::render('Sma/Setting/Mail', ['current' => get_settings('mail')]);
    }

    public function store(Request $request)
    {
        $form = $request->validate([
            'mail'                => 'required|array',
            'prefer_biller_email' => 'nullable|boolean',

            'mail.from.name'    => 'required|string',
            'mail.from.address' => 'required|email',

            'mail.default' => 'required|in:smtp,sendmail,mailgun,ses,postmark,log,resend,mailersend',

            'mail.mailers'                 => 'required|array',
            'mail.mailers.smtp'            => 'nullable|array',
            'mail.mailers.smtp.host'       => 'nullable|required_if:mail.default,smtp',
            'mail.mailers.smtp.port'       => 'nullable|numeric|required_if:mail.default,smtp',
            'mail.mailers.smtp.username'   => 'nullable',
            'mail.mailers.smtp.password'   => 'nullable',
            'mail.mailers.smtp.encryption' => 'nullable',

            'mail.mailers.smtp.path' => 'nullable|required_if:mail.default,sendmail',

            'services'                  => 'required|array',
            'services.mailgun'          => 'nullable|array',
            'services.mailgun.domain'   => 'nullable|required_if:mail.default,mailgun',
            'services.mailgun.secret'   => 'nullable|required_if:mail.default,mailgun',
            'services.mailgun.endpoint' => 'nullable|required_if:mail.default,mailgun',
            'services.postmark'         => 'nullable|array',
            'services.postmark.token'   => 'nullable|required_if:mail.default,postmark',
            'services.ses'              => 'nullable|array',
            'services.ses.key'          => 'nullable|required_if:mail.default,ses',
            'services.ses.secret'       => 'nullable|required_if:mail.default,ses',
            'services.ses.region'       => 'nullable|required_if:mail.default,ses',
            'services.resend'           => 'nullable|array',
            'services.resend.key'       => 'nullable|required_if:mail.default,resend',
            'mailersend-driver'         => 'nullable|array',
            'mailersend-driver.api_key' => 'nullable|required_if:mail.default,mailersend',
        ]);

        if (demo()) {
            return back()->with('error', __('Changes are not allowed in demo mode.'));
        }

        Setting::updateOrCreate(['tec_key' => 'mail'], ['tec_value' => json_encode($form)]);

        return back()->with('message', __('Mail settings successfully saved.'));
    }
}
