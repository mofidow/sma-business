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
          {{-- <x-shop::jet.validation-errors class="mb-4" /> --}}

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
              <x-shop::jet.button class="me-4">
                {{ __('Email Password Reset Link') }}
              </x-shop::jet.button>
              <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800"
                href="{{ route('shop.signin') }}" wire:navigate w.hover>
                {{ __('Back to Login') }}
              </a>
            </div>
          </form>
        </div>
      </div>
    </x-slot>
  </x-shop::jet.form-section>
</div>
