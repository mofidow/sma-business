<div class="container mx-auto sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-24">
  <x-shop::jet.form-section id="register-form" submit="login">
    <x-slot name="title">
      {{ __('Sign In') }}
    </x-slot>

    <x-slot name="description">
      {{ __('Please fill in the form to login.') }}
    </x-slot>

    <x-slot name="form">
      <div class="col-span-full">
        <div class="max-w-md pb-6">
          <x-shop::jet.validation-errors class="mb-4" />

          <div>
            <x-shop::jet.label for="email" value="{{ __('Username') }}" />
            <x-shop::jet.input id="username" wire:model="username" class="block mt-1 w-full" type="text" name="username" required
              :value="old('username')" autofocus autocomplete="username" />
            @error('username')
              <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span>
            @enderror
          </div>

          <div class="mt-4">
            <x-shop::jet.label for="password" value="{{ __('Password') }}" />
            <x-shop::jet.input id="password" wire:model="password" class="block mt-1 w-full" type="password" name="password" required
              autocomplete="current-password" />
            @error('password')
              <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span>
            @enderror
          </div>

          @if ($provider ?? null)
            @if ($provider == 'recaptcha')
              <script src="https://www.google.com/recaptcha/api.js?render={{ config('captcha.sitekey') }}"></script>

              <script>
                function handle(e) {
                  grecaptcha.ready(function() {
                    grecaptcha.execute('{{ config('captcha.sitekey') }}', {
                        action: 'submit'
                      })
                      .then(function(token) {
                        @this.set('captcha', token);
                        @this.login(token);
                      });
                  })
                }
              </script>
              @if ($errors->has('captcha'))
                <div class="text-center text-sm text-red-500">
                  {{ $errors->first('captcha') }}
                </div>
              @endif
            @elseif ($provider == 'trunstile')
              <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" defer></script>
              <div id="cf-widget" class="mt-4" wire:ignore></div>
              <script>
                window.onload = function() {
                  turnstile.render('#cf-widget', {
                    sitekey: '{{ config('captcha.sitekey') }}',
                    theme: localStorage.getItem('theme') == 'dark' ? 'dark' : (localStorage.getItem('theme') == 'light' ? 'light' : 'auto'),
                    callback: function(token) {
                      @this.set('captcha', token);
                      //   Livewire.dispatch('updateToken', {
                      //     token
                      //   });
                    }
                  });
                };
              </script>
            @elseif ($provider == 'local')
              <div class="mt-4">
                <x-shop::jet.label for="captcha" value="{{ __('Captcha') }}" />
                <div class="flex items-stretch justify-start mt-1 gap-4">
                  <x-shop::jet.input id="captcha" wire:model="captcha" class="block w-full" type="captcha" name="captcha" required />
                  <img src="{{ captcha_src() }}" alt="" class="shadow-sm rounded-md" />
                </div>
                @error('captcha')
                  <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span>
                @enderror
              </div>
            @endif
          @endif

          <div class="block mt-4">
            <label for="remember_me" class="flex items-center">
              <x-shop::jet.checkbox id="remember_me" name="remember" wire:model="remember" />
              <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Remember me') }}</span>
            </label>
          </div>

          <div class="flex items-center mt-4">
            @if ($provider && $provider == 'recaptcha')
              <x-shop::jet.button class="me-4 g-recaptcha" type="submit" data-sitekey="{{ config('captcha.sitekey') }}"
                data-callback='handle' data-action='submit'>
                {{ __('Log in') }}
              </x-shop::jet.button>
            @else
              <x-shop::jet.button class="me-4">
                {{ __('Log in') }}
              </x-shop::jet.button>
            @endif

            @if (Route::has('shop.password.request'))
              <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800"
                href="{{ route('shop.password.request') }}" wire:navigate w.hover>
                {{ __('Forgot your password?') }}
              </a>
            @endif
          </div>

          <div class="mt-6 text-sm">
            {{ __("Don't have an account?") }}
            <a href="{{ route('shop.signup') }}" wire:navigate w.hover class="link group relative ms-1">
              {{ __('Sign up') }}

              <span class="hidden group-hover:inline absolute start-full ms-1">&rarr;</span>
            </a>
          </div>
        </div>
      </div>
    </x-slot>
  </x-shop::jet.form-section>
</div>
