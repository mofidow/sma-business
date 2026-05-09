<div class="container mx-auto sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-24">
  <x-shop::jet.form-section>
    <x-slot name="title">
      {{ __('Verify Email') }}
    </x-slot>

    <x-slot name="description">
      {{ __('Please verify your email address.') }}
    </x-slot>

    <x-slot name="form">
      <div class="col-span-full">
        <div class="pb-6">
          <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Before continuing, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
          </div>

          @if (session('status') == 'verification-link-sent')
            <div class="mb-4 font-medium text-sm text-green-600 dark:text-green-400">
              {{ __('A new verification link has been sent to the email address you provided in your profile settings.') }}
            </div>
          @endif

          <div class="mt-4 flex items-center justify-between">
            <form method="POST" action="{{ route('verification.send') }}">
              @csrf

              <div>
                <x-shop::jet.button type="submit">
                  {{ __('Resend Verification Email') }}
                </x-shop::jet.button>
              </div>
            </form>

            <div>
              <a href="{{ route('shop.profile') }}"
                class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800">
                {{ __('Edit Profile') }}</a>

              <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf

                <button type="submit"
                  class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 ms-2">
                  {{ __('Log Out') }}
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
    </x-slot>
  </x-shop::jet.form-section>
</div>
