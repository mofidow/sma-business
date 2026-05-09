<?php

namespace Modules\Shop\Http\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\On;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Features;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Validation\ValidationException;

class SignIn extends Component
{
    use Captcha;

    public $username;

    public $password;

    public $provider;

    public $captcha;

    public $remember = false;

    public function mount()
    {
        if (auth()->check()) {
            return redirect()->route('shop.profile');
        }

        if (demo()) {
            $this->username = 'super';
            $this->password = 'password';
        }

        $this->provider = get_settings('captcha_provider');
    }

    public function render()
    {
        if (! str(url()->previous())->contains('captcha')) {
            session()->put('url.intended', url()->previous() ?? route('shop.home'));
            session()->put('url.back', url()->previous() ?? route('shop.home'));
        }

        return view('shop::jet.auth.login')->title(__('Sign In'));
    }

    protected function rules(): array
    {
        return [
            'username' => 'required|string',
            'password' => 'required|string',
            'remember' => 'boolean',
            'captcha'  => ($this->provider && $this->provider == 'local') ? ['required', 'captcha'] : 'nullable',
        ];
    }

    protected function messages(): array
    {
        return [
            'captcha.captcha' => __('The captcha is invalid. Please try again.'),
        ];
    }

    #[On('updateToken')]
    public function updateToken($token = null)
    {
        $this->captcha = $token;
    }

    public function login($token = null)
    {
        $this->validate();
        if ($this->provider && $this->provider == 'recaptcha') {
            if ($token) {
                $this->validateRecaptcha($token);
            } else {
                throw ValidationException::withMessages([
                    'captcha' => __('Captcha is required.'),
                ]);
            }
        } elseif ($this->provider && $this->provider == 'trunstile') {
            if ($this->captcha) {
                $this->validateTrunstile($this->captcha);
            } else {
                throw ValidationException::withMessages([
                    'captcha' => __('Captcha is required.'),
                ]);
            }
        }

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['username' => $this->username, 'password' => $this->password], $this->remember)) {
            if (! Auth::attempt(['email' => $this->username, 'password' => $this->password], $this->remember)) {
                RateLimiter::hit($this->throttleKey());

                throw ValidationException::withMessages([
                    'username' => trans('auth.failed'),
                ]);
            }
        }

        RateLimiter::clear($this->throttleKey());

        $user = Auth::user();
        if ($user && ! $user->active) {
            throw ValidationException::withMessages([
                'username' => __('Account is not active!'),
            ]);
        }

        if (Features::enabled(Features::twoFactorAuthentication()) && Fortify::confirmsTwoFactorAuthentication() && $user?->two_factor_secret && ! $user?->two_factor_confirmed_at && in_array(TwoFactorAuthenticatable::class, class_uses_recursive($user))) {
            return redirect()->route('shop.otp-code');
        }

        return redirect()->intended($user->customer_id ? 'shop.profile' : 'dashboard')->with('success', __('You are logged in successfully.'));
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'username' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->username)) . '|' . request()->ip();
    }
}
