<?php

namespace App\Tec\Rules;

use Closure;
use Illuminate\Support\Facades\Http;
use Illuminate\Contracts\Validation\ValidationRule;

class Turnstile implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $res = Http::acceptJson()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'response' => $value,
            'remoteip' => request()->ip(),
            'secret'   => config('captcha.secret'),
        ])->json();

        // logger()->info('Turnstile res ', $res);

        if (! $res['success']) {
            $fail('Captcha is invalid, please try again.');
        }
    }
}
