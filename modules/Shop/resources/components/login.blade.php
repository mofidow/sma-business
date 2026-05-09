<div class="relative w-full flex items-center justify-center">
  <button type="button" class="absolute top-0 end-0 bg-gray-950 px-2 py-1 rounded-bl-lg text-white" @click="$dispatch('close-login')">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
      <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
    </svg>
  </button>

  <div class="w-full flex flex-col sm:flex-row items-stretch justify-stretch">
    <a href="/" target="_blank" class="self-stretch sm:w-1/2">
      <img src="/img/login-banner.jpg" loading="lazy" class="object-cover h-full max-w-full" alt="Login Banner">
    </a>

    <div class="flex flex-col self-stretch sm:w-1/2">
      <div x-data="{
          two_factor: false,
          form: {
              errors: {},
              username: '{{ demo() ? 'super' : '' }}',
              password: '{{ demo() ? 'password' : '' }}',
              remember: false,
          },
          loading: false,
          onLogin() {
              if (this.form.username && this.form.password) {
                  this.loading = true;
                  axios.post('/login', this.form)
                      .then(({ data }) => {
                          if (data.two_factor) {
                              this.two_factor = true;
                          } else {
                              $dispatch('notification', { content: data.message, type: 'success' });
                              this.open = false;
                              window.location.reload();
                          }
                      })
                      .catch(({ response }) => {
                          this.form.errors = response.data.errors;
                          $dispatch('notification', { content: response.data.message, type: 'error' });
                      })
                      .finally(() => (this.loading = false));
              } else {
                  if (!this.form.username) {
                      this.form.errors.username = '{{ @trans('validation.required', ['attribute' => __('username')]) }}';
                  }
                  if (!this.form.password) {
                      this.form.errors.password = '{{ @trans('validation.required', ['attribute' => __('password')]) }}';
                  }
              }
          }
      }" class="grow p-6 flex flex-col items-center justify-center">
        <h4 class="font-bold text-xl mb-4">{{ __('Login') }}</h4>
        <form @submit.prevent="onLogin" class="w-full">
          <div class="mb-4">
            <x-shop::jet.input id="login-username" x-model="form.username" placeholder="{{ __('Username or email address') }}"
              class="w-full" />
            <p x-show="Object.keys(form.errors) && form.errors?.username" class="input-error mt-1" x-text="form.errors?.username" />
          </div>

          <div class="mb-4">
            <x-shop::jet.input type="password" x-model="form.password" placeholder="{{ __('Password') }}" class="w-full" />
            <p x-show="Object.keys(form.errors) && form.errors?.password" class="input-error mt-1" x-text="form.errors?.password" />
          </div>

          <div class="flex items-center justify-between mb-4 text-sm">
            {{-- Forgot  Password --}}
            <div x-data="{
                email: 'super@sma.tec.sh',
                error: '',
                open: false,
                loading: false,
                name: 'forgot-password',
                maxWidth: 'sm:max-w-md',
                onForgot() {
                    if (this.email) {
                        this.loading = true;
                        axios.post('/forgot-password', { email: this.email })
                            .then(({ data }) => {
                                $dispatch('notification', { content: data.message, type: 'success' });
                                this.open = false;
                            })
                            .catch(({ response }) => (this.error = response.data.message))
                            .finally(() => (this.loading = false));
                    } else {
                        this.error = '{{ @trans('validation.required', ['attribute' => __('email')]) }}';
                    }
                }
            }" @close-forgot-password.window="open = false">
              <button type="button" class="link rounded-md" @click="open = ! open">{{ __('Forgot Password?') }}</button>
              <x-shop::alpine.modal>
                <button type="button" class="absolute top-0 end-0 bg-gray-950 px-2 py-1 rounded-bl-lg text-white"
                  @click="$dispatch('close-forgot-password')">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                    class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                  </svg>
                </button>

                <div class="p-6">
                  <h4 class="font-bold text-xl mb-4">{{ __('Forgot Password?') }}</h4>
                  <p>
                    {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
                  </p>
                  <p x-show="error" class="input-error mt-4" x-text="error" />
                  <div>
                    <x-shop::jet.input type="email" x-model="email" placeholder="{{ __('Email') }}" class="mt-4 w-full" />
                    <x-shop::alpine.button type="button" @click="onForgot" class="w-full justify-center py-2.5 mt-4">
                      {{ __('Email Password Reset Link') }}
                    </x-shop::alpine.button>
                    <x-shop::shared.link href="" class="w-full justify-center py-2.5 mt-4">
                      {{ __('Back to Login') }}
                    </x-shop::shared.link>
                  </div>
                </div>
              </x-shop::alpine.modal>
            </div>

            <label class="flex items-center gap-2">
              <x-shop::jet.checkbox id="remember" name="remember" x-model="form.remember" />
              {{ __('Remember Me') }}
            </label>
          </div>

          <x-shop::alpine.button type="submit" class="w-full justify-center py-2.5">{{ __('Log In') }}</x-shop::alpine.button>
        </form>

        <div x-show="two_factor" x-trap="two_factor" x-data="{ name: 'otp', maxWidth: 'sm:max-w-md', backdrop: true }">
          <x-shop::alpine.modal>
            <div class="p-6">
              <h2 class="text-lg font-bold">{{ __('OTP Code') }}</h2>
              {{-- <p class="text-sm mb-6">{{ __('Please type one time password below to login.') }}</p> --}}

              <div x-data="{ recovery: false }">
                <div class="mb-4 text-sm text-gray-600 dark:text-gray-400" x-show="! recovery">
                  {{ __('Please confirm access to your account by entering the authentication code provided by your authenticator application.') }}
                </div>

                <div class="mb-4 text-sm text-gray-600 dark:text-gray-400" x-cloak x-show="recovery">
                  {{ __('Please confirm access to your account by entering one of your emergency recovery codes.') }}
                </div>

                <x-shop::jet.validation-errors class="mb-4" />

                <form method="POST" action="{{ route('two-factor.login') }}">
                  @csrf

                  <div class="mt-4" x-show="! recovery">
                    <x-shop::jet.label for="code" value="{{ __('Code') }}" />
                    <x-shop::jet.input id="code" class="block mt-1 w-full" type="text" inputmode="numeric" name="code" autofocus
                      x-ref="code" autocomplete="one-time-code" />
                  </div>

                  <div class="mt-4" x-cloak x-show="recovery">
                    <x-shop::jet.label for="recovery_code" value="{{ __('Recovery Code') }}" />
                    <x-shop::jet.input id="recovery_code" class="block mt-1 w-full" type="text" name="recovery_code"
                      x-ref="recovery_code" autocomplete="one-time-code" />
                  </div>

                  <div class="flex items-center justify-end mt-4">
                    <button type="button" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 underline cursor-pointer"
                      x-show="! recovery" x-on:click="recovery = true; $nextTick(() => { $refs.recovery_code.focus() })">
                      {{ __('Use a recovery code') }}
                    </button>

                    <button type="button" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 underline cursor-pointer"
                      x-cloak x-show="recovery" x-on:click="recovery = false; $nextTick(() => { $refs.code.focus() })">
                      {{ __('Use an authentication code') }}
                    </button>

                    <x-shop::jet.button class="ms-4">
                      {{ __('Log in') }}
                    </x-shop::jet.button>
                  </div>
                </form>
              </div>
            </div>
          </x-shop::alpine.modal>
        </div>
      </div>

      @if ($shop_settings['general']['user_registration'] ?? false)
        <div class="mt-auto border-t border-gray-200 dark:border-gray-700 px-4 py-2 text-sm">
          <span class="me-2">{{ __("Don't have an account yet?") }}</span>
          <a href="/signup">{{ __('Register Now') }}</a>
        </div>
      @endif
    </div>
  </div>
</div>
