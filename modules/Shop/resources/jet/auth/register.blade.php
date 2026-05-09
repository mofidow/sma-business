<div class="container mx-auto sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-24">
  <x-shop::jet.form-section submit="register">
    <x-slot name="title">
      {{ __('Sign Up') }}
    </x-slot>

    <x-slot name="description">
      {{ __('Please fill in the form to register.') }}
    </x-slot>

    <x-slot name="form">
      <div class="col-span-full">
        <div class="max-w-md pb-6">
          <x-shop::jet.validation-errors class="mb-4" />

          <div>
            <x-shop::jet.label for="name" value="{{ __('Name') }}" />
            <x-shop::jet.input id="name" class="block mt-1 w-full" type="text" wire:model="form.name" :value="old('name')" required
              autofocus autocomplete="name" />
          </div>

          <div class="mt-4">
            <x-shop::jet.label for="email" value="{{ __('Email') }}" />
            <x-shop::jet.input id="email" class="block mt-1 w-full" type="email" wire:model="form.email" :value="old('email')" required
              autocomplete="email" />
          </div>

          <div class="mt-4">
            <x-shop::jet.label for="username" value="{{ __('Username') }}" />
            <x-shop::jet.input id="username" class="block mt-1 w-full" type="text" wire:model="form.username" :value="old('username')" required
              autocomplete="username" />
          </div>

          <div class="mt-4">
            <x-shop::jet.label for="password" value="{{ __('Password') }}" />
            <x-shop::jet.input id="password" class="block mt-1 w-full" type="password" wire:model="form.password" required
              autocomplete="new-password" />
          </div>

          <div class="mt-4">
            <x-shop::jet.label for="password_confirmation" value="{{ __('Confirm Password') }}" />
            <x-shop::jet.input id="password_confirmation" class="block mt-1 w-full" type="password" wire:model="form.password_confirmation"
              required autocomplete="new-password" />
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
                        @this.register(token);
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

          @if (Laravel\Jetstream\Jetstream::hasTermsAndPrivacyPolicyFeature())
            <div class="mt-4">
              <x-shop::jet.label for="terms">
                <div class="flex items-center">
                  <x-shop::jet.checkbox wire:model="form.terms" id="terms" required />

                  <div class="ms-2">
                    {!! __('I agree to the :terms_of_service and :privacy_policy', [
                        'terms_of_service' =>
                            '<a target="_blank" href="' .
                            route('terms.show') .
                            '" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">' .
                            __('Terms of Service') .
                            '</a>',
                        'privacy_policy' =>
                            '<a target="_blank" href="' .
                            route('policy.show') .
                            '" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">' .
                            __('Privacy Policy') .
                            '</a>',
                    ]) !!}
                  </div>
                </div>
              </x-shop::jet.label>
            </div>
          @endif

          <div class="flex items-center mt-4">
            @if ($provider && $provider == 'recaptcha')
              <x-shop::jet.button class="me-4 g-recaptcha" type="submit" data-sitekey="{{ config('captcha.sitekey') }}"
                data-callback='handle' data-action='submit'>
                {{ __('Register') }}
              </x-shop::jet.button>
            @else
              <x-shop::jet.button class="me-4">
                {{ __('Register') }}
              </x-shop::jet.button>
            @endif

            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800"
              href="{{ route('shop.signin') }}" wire:navigate w.hover>
              {{ __('Already registered?') }}
            </a>
          </div>
        </div>
      </div>
    </x-slot>
  </x-shop::jet.form-section>
</div>
