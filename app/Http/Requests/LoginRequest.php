<?php

namespace App\Http\Requests;

use App\Tec\Rules\Recaptcha;
use App\Tec\Rules\Turnstile;
use Laravel\Fortify\Http\Requests\LoginRequest as BaseLoginRequest;

class LoginRequest extends BaseLoginRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $provider = get_settings('captcha_provider');

        $captcha_rules = match ($provider) {
            'local'     => ['required', 'captcha'],
            'turnstile' => ['required', new Turnstile],
            'recaptcha' => ['required', new Recaptcha],
            default     => 'nullable',
        };

        return [
            'username' => 'required|string',
            'password' => 'required|string',
            'remember' => 'nullable|boolean',
            'captcha'  => $captcha_rules,
        ];
    }

    public function messages(): array
    {
        return [
            'captcha.captcha' => __('The captcha is invalid. Please try again.'),
        ];
    }
}
