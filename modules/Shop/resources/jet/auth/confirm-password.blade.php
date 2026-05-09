<div class="container mx-auto sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-24">
  <x-shop::jet.form-section>
    <x-slot name="title">
      {{ __('Confirm Password') }}
    </x-slot>

    <x-slot name="description">
      {{ __('Please fill in the form to confirm your password.') }}
    </x-slot>

    <x-slot name="form">
      <div class="col-span-full">
        <div class="max-w-md pb-6">
          <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
          </div>

          <x-shop::jet.validation-errors class="mb-4" />

          <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <div>
              <x-shop::jet.label for="password" value="{{ __('Password') }}" />
              <x-shop::jet.input id="password" class="block mt-1 w-full" type="password" name="password" required
                autocomplete="current-password" autofocus />
            </div>

            <div class="flex mt-4">
              <x-shop::jet.button class="ms-4">
                {{ __('Confirm') }}
              </x-shop::jet.button>
            </div>
          </form>
        </div>
      </div>
    </x-slot>
  </x-shop::jet.form-section>
</div>
