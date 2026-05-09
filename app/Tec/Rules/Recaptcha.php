<?php

namespace App\Tec\Rules;

use Closure;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Validation\ValidationRule;

class Recaptcha implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'response' => $value,
            'secret'   => config('captcha.secret'),
            'remoteip' => request()->getClientIp(),
        ])->json();

        // logger()->info('Recaptcha ', ['value' => $value, 'res' => $response]);

        if (! isset($response['success']) || $response['success'] === false) {
            $fail('Captcha is invalid, please try again.');
        }
    }
}
