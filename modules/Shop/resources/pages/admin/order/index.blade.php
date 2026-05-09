<x-slot name="title">{{ __('My Orders') }}</x-slot>

<div class="container mx-auto px-0 sm:px-6 lg:px-8 py-8 sm:py-12 lg:py-24">
  <x-shop::sections.action-section>
    <x-slot name="title">
      {{ __('My Orders') }}
    </x-slot>

    <x-slot name="description">
      {{ __('Please review the records in the section.') }}
      <div class="mt-6 flex items-center gap-4">
        <x-shop::elements.link :to="route('shop.home')">
          {{ __('Home') }}
        </x-shop::elements.link>
        <x-shop::elements.link :to="route('shop.payments')">
          {{ __('Payments') }}
        </x-shop::elements.link>
      </div>
    </x-slot>

    <x-slot name="content">
      @unless ($sales && $sales->count())
        <div class="rounded-lg ring-1 ring-gray-200 dark:ring-gray-700 overflow-hidden">
          <div class="bg-white dark:bg-gray-900 dark:-mx-2 dark:md:mx-0">
            <div class="p-8 text-center">
              <x-shop::elements.icon name="text-file" class="w-32 h-32 mx-auto" />
              <h3 class="text-lg font-extrabold my-4">{{ __('You do not have any order yet.') }}</h3>
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
            @foreach ($sales as $sale)
              <div wire:key="sale-{{ $sale->id }}"
                class="block lg:flex items-center w-full text-gray-900 border dark:border-gray-700 dark:text-gray-100 bg-gray-50 hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 rounded-md select-none p-4 gap-4">
                <a href="{{ route('shop.order', ['order' => $sale->id]) }}" class="grow flex items-center rounded focus-default">
                  <div class="grow">
                    {{-- @unless ($sale->paid)
                      <p class="text-xs -mt-2 mb-2 font-mono text-gray-500">{{ __('Please go to payments to make payment.') }}</p>
                    @endunless --}}
                    <div class="block mb-1"><span class="text-gray-600 dark:text-gray-400">{{ __('Number') }}:</span>
                      {{ $sale->number }}</div>
                    <div class="block mb-1"><span class="text-gray-600 dark:text-gray-400">{{ __('Date') }}:</span>
                      {{ $sale->date->toFormattedDateString() }}</div>
                    <div class="block mb-1"><span class="text-gray-600 dark:text-gray-400">{{ __('Reference') }}:</span>
                      {{ $sale->reference }}</div>
                    <div class="block mb-1"><span class="text-gray-600 dark:text-gray-400">{{ __('ID') }}:</span>
                      {{ $sale->id }}
                    </div>
                    <div class="flex items-center mb-1">
                      <span class="text-gray-600 dark:text-gray-400 me-2">{{ __('Grand Total') }}:</span>
                      <x-shop::elements.display-price :price="$sale->grand_total" wire:key="dp-{{ $sale->id }}" />
                    </div>
                    @if ($sale->details)
                      <span class="block mb-1"><span class="text-gray-600 dark:text-gray-400">{{ __('Comment') }}:</span>
                        {{ $sale->details }}</span>
                    @endif
                  </div>
                </a>
                @unless ($sale->paid)
                  <div class="flex mt-4 lg:mt-0 lg:flex-col items-center justify-start gap-4">
                    <button type="button" wire:click="makePayment('{{ $sale->id }}')"
                      class="flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus-default">
                      {{ __('Make Payment') }}
                    </button>
                    <div class="flex items-stretch">
                      @if ($confirming == $sale->id)
                        <button type="button" wire:click="removeItem('{{ $sale->id }}')"
                          class="flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-s-md text-white bg-red-600 hover:bg-red-700 focus-default">
                          {{ __('Sure?') }}
                        </button>
                        <button type="button" wire:click="confirmDelete()"
                          class="flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-e-md text-white bg-blue-600 hover:bg-blue-700 focus-default">
                          <x-shop::elements.icon name="x" class="w-4 h-4" />
                        </button>
                      @else
                        <button type="button" wire:click="confirmDelete('{{ $sale->id }}')"
                          class="flex items-center justify-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-yellow-600 hover:bg-yellow-700 focus-default">
                          {{ __('Delete Order') }}
                        </button>
                      @endif
                    </div>
                  </div>
                @endunless
              </div>
            @endforeach
          </div>
        </div>
        <div class="mt-6 col-span-full w-full flex items-center justify-between">
          {{ $sales->onEachSide(5)->links() }}
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
