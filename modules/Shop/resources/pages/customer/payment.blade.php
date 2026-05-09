<x-slot name="title">{{ __('Payment') }}</x-slot>

@php
  $request = request();
  $pay = $request->type == 'pay' ? true : false;
  $activeTab =
      $request->gateway != 'Cash on Delivery' ? $request->gateway : ($card_gateway ? 'Credit Card' : ($paypal ? 'PayPal' : 'Others'));
@endphp

<div class="max-w-7xl mx-auto px-0 sm:px-6 lg:px-8 py-8 sm:22 lg:py-24">
  <x-shop::jet.action-section>
    <x-slot name="title">
      {{ $payment->received ? __('Payment') : __('Payment Request') }}
    </x-slot>

    <x-slot name="description">
      {{ __('Please review the records in the section.') }}
      <div class="mt-6 flex flex-wrap items-center gap-4">
        <x-shop::shared.link :to="route('shop.orders')" class="whitespace-nowrap">
          {{ __('List Orders') }}
        </x-shop::shared.link>
        <x-shop::shared.link :to="route('shop.products')" class="whitespace-nowrap">
          {{ __('Browse Products') }}
        </x-shop::shared.link>
      </div>
    </x-slot>

    <x-slot name="content">
      <div class="-m-6">
        <div class="flex flex-col">
          <div class="sm:rounded-lg overflow-x-auto">
            <div class="align-middle inline-block min-w-full">
              <div class="mt-4 px-6 py-4 print:py-0 print:m-0">
                @if (!$payment->received)
                  <div class="col-span-full pb-6">
                    <div
                      class="block rounded-md bg-yellow-50 dark:bg-yellow-800 p-4 print:border print:border-yellow-700 border border-yellow-700">
                      <div class="flex">
                        <div class="shrink-0">
                          <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd"
                              d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z"
                              clip-rule="evenodd" />
                          </svg>
                        </div>
                        <div class="ms-3">
                          <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                            {{ __('Please make payment to complete your order.') }}
                          </h3>
                        </div>
                      </div>
                    </div>
                  </div>
                @endif

                <div class="max-h-16 max-w-[250px] mb-1">
                  @if ($payment->store->logo)
                    <img class="max-w-full h-16" src="{{ $payment->store->logo }}" alt="{{ $payment->store->name }}" />
                  @else
                    <img alt="{{ $settings['name'] }}" src="{{ $settings['logo'] }}" class="max-w-full h-16 dark:hidden" />
                    @if ($settings['logo_dark'])
                      <img alt="{{ $settings['name'] }}" src="{{ $settings['logo_dark'] }}" class="max-w-full h-16 hidden dark:block" />
                    @endif
                  @endif
                </div>
                <div class="flex items-start justify-between gap-3 mb-8">
                  <div class="flex flex-col w-3/5">
                    {{-- <div class="font-semibold text-lg">{{ $payment->store->name }}</div> --}}
                    <div class="text-sm">{{ address($payment->store) }}</div>
                  </div>
                  <div class="w-2/5">
                    <div class="font-extrabold uppercase text-lg mb-1">{{ $payment->received ? __('Payment') : __('Payment Request') }}
                    </div>
                    <div class="text-sm">{{ __('Payment No. {x}', ['x' => $payment->id]) }}</div>
                    <div class="text-sm">{{ __('Date') }}: {{ $payment->date }}</div>
                    <div class="text-sm">{{ __('Created at') }}: {{ $payment->created_at }}</div>
                    <div class="text-sm flex gap-1">
                      {{ __('Reference') }}:
                      <p class="truncate hover:text-clip print:text-clip print:block" dir="rtl">{{ $payment->reference }}</p>
                    </div>
                  </div>
                </div>

                <div class="pb-8">
                  <h2 class="text-xs font-bold mb-1">{{ __('From') }}</h2>
                  {{-- <div class="font-semibold text-lg">{{ $payment->customer->company ?: $payment->customer->name }}</div> --}}
                  <div class="text-sm">{{ address($payment->customer, true) }}</div>
                </div>

                <div class="bg-gray-50 dark:bg-gray-950 border border-gray-200 dark:border-gray-700 py-4 px-5 rounded-md">
                  <div class="text-lg font-bold flex items-center justify-between">
                    {{ __('Amount') }}: <span>{{ currency_value($payment->amount, true) }}</span>
                  </div>
                  @if ($payment->method)
                    <div class="mt-3 flex items-center justify-start gap-1">
                      {{ __('Method') }}:
                      <div class="font-bold">
                        {{ __($payment->method) }}
                      </div>
                    </div>
                  @endif
                  {{-- {{ json_encode($payment->method_data) }} --}}
                  @if ($payment->method_data)
                    <div class="mt-2 flex items-center justify-start gap-1">
                      {{-- {{ __('Method Details') }}: --}}
                      <div class="capitalize">
                        {{-- @foreach ($payment->method_data as $key => $value) --}}
                        <span>{{ __('Transaction Id') }}:
                          {{ $payment->method_data['transaction_id'] ?? ($payment->method_data['response']['id'] ?? '') }}</span>
                        {{-- @endforeach --}}
                      </div>
                    </div>
                  @endif
                </div>

                {{-- <ViewCustomFields :modal="false" :fields="custom_fields" :title="__('Custom Fields')" :extra_attributes="$payment->extra_attributes" /> --}}
                @if ($payment->details)
                  <div class="pt-8 mb-4">
                    {{ $payment->details }}
                  </div>
                @endif

                {{-- @if ($payment->attachments)
                  <div class="mt-8 py-2 w-full print:hidden">
                    <Attachments :attachments="$payment->attachments" />
                  </div>
                @endif --}}
              </div>
            </div>
          </div>
        </div>
      </div>

      @if (!$payment->received)
        <x-shop::payments.form :payment="$payment" :payment_settings="$payment_settings" :activeTab="$activeTab" />
      @else
        <div class="mt-8 flex items-center justify-between gap-x-6 gap-y-2">
          <div class="flex gap-x-6 gap-y-2">
            @if ($payment->sale_id && auth()->user())
              <a href="{{ route('shop.order', ['id' => $payment->sale_id]) }}" class="btn-primary w-full sm:w-auto">
                {{ __('View Order') }}
              </a>
            @endif
            <a href="{{ route('shop.orders', ['id' => $payment->sale_id]) }}" class="btn-primary w-full sm:w-auto">
              {{ __('List Orders') }}
            </a>
          </div>
          <x-shop::jet.button onclick="window.print()" class="w-full sm:w-auto">
            {{ __('Print') }}
          </x-shop::jet.button>
        </div>
      @endif
    </x-slot>
  </x-shop::jet.action-section>
</div>
