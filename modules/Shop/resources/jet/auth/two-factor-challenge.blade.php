<div class="container mx-auto sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-24">
  <x-shop::jet.form-section>
    <x-slot name="title">
      {{ __('Forgot Password') }}
    </x-slot>

    <x-slot name="description">
      {{ __('Please fill in the form to get reset password link.') }}
    </x-slot>

    <x-slot name="form">
      <div class="col-span-full">
        <div class="max-w-md pb-6">

          <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
          </div>

          @session('status')
            <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
              {{ $value }}
            </div>
          @endsession

          <x-shop::jet.validation-errors class="mb-4" />

          <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="block">
              <x-shop::jet.label for="email" value="{{ __('Email') }}" />
              <x-shop::jet.input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus
                autocomplete="username" />
            </div>

            <div class="flex items-center mt-4">
              <x-shop::jet.button>
                {{ __('Email Password Reset Link') }}
              </x-shop::jet.button>
            </div>
            </form<div x-data="{ recovery: false }">
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
                <x-shop::jet.input id="recovery_code" class="block mt-1 w-full" type="text" name="recovery_code" x-ref="recovery_code"
                  autocomplete="one-time-code" />
              </div>

              <div class="flex items-center justify-end mt-4">
                <button type="button" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 underline cursor-pointer"
                  x-show="! recovery"
                  x-on:click="
                                        recovery = true;
                                        $nextTick(() => { $refs.recovery_code.focus() })
                                    ">
                  {{ __('Use a recovery code') }}
                </button>

                <button type="button" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 underline cursor-pointer" x-cloak
                  x-show="recovery"
                  x-on:click="
                                        recovery = false;
                                        $nextTick(() => { $refs.code.focus() })
                                    ">
                  {{ __('Use an authentication code') }}
                </button>

                <x-shop::jet.button class="ms-4">
                  {{ __('Log in') }}
                </x-shop::jet.button>
              </div>
            </form>
        </div>
      </div>
    </x-slot>
  </x-shop::jet.form-section>
</div>
