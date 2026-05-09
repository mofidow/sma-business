<?php

namespace Modules\Shop\Http\Livewire\Auth;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Sma\People\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Tec\Actions\Fortify\PasswordValidationRules;

class SignUp extends Component
{
    use Captcha;
    use PasswordValidationRules;

    public $form = [];

    public $provider;

    public $captcha;

    private $general = [];

    public function mount()
    {
        if (auth()->check()) {
            return redirect()->route('shop.profile');
        }

        $this->general = get_settings('general');
        if (! ($this->general['user_registration'] ?? false)) {
            abort(404);
        }
        $this->provider = get_settings('captcha_provider');
    }

    public function render()
    {
        return view('shop::jet.auth.register')->title(__('Sign Up'));
    }

    #[On('updateToken')]
    public function updateToken($token = null)
    {
        $this->captcha = $token;
    }

    public function register($token = null)
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

        $user = User::create([
            'active'            => 1,
            'name'              => $this->form['name'],
            'email'             => $this->form['email'],
            'username'          => $this->form['username'],
            'password'          => Hash::make($this->form['password']),
            'email_verified_at' => demo() || ! $this->general['email_confirmation'] ? now() : null,
        ]);

        $customer = Customer::create([
            'name'            => $this->form['name'],
            'company'         => $this->form['name'],
            'opening_balance' => 0,
            'user_id'         => $user->id,
            'email'           => $this->form['email'],
        ]);

        $user->assignRole('Customer');
        $user->customer_id = $customer->id;
        $user->save();

        auth()->login($user, true);

        return to_route('shop.profile')->with('success', __('You have successfully registered, please check your email to verify your account.'));
    }

    protected function rules()
    {
        return [
            'form.name'     => ['required', 'string', 'max:255'],
            'form.username' => ['required', 'string', 'max:25', 'unique:users,username'],
            'form.email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'form.password' => $this->passwordRules(),
            'form.terms'    => 'accepted',
            'captcha'       => ($this->provider && $this->provider == 'local') ? ['required', 'captcha'] : 'nullable',
        ];
    }

    protected function messages(): array
    {
        return [
            'captcha.captcha' => __('The captcha is invalid. Please try again.'),
        ];
    }
}
