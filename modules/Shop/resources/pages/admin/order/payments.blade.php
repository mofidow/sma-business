<x-slot name="title">{{ __('My Payments') }}</x-slot>

<div class="container mx-auto px-0 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-24">
  <x-shop::sections.action-section>
    <x-slot name="title">
      {{ __('My Payments') }}
    </x-slot>

    <x-slot name="description">
      {{ __('Please review the records in the section.') }}
      <div class="mt-6 flex items-center gap-4">
        <x-shop::elements.link :to="route('shop.home')">
          {{ __('Home') }}
        </x-shop::elements.link>
        <x-shop::elements.link :to="route('shop.orders')">
          {{ __('Sales') }}
        </x-shop::elements.link>
      </div>
    </x-slot>

    <x-slot name="content">
      @unless ($payments && $payments->count())
        <div class="rounded-lg ring-1 ring-gray-200 dark:ring-gray-700 overflow-hidden">
          <div class="bg-white dark:bg-gray-900 dark:-mx-2 dark:md:mx-0">
            <div class="p-8 text-center">
              <x-shop::elements.icon name="text-file" class="w-32 h-32 mx-auto" />
              <h3 class="text-lg font-extrabold my-4">{{ __('You do not have any payment yet.') }}</h3>
              <a href="{{ route('shop.home') }}"
                class="inline-flex mt-6 px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                {{ __('Go to Home') }}
              </a>
            </div>
          </div>
        </div>
      @else
        <div class="bg-white dark:bg-gray-900">
          <div class="w-full flex flex-col gap-6">
            @foreach ($payments as $payment)
              <div wire:key="payment-{{ $payment->id }}"
                class="block lg:flex items-center w-full text-gray-900 border dark:border-gray-700 dark:text-gray-100 bg-gray-50 hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 rounded-md select-none p-4 gap-4">
                <a href="{{ route('shop.payment', ['payment' => $payment->id]) }}" class="grow flex items-center rounded focus-default">
                  <div class="grow">
                    {{-- <div class="block mb-1"><span class="text-gray-600 dark:text-gray-400">{{ __('Number') }}:</span>
                      {{ $payment->number }}</div> --}}
                    <div class="block mb-1"><span class="text-gray-600 dark:text-gray-400">{{ __('Reference') }}:</span>
                      {{ $payment->reference }}</div>
                    <div class="block mb-1"><span class="text-gray-600 dark:text-gray-400">{{ __('Created at') }}:</span>
                      {{ $payment->created_at->toDayDateTimeString() }}</div>
                    <div class="block mb-1"><span class="text-gray-600 dark:text-gray-400">{{ __('ID') }}:</span>
                      {{ $payment->id }}
                    </div>
                    <div class="flex items-center mb-1">
                      <span class="text-gray-600 dark:text-gray-400 me-2">{{ __('Amount') }}:</span>
                      <x-shop::elements.display-price :price="$payment->amount" wire:key="dp-{{ $payment->id }}" />
                    </div>
                    @if ($payment->details)
                      <span class="block mb-1"><span class="text-gray-600 dark:text-gray-400">{{ __('Comment') }}:</span>
                        {{ $payment->details }}</span>
                    @endif
                  </div>
                </a>
                @unless ($payment->received)
                  <div class="flex mt-4 lg:mt-0 lg:flex-col items-center justify-start gap-4">
                    <a href="{{ route('shop.payment', ['payment' => $payment->id]) }}"
                      class="flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus-default">
                      {{ __('Make Payment') }}
                    </a>
                  </div>
                @endunless
              </div>
            @endforeach
          </div>
        </div>
        <div class="mt-6 col-span-full w-full flex items-center justify-between">
          {{ $payments->onEachSide(5)->links() }}
        </div>
      @endunless
    </x-slot>
  </x-shop::sections.action-section>

  @once
    @push('scripts')
      <script>
        document.addEventListener('DOMContentLoaded', function() {
          @this.on('gotoTop', function() {
            window.scrollTo({
              top: 0,
              behavior: 'smooth'
            });
          });
        });
      </script>
    @endpush
  @endonce
</div>
