@props(['payment', 'payment_settings', 'activeTab' => 'Credit Card'])

<div x-data="{ activeTab: '{{ $activeTab }}' }" class="print:hidden">
  <hr class="my-8" />
  <div class="text-lg font-extrabold mb-3">{{ __('Make Payment') }}</div>
  <div class="grid grid-cols-1 sm:hidden">
    <x-shop::jet.select aria-label="Select a tab" @change="activeTab = $event.target.value"
      class="col-start-1 row-start-1 w-full appearance-none rounded-md bg-white py-2 pe-8 ps-3 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-primary-600 dark:bg-white/5 dark:text-gray-100 dark:outline-white/10 dark:*:bg-gray-800 dark:focus:outline-primary-500">
      <option :selected="activeTab == 'Credit Card'" {{ $activeTab == 'Credit Card' ? 'selected' : '' }}>{{ __('Credit Card') }}
      </option>
      <option :selected="activeTab == 'PayPal'" {{ $activeTab == 'PayPal' ? 'selected' : '' }}>{{ __('PayPal') }}</option>
      <option :selected="activeTab == 'Others'" {{ $activeTab == 'Others' ? 'selected' : '' }}>{{ __('Others') }}</option>
    </x-shop::jet.select>
    <svg viewBox="0 0 16 16" fill="currentColor" data-slot="icon" aria-hidden="true"
      class="pointer-events-none col-start-1 row-start-1 me-2 size-5 self-center justify-self-end fill-gray-500 dark:fill-gray-400">
      <path
        d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z"
        clip-rule="evenodd" fill-rule="evenodd" />
    </svg>
  </div>
  <div class="hidden sm:block">
    <div class="border-b border-gray-200 dark:border-white/10">
      <nav aria-label="Tabs" class="-mb-px flex space-x-8">
        @if (($payment_settings['gateway'] ?? null) || $payment_settings['services']['stripe']['enabled'] ?? false)
          <button type="button" @click="activeTab = 'Credit Card'"
            class="group inline-flex items-center border-b-2 px-1 py-4 text-sm font-medium x-focus"
            :class="activeTab == 'Credit Card' ? 'border-primary-500 dark:border-primary-400 text-primary-600 dark:text-primary-400' :
                'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-white/20 hover:text-gray-700 dark:hover:text-gray-300'">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" fill="currentColor" class="me-2 -ms-0.5 size-5">
              <rect width="256" height="256" fill="none" />
              <path
                d="M224,48H32A16,16,0,0,0,16,64V192a16,16,0,0,0,16,16H224a16,16,0,0,0,16-16V64A16,16,0,0,0,224,48ZM136,176H120a8,8,0,0,1,0-16h16a8,8,0,0,1,0,16Zm64,0H168a8,8,0,0,1,0-16h32a8,8,0,0,1,0,16ZM32,88V64H224V88Z" />
            </svg>
            <span>{{ __('Credit Card') }}</span>
          </button>
        @endif
        @if ($payment_settings['services']['paypal']['enabled'] ?? false)
          <button type="button" @click="activeTab = 'PayPal'"
            class="group inline-flex items-center border-b-2 px-1 py-4 text-sm font-medium x-focus"
            :class="activeTab == 'PayPal' ? 'border-primary-500 dark:border-primary-400 text-primary-600 dark:text-primary-400' :
                'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-white/20 hover:text-gray-700 dark:hover:text-gray-300'">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" fill="currentColor" class="me-2 -ms-0.5 size-5">
              <rect width="256" height="256" fill="none" />
              <path
                d="M220.12,93.54a55.8,55.8,0,0,0-20.19-16.18A56,56,0,0,0,144,24H84A16,16,0,0,0,68.48,36.12l-36,144A16,16,0,0,0,48,200h27.5l-3,12.12A16,16,0,0,0,88,232h31.5A16,16,0,0,0,135,219.88L144,184h32a56,56,0,0,0,44.14-90.46ZM48,184,84,40h60a40,40,0,0,1,39.3,32.49A57,57,0,0,0,176,72H120a16,16,0,0,0-15.53,12.12L79.52,184H48Zm166.77-46.3A39.94,39.94,0,0,1,176,168H144a16,16,0,0,0-15.52,12.12l-9,35.88H88l20-80h36a55.9,55.9,0,0,0,54-41.39,40.2,40.2,0,0,1,9.48,8.77A39.73,39.73,0,0,1,214.78,137.7Z" />
            </svg>
            <span>{{ __('PayPal') }}</span>
          </button>
        @endif
        <button type="button" @click="activeTab = 'Others'"
          class="group inline-flex items-center border-b-2 px-1 py-4 text-sm font-medium x-focus"
          :class="activeTab == 'Others' ? 'border-primary-500 dark:border-primary-400 text-primary-600 dark:text-primary-400' :
              'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-white/20 hover:text-gray-700 dark:hover:text-gray-300'">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 256" fill="currentColor" class="me-2 -ms-0.5 size-5">
            <rect width="256" height="256" fill="none" />
            <path
              d="M168,128a40,40,0,1,1-40-40A40,40,0,0,1,168,128Zm80-64V192a8,8,0,0,1-8,8H16a8,8,0,0,1-8-8V64a8,8,0,0,1,8-8H240A8,8,0,0,1,248,64Zm-16,46.35A56.78,56.78,0,0,1,193.65,72H62.35A56.78,56.78,0,0,1,24,110.35v35.3A56.78,56.78,0,0,1,62.35,184h131.3A56.78,56.78,0,0,1,232,145.65Z" />
          </svg>
          <span>{{ __('Others') }}</span>
        </button>
      </nav>
    </div>
  </div>

  @if ($payment_settings['services']['stripe']['enabled'] ?? false)
    <div x-show="activeTab == 'Credit Card'" class="mt-6">
      <livewire:components.payments.stripe :payment="$payment" :payment_settings="$payment_settings" wire:ignore />
    </div>
    {{-- @if ($payment_settings['gateway'] == 'Stripe')
              TODO: Other Credit Card Gateways Coming Soon...
            @endif --}}
  @endif
  @if (($payment_settings['gateway'] ?? null) && $payment_settings['gateway'] != 'Stripe')
    <div x-show="activeTab == 'Credit Card'" class="mt-6">
      <livewire:components.payments.custom :payment="$payment" :payment_settings="$payment_settings" />
    </div>
  @endif
  @if ($payment_settings['services']['paypal']['enabled'] ?? false)
    <div x-show="activeTab == 'PayPal'" class="mt-6">
      <livewire:components.payments.paypal :payment="$payment" :payment_settings="$payment_settings" />
    </div>
  @endif

  <div x-show="activeTab == 'Others'" class="mt-6">
    <livewire:components.payments.others :payment="$payment" :payment_settings="$payment_settings" />
  </div>
</div>
