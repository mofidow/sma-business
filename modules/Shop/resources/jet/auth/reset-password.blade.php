<div class="container mx-auto sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-24">
  <x-shop::jet.form-section>
    <x-slot name="title">
      {{ __('Reset Password') }}
    </x-slot>

    <x-slot name="description">
      {{ __('Please fill in the form to reset your password.') }}
    </x-slot>

    <x-slot name="form">
      <div class="col-span-full">
        <div class="max-w-md pb-6">
          <x-shop::jet.validation-errors class="mb-4" />

          <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <input type="hidden" name="token" value="{{ $token }}">

            <div class="block">
              <x-shop::jet.label for="email" value="{{ __('Email') }}" />
              <x-shop::jet.input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $email)" required autofocus
                autocomplete="username" />
            </div>

            <div class="mt-4">
              <x-shop::jet.label for="password" value="{{ __('Password') }}" />
              <x-shop::jet.input id="password" class="block mt-1 w-full" type="password" name="password" required
                autocomplete="new-password" />
            </div>

            <div class="mt-4">
              <x-shop::jet.label for="password_confirmation" value="{{ __('Confirm Password') }}" />
              <x-shop::jet.input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required
                autocomplete="new-password" />
            </div>

            <div class="flex items-center mt-4">
              <x-shop::jet.button>
                {{ __('Reset Password') }}
              </x-shop::jet.button>
            </div>
          </form>
        </div>
      </div>
    </x-slot>
  </x-shop::jet.form-section>
</div>
