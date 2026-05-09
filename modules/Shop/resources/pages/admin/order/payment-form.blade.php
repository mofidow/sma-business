@if ($card_gateway || $paypal || $accounts->isNotEmpty())
  @php
    $request = request();
    $pay = $request->type == 'pay' ? true : false;
    $activeTab = $request->gateway != 'cod' ? $request->gateway : ($card_gateway ? 'credit_card' : ($paypal ? 'paypal' : 'other'));
  @endphp

  <div class="np max-w-7xl mx-auto pt-6" id="pay-form">
    <div class="relative mb-6 -mx-6">
      <div class="absolute inset-0 flex items-center" aria-hidden="true">
        <div class="w-full border-t dark:border-gray-700"></div>
      </div>
      <div class="relative flex justify-center">
        <span class="px-2 bg-white dark:bg-gray-900 text-sm text-gray-500">
          {{ __('Payment Form') }}
        </span>
      </div>
    </div>
    @if (!$payment->received && !$payment->review)
      <div x-data="{ activeTab: '{{ $activeTab }}' }" class="w-full overflow-hidden">
        <div class="rounded-md border dark:border-gray-800">
          <!-- grid grid-flow-col justify-items-stretch -->
          <ul id="tabs" class="inline-flex pt-4 px-4 w-full border-b dark:border-gray-800" style="min-width:100%">
            @if ($card_gateway)
              <li class="px-4 sm:px-8 text-gray-700 dark:text-gray-300 py-3 rounded-t-md -mb-px"
                :class="activeTab == 'credit_card' ? 'bg-gray-50 dark:bg-gray-800 border-t border-e border-s dark:border-gray-800' :
                    'border-transparent'">
                <button type="button" @click="activeTab = 'credit_card'" :class="{ 'font-bold': activeTab == 'credit_card' }"
                  class="focus:outline-none">
                  {{ __('Credit Card') }}
                </button>
              </li>
            @endif
            @if ($paypal)
              <li class="px-4 sm:px-8 text-gray-700 dark:text-gray-300 py-3 rounded-t-md -mb-px"
                :class="activeTab == 'paypal' ? 'bg-gray-50 dark:bg-gray-800 border-t border-e border-s dark:border-gray-800' : 'border-transparent'">
                <button type="button" @click="activeTab = 'paypal'" :class="{ 'font-bold': activeTab == 'paypal' }"
                  class="focus:outline-none">
                  {{ __('PayPal') }}
                </button>
              </li>
            @endif
            @if ($accounts->isNotEmpty())
              <li class="px-4 sm:px-8 text-gray-700 dark:text-gray-300 py-3 rounded-t-md -mb-px"
                :class="activeTab == 'other' ? 'bg-gray-50 dark:bg-gray-800 border-t border-e border-s dark:border-gray-800' : 'border-transparent'">
                <button type="button" @click="activeTab = 'other'" :class="{ 'font-bold': activeTab == 'other' }"
                  class="focus:outline-none">
                  {{ __('Offline & Others') }}
                </button>
              </li>
            @endif
          </ul>

          <div id="pay-con" class="transition-all p-6 bg-gray-50 dark:bg-gray-800 rounded-b-md">
            @if ($card_gateway)
              <div x-show="activeTab == 'credit_card'" x-transition:enter="transition-all transform ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-full" x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition-all transform ease-in duration-75" x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 -translate-x-full">
                <div class="mb-6">
                  <x-shop::sections.section-title>
                    <x-slot name="title">{{ __('Pay with Credit Cart') }}</x-slot>
                    <x-slot name="description">
                      {{ __('Please fill the form below to make pay using card.') }}
                    </x-slot>
                  </x-shop::sections.section-title>
                </div>
                @if ($card_gateway == 'Stripe')
                  <div>
                    <div class="mb-4">
                      <form autocomplete="off" id="stripe-payment-form" method="post" wire:submit  ="pay">
                        {{-- action="{{ route('shop.pay', ['gateway' => 'stripe', 'payment' => $payment->id]) }}"> --}}
                        @csrf
                        <label for="card-element" class="block mb-1">{{ __('Pay with Credit Card') }}</label>
                        <div id="card-element" class="card-element"></div>
                        <div id="card-errors" role="alert"></div>
                        <div class="mt-4 payment-buttons">
                          <x-shop::elements.button class="w-full py-3 justify-center">
                            <span class="text-lg font-extrabold">{{ __('Submit') }}</span>
                          </x-shop::elements.button>
                        </div>
                      </form>
                    </div>
                  </div>
                @elseif (
                    $card_gateway == 'TillPayments' ||
                        $card_gateway == 'PayPal_Rest' ||
                        $card_gateway == 'Paymes' ||
                        $card_gateway == 'AuthorizeNetApi_Api' ||
                        $card_gateway == 'PayPal_Pro')
                  <div>
                    @if ($card_gateway == 'TillPayments')
                      <form autocomplete="off" id="payment-form" method="post" onsubmit="interceptSubmit(); return false;">
                        {{-- <form autocomplete="off" method="post"
                      action="{{ route('shop.pay', ['gateway' => $card_gateway, 'payment' => $payment->id]) }}"> --}}
                        @csrf
                    @endif
                    <div id="till-errors" class="pb-4 text-red-500 text-sm"></div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-6">
                      <div class="col-span-6 sm:col-span-3">
                        <x-shop::elements.label for="firstName" value="{{ __('First Name') }}" />
                        <x-shop::elements.input id="firstName" class="block w-full mt-1 py-2 sm:text-sm" type="text" name="firstName"
                          wire:model.live.defer="payer.firstName" />
                        <x-shop::elements.input-error for="payer.firstName" class="mt-2" />
                      </div>
                      <div class="col-span-6 sm:col-span-3">
                        <x-shop::elements.label for="lastName" value="{{ __('Last Name') }}" />
                        <x-shop::elements.input id="lastName" class="block w-full mt-1 px-4 py-2 sm:text-sm" type="text" name="lastName"
                          wire:model.live.defer="payer.lastName" />
                        <x-shop::elements.input-error for="payer.lastName" class="mt-2" />
                      </div>
                      <div class="col-span-6">
                        <x-shop::elements.label for="billingAddress1" value="{{ __('Billing Address') }}" />
                        <x-shop::elements.input id="billingAddress1" class="block w-full mt-1 px-4 py-2 sm:text-sm" type="text"
                          name="billingAddress1" wire:model.live.defer="payer.billingAddress1" />
                        <x-shop::elements.input-error for="payer.billingAddress1" class="mt-2" />
                      </div>
                      <div class="col-span-6 grid grid-cols-1 gap-4 sm:grid-cols-6">
                        <div class="sm:col-span-2">
                          <x-shop::elements.label for="billingCity" value="{{ __('Billing City') }}" />
                          <x-shop::elements.input id="billingCity" class="block w-full mt-1 px-4 py-2 sm:text-sm" type="text"
                            name="billingCity" wire:model.live.defer="payer.billingCity" />
                          <x-shop::elements.input-error for="payer.billingCity" class="mt-2" />
                        </div>
                        <div class="sm:col-span-1">
                          <x-shop::elements.label for="billingPostcode" value="{{ __('Billing Postal Code') }}" />
                          <x-shop::elements.input id="billingPostcode" class="block w-full mt-1 px-4 py-2 sm:text-sm" type="text"
                            name="billingPostcode" wire:model.live.defer="payer.billingPostcode" />
                          <x-shop::elements.input-error for="payer.billingPostcode" class="mt-2" />
                        </div>
                        <div class="sm:col-span-1">
                          <x-shop::elements.label for="billingState" value="{{ __('Billing State') }}" />
                          <x-shop::elements.input id="billingState" class="block w-full mt-1 px-4 py-2 sm:text-sm" type="text"
                            name="billingState" wire:model.live.defer="payer.billingState" />
                          <x-shop::elements.input-error for="payer.billingState" class="mt-2" />
                        </div>
                        <div class="sm:col-span-2">
                          <x-shop::elements.label value="{{ __('Billing Country') }}" />
                          <input type="hidden" name="billingCountry" wire:model.live.defer="payer.billingCountry">
                          <div class="mt-1 rounded-md border dark:border-gray-700">
                            <x-shop::elements.input class="block w-full px-4 py-2 sm:text-sm" name="billingCountryName"
                              value="{{ $payment->payable->country_name }}" required="required" readonly disabled />
                          </div>
                          <x-shop::elements.input-error for="payer.billingCountry" class="mt-2" />
                        </div>
                      </div>
                      @if ($card_gateway != 'Paymes')
                        <div class="col-span-6 grid grid-cols-1 gap-4 sm:grid-cols-6">
                          <div class="sm:col-span-3">
                            @if ($card_gateway == 'TillPayments')
                              <x-shop::elements.label for="number_div" value="{{ __('Credit Card Number') }}" />
                              <div id="number_div" style="height: 38px;"
                                class="till mt-1 w-full border dark:border-gray-700 bg-white dark:bg-gray-800 rounded-md"></div>
                            @else
                              <x-shop::elements.label for="number" value="{{ __('Credit Card Number') }}" />
                              <x-shop::elements.input id="number" class="block w-full mt-1 px-4 py-2 sm:text-sm" type="text"
                                name="number" wire:model.live.defer="payer.number" />
                            @endif
                            <x-shop::elements.input-error for="payer.number" class="mt-2" />
                          </div>
                          <div class="sm:col-span-2">
                            <x-shop::elements.label for="expiryMonth" value="{{ __('Expiry Month') }}" />
                            <x-shop::elements.input id="expiryMonth" class="block w-full mt-1 px-4 py-2 sm:text-sm" type="month"
                              name="expiryMonth" wire:model.live.defer="payer.expiryMonth" x-data="{ init() { this.$el.setAttribute('min', new Date().toISOString().substring(0, 7)); } }" />
                            <x-shop::elements.input-error for="payer.expiryMonth" class="mt-2" />
                          </div>
                          <div class="sm:col-span-1">
                            @if ($card_gateway == 'TillPayments')
                              <x-shop::elements.label for="cvv_div" value="{{ __('CVV') }}" />
                              <div id="cvv_div" style="height: 38px;"
                                class="mt-1 w-full border dark:border-gray-700 bg-white dark:bg-gray-800 rounded-md"></div>
                            @else
                              <x-shop::elements.label for="cvv" value="{{ __('CVV') }}" />
                              <x-shop::elements.input id="cvv" class="block w-full mt-1 px-4 py-2 sm:text-sm" type="text"
                                name="cvv" wire:model.live.defer="payer.cvv" minlength="3" maxlength="4" />
                            @endif
                            <x-shop::elements.input-error for="payer.cvv" class="mt-2" />
                          </div>
                        </div>

                        <div class="col-span-6">
                          @if ($card_gateway == 'TillPayments')
                            <x-shop::elements.button type="submit" wire:loading.attr="disabled" class="w-full py-3 justify-center">
                              <div class="flex items-center justify-center">
                                <span wire:loading class="ms-2 -me-2">
                                  <x-shop::helpers.loading-circle class="w-5 h-5 me-4 text-gray-900 dark:text-gray-100" />
                                </span>
                                <span class="text-lg font-extrabold">{{ __('Submit') }}</span>
                              </div>
                            </x-shop::elements.button>
                          @else
                            <x-shop::elements.button type="button" wire:loading.attr="disabled" wire:click="pay"
                              class="w-full py-3 justify-center">
                              <div class="flex items-center justify-center">
                                <span wire:loading class="ms-2 -me-2">
                                  <x-shop::helpers.loading-circle class="w-5 h-5 me-4 text-gray-900 dark:text-gray-100" />
                                </span>
                                <span class="text-lg font-extrabold">{{ __('Submit') }}</span>
                              </div>
                            </x-shop::elements.button>
                          @endif
                        </div>
                      @else
                        <div class="col-span-6">
                          <x-shop::elements.button type="button" wire:loading.attr="disabled" wire:click="payWithPaymes"
                            class="w-full py-3 justify-center">
                            <div class="flex items-center justify-center">
                              <span wire:loading class="ms-2 -me-2">
                                <x-shop::helpers.loading-circle class="w-5 h-5 me-4 text-gray-900 dark:text-gray-100" />
                              </span>
                              <span class="text-lg font-extrabold">{{ __('Pay Now') }}</span>
                            </div>
                          </x-shop::elements.button>
                        </div>
                      @endif
                    </div>
                    @if ($card_gateway == 'TillPayments')
                      </form>
                    @endif
                  </div>
                @endif
              </div>
            @else
              <div x-show="activeTab == 'credit_card'">{{ __('Please choose the payment option above.') }}</div>
            @endif

            @if ($paypal)
              <div x-show="activeTab == 'paypal'" x-transition:enter="transition-all transform ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-full" x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition-all transform ease-in duration-75" x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 -translate-x-full" style="display:none">
                <div class="mb-6">
                  <x-shop::sections.section-title>
                    <x-slot name="title">{{ __('Pay with PayPal') }}</x-slot>
                    <x-slot name="description">
                      {{ __('Please click the button below to make pay with PayPal.') }}
                    </x-slot>
                  </x-shop::sections.section-title>
                </div>

                <x-shop::elements.link class="w-full justify-center"
                  to="{{ route('shop.paypal.payment', ['payment' => $payment->id]) }}">
                  {{ __('Pay with') }}
                  <img src="/images/paypal-logo.svg" alt="PayPal" class="ms-2 h-8">
                </x-shop::elements.link>
              </div>
            @endif
            @if ($accounts->isNotEmpty())
              <div x-show="activeTab == 'other'" x-transition:enter="transition-all transform ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-x-full" x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition-all transform ease-in duration-75" x-transition:leave-start="opacity-100 translate-x-0"
                x-transition:leave-end="opacity-0 -translate-x-full" style="display:none">
                <div class="mb-6">
                  <x-shop::sections.section-title>
                    <x-slot name="title">{{ __('Offline Payments') }}</x-slot>
                    <x-slot name="description">
                      {{ __('You can make payment to any of the following.') }}
                    </x-slot>
                  </x-shop::sections.section-title>
                </div>
                <div>
                  <div>
                    <div x-data="{ account: '' }">
                      <div class="alerts" id="offlineError" style="display:none;">
                        <div class="my-4 p-4 bg-red-200 text-red-800">
                          {{ __('Something went wrong! please try again.') }}
                        </div>
                      </div>
                      <form autocomplete="off" method="POST" wire:submit  ="pay('offline')">
                        {{-- enctype="multipart/form-data" action="{{ route('shop.pay', ['gateway' => 'offline', 'payment' => $payment->id]) }}"> --}}
                        @csrf
                        <fieldset>
                          <legend class="sr-only">
                            {{ __('Offline Payment Account') }}
                          </legend>
                          <label for="receipt" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('Account') }}
                          </label>
                          <div class="space-y-4">
                            @foreach ($accounts as $account)
                              <label
                                class="relative block rounded-md border dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm cursor-pointer hover:border-gray-400 sm:flex sm:justify-between">
                                <input type="radio" name="account" value="{{ $account->id }}" class="sr-only"
                                  aria-labelledby="server-size-0-label" x-model="account" wire:model.live.defer="account"
                                  aria-describedby="server-size-0-description-0 server-size-0-description-1">
                                <div class="flex items-center px-6 py-4">
                                  <div class="text-sm">
                                    <p id="server-size-0-label" class="font-medium text-gray-900 dark:text-gray-300">
                                      {{ $account->name }}
                                    </p>
                                    <div id="server-size-0-description-0" class="text-gray-500 dark:text-gray-400">
                                      {{ $account->reference }}
                                      <span class="hidden sm:inline sm:mx-1" aria-hidden="true">&middot;</span>
                                      <p class="sm:inline">{{ str($account->details)->markdown()->toHtmlString() }}</p>
                                    </div>
                                  </div>
                                </div>
                                <!-- Checked: "border-indigo-500", Not Checked: "border-transparent" -->
                                <div class="absolute -inset-px rounded-md border-2 pointer-events-none"
                                  :class="account == '{{ $account->id }}' ? 'border-blue-600' : 'border-transparent'" aria-hidden="true">
                                </div>
                              </label>
                            @endforeach
                          </div>
                          @error('account')
                            <div class="text-red-500 mt-1">{{ $message }}</div>
                          @enderror
                        </fieldset>

                        <div class="my-4">
                          <label for="receipt" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            {{ __('Receipt') }}
                          </label>
                          <div class="mt-1 sm:mt-0 sm:col-span-2">
                            <div class="w-full flex justify-center px-6 pt-5 pb-6 shadow-sm border dark:border-gray-700 rounded-md">
                              <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48"
                                  aria-hidden="true">
                                  <path
                                    d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
                                    stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div x-data="{ isUploading: false, progress: 0 }" x-on:livewire-upload-start="isUploading = true"
                                  x-on:livewire-upload-finish="isUploading = false" x-on:livewire-upload-error="isUploading = false"
                                  x-on:livewire-upload-progress="progress = $event.detail.progress">
                                  <div class="flex items-center justify-center text-sm text-gray-600 dark:text-gray-400">
                                    <label for="file-upload"
                                      class="relative cursor-pointer rounded-md font-medium text-blue-600 hover:text-blue-500 focus-default">
                                      <span>{{ __('Select file') }}</span>
                                      <input id="file-upload" name="file" type="file" wire:model.live="file" multiple
                                        accept=".jpg,.jpeg,.png,.pdf" class="sr-only" />
                                    </label>
                                    <p class="ms-1">{{ __('or drag and drop') }}</p>
                                  </div>
                                  <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ __('PNG, JPG, JPEG, PDF up to 2MB') }}
                                  </p>
                                  <div>
                                    @if ($file)
                                      <ul
                                        class="block mt-4 -mx-1 text-sm rounded-md border dark:border-gray-700 divide-y divide-gray-200 dark:divide-gray-700">
                                        @foreach ($file as $attachment)
                                          <li class="px-4 py-2">
                                            {{ $loop->iteration }}. {{ $attachment->getClientOriginalName() }}
                                            ({{ ceil($attachment->getSize() / 1024) }}kb)
                                          </li>
                                        @endforeach
                                      </ul>
                                    @endif
                                    {{-- <progress value="66" max="100">Determinate</progress> --}}
                                    <div class="pt-3" x-show="isUploading">
                                      <progress max="100" x-bind:value="progress"></progress>
                                    </div>
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                          @error('file')
                            <span class="text-red-500">{{ $message }}</span>
                          @enderror
                        </div>

                        <x-shop::elements.button wire:loading.attr="disabled" class="w-full py-3 justify-center">
                          <span class="text-lg font-extrabold">{{ __('Submit') }}</span>
                        </x-shop::elements.button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            @endif
          </div>
        </div>
      </div>
    @endif
  </div>

  @push('scripts')
    @if ($pay)
      <script>
        document.addEventListener('DOMContentLoaded', function() {
          setTimeout(() => {
            document.getElementById('pay-form').scrollIntoView();
            // window.location.href = window.location.href + '#pay-form';
          }, 200);
        });
      </script>
    @endif
    @if (!$payment->received && !$payment->review && $card_gateway == 'TillPayments')
      <script data-main="payment-js" src="https://test-gateway.tillpayments.com/js/integrated/payment.1.3.min.js"></script>
      <script>
        var till_payment = new PaymentJs();
        till_payment.init('{{ mps_config('services.tillpayments.public_key') }}', 'number_div', 'cvv_div', function(payment) {
          payment.setNumberStyle({
            "font-size": "0.875rem",
            "line-height": "1.25rem",
            height: '35px',
            "border-radius": "0.375rem",
            'background-image': 'none',
            border: "0",
            padding: "0 1rem",
            display: "block",
            "width": "100%",
          });
          payment.setCvvStyle({
            "font-size": "0.875rem",
            "line-height": "1.25rem",
            height: '35px',
            "border-radius": "0.375rem",
            'background-image': 'none',
            border: "0",
            padding: "0 1rem",
            display: "block",
            "width": "100%",
          });

          // payment.numberOn('input', function (data) {
          //   console.log(data);
          // });
        });

        // var form = document.getElementById('payment-form');
        // form.addEventListener('submit', function(event) {
        //   event.preventDefault();
        function interceptSubmit() {
          let expiryMonth = document.getElementById('expiryMonth').value;
          expiryMonth = expiryMonth.split('-');
          var data = {
            first_name: document.getElementById('firstName').value,
            last_name: document.getElementById('lastName').value,
            month: expiryMonth[1],
            year: expiryMonth[0],
          };
          till_payment.tokenize(
            data, //additional data, MUST include card_holder (or first_name & last_name), month and year
            function(token, cardData) { //success callback function
              @this.pay(token);
            },
            function(errors) { //error callback function
              document.getElementById('till-errors').innerHTML = errors.map(err => err.message).join('<br />');
              document.getElementById('pay-con').scrollIntoView({
                behavior: 'smooth'
              });
              // window.scrollTo(0, 0);
              // console.log(errors);
              //render error information here
            }
          );
          return false;
        }
        // });
      </script>
    @endif
    @if (!$payment->received && !$payment->review && $card_gateway == 'Stripe')
      <script src="https://js.stripe.com/v3/"></script>
      <script>
        var stripe = Stripe('{{ mps_config('services.stripe.key') }}');
        var elements = stripe.elements();

        var style = {
          base: {
            color: '#32325d',
            fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '16px',
            '::placeholder': {
              color: '#aab7c4'
            }
          },
          invalid: {
            color: '#DC2626',
            iconColor: '#DC2626'
          }
        };

        var card = elements.create('card', {
          style: style
        });
        card.mount('#card-element');

        card.addEventListener('change', function(event) {
          var displayError = document.getElementById('card-errors');
          if (event.error) {
            displayError.textContent = event.error.message;
          } else {
            displayError.textContent = '';
          }
        });

        var form = document.getElementById('stripe-payment-form');
        form.addEventListener('submit', function(event) {
          event.preventDefault();
          stripe.createToken(card).then(function(result) {
            if (result.error) {
              var errorElement = document.getElementById('card-errors');
              errorElement.textContent = result.error.message;
            } else {
              // var hiddenInput = document.createElement('input');
              // hiddenInput.setAttribute('type', 'hidden');
              // hiddenInput.setAttribute('name', 'stripeToken');
              // hiddenInput.setAttribute('value', result.token.id);
              // form.appendChild(hiddenInput);
              // form.submit();
              @this.pay(result.token.id);
            }
          });
        });
      </script>
    @endif
  @endpush
@else
  <div></div>
@endif
