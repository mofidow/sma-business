<x-slot name="title">{{ __('Payment No.') }}: {{ $payment->number }}</x-slot>
<x-slot name="metaDesc"></x-slot>
<x-slot name="ogMetaData"></x-slot>

<div class="container max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 my-8 text-gray-700 dark:text-gray-300">
  <div class="bg-white dark:bg-gray-900 shadow overflow-hidden rounded-md">
    {{-- <div class="px-4 py-5 sm:px-6">
      <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
        {{ __('Sale No.') }}: {{ $payment->number }}
      </h3>
      <p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">
        {{ __('Sale Id') }}: {{ $payment->id }}
      </p>
    </div> --}}
    <div class="p-6">
      <div class="w-full flex justify-between">
        <div>
          <span class="np hidden dark:block rounded h-20">
            <img src="{{ storage_url($shop_settings->shop_logo_dark) }}" alt="{{ __($shop_settings->shop_name) }}"
              class="max-h-20 h-full object-cover" />
          </span>
          <span class="print block dark:hidden rounded h-20">
            <img src="{{ storage_url($shop_settings->shop_logo_light) }}" alt="{{ __($shop_settings->shop_name) }}"
              class="max-h-20 h-full object-cover" />
          </span>
        </div>
        <div class="grow flex justify-end text-end">
          <div class="text-sm pe-2">
            <ul>
              <li>
                <strong>{{ $settings['name'] }} ({{ $settings['short_name'] }})</strong>
              </li>
              <li>
                <strong>{{ $settings['company'] }}</strong>
              </li>
              <li>{{ $settings['email'] }} <span class="text-muted">|</span> {{ $settings['phone'] }}</li>
              <li>{{ $settings['address'] }}</li>
            </ul>
          </div>
          <div class="flex items-center justify-center">
            <div class="np hidden dark:block">
              <div class="p-1 flex flex-col items-center justify-center">
                {!! generate_barcode($payment->reference, null, 1, 70, 'white') !!}
                {{-- <code style="font-size:11px;">{{ $payment->reference }}</code> --}}
              </div>
            </div>
            <div class="print block dark:hidden">
              <div class="p-1 flex flex-col items-center justify-center">
                {!! generate_barcode($payment->reference, null, 1, 70, 'black') !!}
                {{-- <code style="font-size:11px;">{{ $payment->reference }}</code> --}}
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="mt-6 -mx-6 px-6 py-2 text-center uppercase font-extrabold border-b-2 border-t-2 dark:border-gray-700">
        {{ $payment->received ? __('Payment Note') : __('Payment Request') }}
      </div>
      <div class="grid grid-cols-2 gap-6 -mx-6 px-6 py-2 border-b dark:border-gray-700">
        <div>
          <div>{{ __('Created at') }}:
            <span class="font-bold">{{ $payment->created_at->toDayDateTimeString() }}</span>
          </div>
        </div>
        <div>
          <div>{{ __('reference') }}: <span class="font-bold">{{ $payment->reference }}</span></div>
          <div>{{ __('Id') }}: <span class="font-bold">{{ $payment->id }}</span></div>
        </div>
      </div>
      @if ($payment->review && !$payment->reviewed_by)
        <div class="rounded-md bg-yellow-50 dark:bg-yellow-900 p-4 mt-6">
          <div class="flex">
            <div class="shrink-0">
              <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                aria-hidden="true">
                <path fill-rule="evenodd"
                  d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                  clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ms-3">
              <p class="text-sm font-medium text-yellow-800 dark:text-yellow-300">
                {{ __('We will update the payment status after reviewing the submitted receipt.') }}
              </p>
            </div>
          </div>
        </div>
      @endif
      <div class="grid grid-cols-2 gap-6 -mx-6 px-6 pt-4">
        <div>
          <span class="text-sm">{{ __('Store') }}:</span>
          <div class="font-extrabold">{{ $payment->location->name }}</div>
          <div>{{ $payment->location->address }} {{ $payment->location->state_name }} {{ $payment->location->country_name }}</div>
          <div>{{ $payment->location->phone }}</div>
          <div>{{ $payment->location->email }}</div>
        </div>
        <div>
          <span class="text-sm">{{ __('For') }}:</span>
          @if ($payment->payable && $payment->payable->id != $settings['default_customer'])
            <div class="font-extrabold">{{ $payment->payable->name }}</div>
            <div>{{ $payment->payable->address }} {{ $payment->payable->state_name }} {{ $payment->payable->country_name }}
            </div>
            <div>{{ $payment->payable->phone }}</div>
            <div>{{ $payment->payable->email }}</div>
          @elseif ($payment->sale && default_customer($payment->sale->customer_id) && $payment->sale->address)
            <div class="font-extrabold">{{ $payment->sale->address->first_name }} {{ $payment->sale->address->last_name }}</div>
            <div>{{ $payment->sale->address->address }} {{ $payment->sale->address->state_name }}
              {{ $payment->sale->address->country_name }}</div>
            <div>{{ $payment->sale->address->phone }}</div>
            <div>{{ $payment->sale->address->email }}</div>
          @endif
        </div>
      </div>

      <div class="flex flex-col mt-6">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <div class="py-2 align-middle inline-block min-w-full sm:px-6 lg:px-8">
            <div class="overflow-hidden border dark:border-gray-700 rounded-md">
              <table class="min-w-full">
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                  <tr>
                    <td class="px-6 py-3">{{ __('Account') }}</td>
                    <td class="px-6 py-3">{{ __($payment->account->name) }}</td>
                  </tr>
                  <tr>
                    <td class="px-6 py-3 font-bold">
                      {{ $payment->received ? __('Payment Received') : __('Payment Requested') }}</td>
                    <td class="px-6 py-3 font-extrabold">{{ formatNumber($payment->amount) }}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      @if ($payment->details)
        <div class="mt-6 rounded-md border dark:border-gray-700">
          {{ __($payment->details) }}
        </div>
      @endif

      @if ($payment->attachments->isNotEmpty())
        <div class="mt-6 sm:col-span-2 np ps-0">
          <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
            {{ __('Attachments') }}
          </div>
          <div class="mt-1 text-sm text-gray-900 dark:text-gray-100">
            <ul class="border border-gray-200 dark:border-gray-700 rounded-md divide-y divide-gray-200 dark:divide-gray-700">
              @foreach ($payment->attachments as $attachment)
                <li class="ps-3 pe-4 py-3 flex items-center justify-between text-sm">
                  <div class="w-0 flex-1 flex items-center">
                    {{-- <PaperClipIcon class="shrink-0 h-5 w-5 text-gray-400" aria-hidden="true" /> --}}
                    <span class="ms-2 flex-1 w-0 truncate">
                      {{ $attachment->title }}
                    </span>
                  </div>
                  <div class="ms-4 shrink-0">
                    <a href="{{ route('shop.attachment', ['attachment' => $attachment->uuid]) }}"
                      class="font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-200">
                      Download
                    </a>
                  </div>
                </li>
              @endforeach
              {{-- <li class="ps-3 pe-4 py-3 flex items-center justify-between text-sm">
                <div class="w-0 flex-1 flex items-center">
                  <span class="ms-2 flex-1 w-0 truncate">
                    coverletter_back_end_developer.pdf
                  </span>
                </div>
                <div class="ms-4 shrink-0">
                  <a href="#" class="font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-200">
                    Download
                  </a>
                </div>
              </li> --}}
            </ul>
          </div>
        </div>
      @endif

      <div class="cgd text-center text-sm mt-6 text-gray-500 dark:text-gray-400 ">
        {{ __('This is a computer-generated document. No signature is required.') }}
      </div>

      <div class="np mt-6 flex items-center justify-center">
        <x-shop::elements.secondary-button type="button" onclick="window.print()" class="w-full justify-center">
          <x-shop::elements.icon name="printer" class="w-5 h-5 me-2" /> {{ __('Print') }}
        </x-shop::elements.secondary-button>
      </div>

      @if (!$payment->received && !$payment->review)
        <livewire:shop::order.payment-form :payment="$payment" :card_gateway="$settings['card_gateway'] ?? null" :paypal="$settings['services.paypal.enabled'] ?? null" />
      @endif
    </div>
  </div>
</div>
