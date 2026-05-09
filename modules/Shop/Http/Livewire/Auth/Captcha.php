<?php

namespace Modules\Shop\Http\Livewire\Auth;

use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

trait Captcha
{
    protected function validateRecaptcha(string $token): void
    {
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'response' => $token,
            'remoteip' => request()->ip(),
            'secret'   => config('captcha.secret'),
        ]);

        $throw = fn ($message) => throw ValidationException::withMessages(['captcha' => $message]);

        if (! $response->successful() || ! $response->json('success')) {
            $throw($response->json(['error-codes'])[0] ?? 'An error occurred.');
        }

        if ($response->json('score') < 0.6) {
            $throw('We were unable to verify that you\'re not a robot. Please try again.');
        }
    }

    protected function validateTrunstile(string $token): void
    {
        $response = Http::acceptJson()->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'response' => $token,
            'remoteip' => request()->ip(),
            'secret'   => config('captcha.secret'),
        ]);

        $throw = fn ($message) => throw ValidationException::withMessages(['captcha' => $message]);

        if (! $response->json('success')) {
            $throw('Captcha is invalid, please try again.');
        }
    }
}
