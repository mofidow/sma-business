<div>
  <x-slot name="title">{{ __('Checkout') }}</x-slot>
  <x-slot name="metaDesc">{{ $seo['shop_description'] ?? '' }}</x-slot>
  <div class="mx-auto max-w-7xl px-4 sm:px-6 py-8 sm:py-16 lg:px-8">
    <div x-data="{ loading: true }" x-init="setTimeout(function() { loading = false }, 250)">
      <div x-show="loading" class="w-full flex items-center justify-center py-12">
        <svg viewBox="0 0 38 38" stroke="currentColor" xmlns="http://www.w3.org/2000/svg" class="size-12 stroke-current inline ms-2">
          <g fill="none" fill-rule="evenodd">
            <g transform="translate(1 1)" stroke-width="1.5">
              <circle stroke-opacity=".3" cx="18" cy="18" r="16" />
              <path d="M34 20c0-9.94-8.06-18-18-18">
                <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s"
                  repeatCount="indefinite" />
              </path>
            </g>
          </g>
        </svg>
      </div>
      <div x-show="!loading" style="display:none" x-transition>
        <div wire:loading class="fixed inset-0 z-20 flex items-center justify-center">
          <svg viewBox="0 0 38 38" stroke="currentColor" xmlns="http://www.w3.org/2000/svg" class="size-5 stroke-current inline ms-2">
            <g fill="none" fill-rule="evenodd">
              <g transform="translate(1 1)" stroke-width="3">
                <circle stroke-opacity=".3" cx="18" cy="18" r="16" />
                <path d="M34 20c0-9.94-8.06-18-18-18">
                  <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s"
                    repeatCount="indefinite" />
                </path>
              </g>
            </g>
          </svg>
        </div>
        <div>
          @if (!$cart_items || $cart_items->isEmpty())
            <div class="flex items-center justify-between mb-6">
              <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
                {{ __('Checkout') }}
              </h2>
              <a href="{{ route('shop.products') }}" class="link x-focus text-sm">
                <span aria-hidden="true"> &larr;</span> {{ __('Continue Shopping') }}
              </a>
            </div>
            <div class="rounded-lg shadow-md ring-1 ring-gray-200 dark:ring-gray-700 overflow-hidden">
              <div class="bg-white dark:bg-gray-900 dark:-mx-2 md:dark:mx-0">
                <div class="p-8 text-center">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"
                    class="w-32 h-32 mx-auto">
                    <path stroke-linecap="round" stroke-linejoin="round"
                      d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                  </svg>
                  <h3 class="text-lg font-extrabold my-4">{{ __('Your cart is empty!') }}</h3>
                  <h4 class="text-mute font-light">{{ __('Add something to make me happy') }} :)</h4>
                  <a href="{{ route('shop.products') }}" class="btn-primary mt-6">
                    {{ __('Continue Shopping') }}
                  </a>
                </div>
              </div>
            </div>
          @else
            <div class="block sm:flex items-end justify-between mb-6 text-center sm:text-left">
              <div>
                <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
                  {{ __('Checkout') }}
                </h2>
                <p class="text-sm text-mute mt-2">
                  {{ __('Please provide shipping address, method and payment information to complete your order.') }}
                </p>
              </div>
              <div class="flex items-center justify-center gap-4 mt-4 sm:mt-0 ">
                @guest
                  @if ($shop_settings['guest_checkout'] ?? null)
                    {{ __('Continue as guest') }} <span class="text-gray-400 dark:text-gray-600">{{ __('OR') }}</span>
                  @endif
                  {{-- @if (isset($shop_settings->login_style) && $shop_settings->login_style == 'modal')
                    <x-shop::helpers.login-modal id="co">
                      <x-slot name="trigger">
                        <button type="button"
                          class="inline-flex px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus-default">
                          {{ __('Sign in') }}
                        </button>
                      </x-slot>
                    </x-shop::helpers.login-modal>
                  @else --}}
                  <a href="{{ route('shop.signin') }}" wire:navigate w.hover class="btn-primary">
                    {{ __('Sign in') }}
                  </a>
                  {{-- @endif --}}
                @endguest
              </div>
            </div>

            <div wire:key="cart-view" class="flex flex-col lg:flex-row items-stretch gap-y-6 gap-x-8">
              <div class="w-full lg:w-3/4 px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8 border bg-white dark:bg-gray-900 rounded-lg">
                @if ($errors->any())
                  <div class="border border-red-500 rounded-md text-red-500 py-3 px-4 mb-6 text-sm font-bold">
                    <ul>
                      @foreach ($errors->all() as $error)
                        <li>{{ str($error)->replace('form.', '')->replace('shop ', '')->replace(' id ', ' ') }}</li>
                      @endforeach
                    </ul>
                  </div>
                @endif
                <div class="flex items-center justify-between mb-6" x-data="{ open: false, close() { this.open = false; } }">
                  <h4 class="text-lg font-bold">{{ __('Shipping Address') }}</h4>
                  @auth
                    @if ($addresses->count() < 5)
                      <x-shop::jet.button @click="open = !open" :loading="false">
                        {{ __('Add New Address') }}
                      </x-shop::jet.button>
                      <div x-show="open">
                        <x-shop::alpine.modal :backdrop="false" maxWidth="sm:max-w-2xl">
                          <livewire:components.customer.address-form />
                        </x-shop::alpine.modal>
                      </div>
                    @endif
                  @endauth
                </div>
                @guest
                  <div class="grid grid-cols-6 gap-6 mb-6 py-6 border-t border-b dark:border-gray-700">
                    <div class="col-span-6 sm:col-span-3">
                      <x-shop::jet.label for="name" value="{{ __('Full Name') }}" />
                      <x-shop::jet.input id="name" class="block w-full mt-1 py-2 sm:text-sm" type="text" name="name"
                        wire:model="form.address.name" />
                      <x-shop::jet.input-error for="form.address.name" class="mt-2" />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                      <x-shop::jet.label for="email" value="{{ __('Email address') }}" />
                      <x-shop::jet.input id="email" class="block w-full mt-1 py-2 sm:text-sm" type="email" name="email"
                        wire:model="form.address.email" />
                      <x-shop::jet.input-error for="form.address.email" class="mt-2" />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                      <x-shop::jet.label for="phone" value="{{ __('Phone') }}" />
                      <x-shop::jet.input id="phone" class="block w-full mt-1 py-2 sm:text-sm" type="text" name="phone"
                        wire:model="form.address.phone" />
                      <x-shop::jet.input-error for="form.address.phone" class="mt-2" />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                      <x-shop::jet.label for="country" value="{{ __('Country') }}" />
                      <x-shop::jet.select id="country" class="input" name="country" wire:model.live="form.address.country_id">
                        <option value="">{{ __('Select') }}</option>
                        @foreach ($countries as $country)
                          <option value="{{ $country->id }}">{{ $country->name }}</option>
                        @endforeach
                      </x-shop::jet.select>
                      <x-shop::jet.input-error for="form.address.country" class="mt-2" />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                      <x-shop::jet.label for="state" value="{{ __('State') }}" />
                      <x-shop::jet.select id="state" class="input" type="text" name="state"
                        wire:model.live="form.address.state_id">
                        <option value="">{{ __('Select') }}</option>
                        @if ($shipping_country && !empty($shipping_country->states))
                          @foreach ($shipping_country->states as $state)
                            <option value="{{ $state->id }}">{{ $state->name }}</option>
                          @endforeach
                        @endif
                      </x-shop::jet.select>
                      <x-shop::jet.input-error for="form.address.state_id" class="mt-2" />
                    </div>

                    <div class="col-span-6">
                      <x-shop::jet.label for="address" value="{{ __('Street Address') }}" />
                      <x-shop::jet.input id="address" class="block w-full mt-1 py-2 sm:text-sm" type="text" name="address"
                        wire:model="form.address.address" />
                      <x-shop::jet.input-error for="form.address.address" class="mt-2" />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                      <x-shop::jet.label for="city" value="{{ __('City') }}" />
                      <x-shop::jet.input id="city" class="block w-full mt-1 py-2 sm:text-sm" type="text" name="city"
                        wire:model="form.address.city" />
                      <x-shop::jet.input-error for="form.address.city" class="mt-2" />
                    </div>

                    <div class="col-span-6 sm:col-span-3">
                      <x-shop::jet.label for="postal_code" value="{{ __('Postal Code') }}" />
                      <x-shop::jet.input id="postal_code" class="block w-full mt-1 py-2 sm:text-sm" type="text" name="postal_code"
                        wire:model="form.address.postal_code" />
                      <x-shop::jet.input-error for="form.address.postal_code" class="mt-2" />
                    </div>

                  </div>
                @else
                  <div class="rounded-md overflow-hidden mb-6 border dark:border-gray-700">
                    @forelse ($addresses as $address)
                      <div
                        class="flex items-center leading-relaxed p-4 {{ $loop->index ? 'border-t dark:border-gray-700' : '' }} hover:bg-gray-50 dark:hover:bg-gray-800">
                        <input wire:model.live="form.address_id" id="address-{{ $address->id }}" name="form.address_id" type="radio"
                          value="{{ $address->id }}"
                          class="focus:ring-blue-300 focus:ring-4 focus:ring-offset-0 h-5 w-5 text-blue-600 focus:border-0">
                        <label for="address-{{ $address->id }}"
                          class="ms-3 block text-sm font-medium text-gray-700 dark:text-gray-300 grow cursor-pointer sm:ps-2">
                          <div class="font-bold">{{ $address->first_name }}
                            {{ $address->last_name }}</div>
                          <div> {{ $address->lot_no }} {{ $address->street }} </div>
                          <div> {{ $address->address_line_1 }}</div>
                          <div> {{ $address->address_line_2 }}</div>
                          <div> {{ $address->city }} {{ $address->postal_code }} {{ $address->state?->name }}
                            {{ $address->country->name }}</div>
                          <div> {{ __('Tel:') }} {{ $address->phone }} @if ($address->email)
                              {{ __('Email:') }}
                              {{ $address->email }}
                            @endif
                          </div>
                        </label>
                      </div>
                    @empty
                      <div class="w-full text-start p-4 text-sm font-bold text-gray-700 dark:text-gray-300">
                        {{ __('Please add address first.') }}
                      </div>
                    @endforelse
                  </div>
                  <x-shop::jet.input-error for="form.address_id" class="-mt-4 mb-6" />
                  <x-shop::jet.input-error for="address_id" class="-mt-4 mb-6" />
                @endguest

                <div class="block w-full md:flex gap-6">
                  <div class="w-full md:w-1/2">
                    <x-shop::jet.label for="shipping_method_id" value="{{ __('Shipping Method') }}" />
                    <x-shop::jet.select id="shipping_method_id" class="input" type="text" name="shipping_method_id"
                      wire:model.live="form.shop_shipping_method_id">
                      <option value="">{{ __('Select') }}</option>
                      @if (!empty($available_shipping))
                        @foreach ($available_shipping as $sm)
                          <option value="{{ $sm->id }}">
                            {{ __($sm->provider_name) }} ({{ currency_value($sm->rate) }})
                          </option>
                          {{-- <option disabled value="">
                            &nbsp;&nbsp;&nbsp;↳ {{ __('Charges') }} {{ currency_value($sm->rate) }}
                        </option> --}}
                        @endforeach
                      @endif
                    </x-shop::jet.select>
                    <x-shop::jet.input-error for="form.shop_shipping_method_id" class="mt-2" />
                    <x-shop::jet.input-error for="shop_shipping_method_id" class="mt-2" />
                  </div>

                  <div class="w-full md:w-1/2">
                    <x-shop::jet.label for="shop-coupon" value="{{ __('Coupon Code') }}" />
                    @if ($this->form['coupon_code'])
                      <div class="flex items-stretch w-full">
                        <div
                          class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-s-md sm:text-sm select-none">
                          {{ $this->form['coupon_code'] }}</div>
                        <button type="button" wire:click="applyCoupon(true)"
                          class="flex items-center justify-center px-4 py-2 border border-gray-700 text-sm font-medium rounded-e-md text-white bg-gray-700 hover:bg-red-700 hover:border-red-700 hover:-m-px focus-default">
                          {{ __('Remove') }}
                        </button>
                      </div>
                    @else
                      <div class="flex items-stretch w-full">
                        <input type="text" wire:model="form.coupon_code" class="input rounded-e-none!" id="shop-coupon" />
                        <button type="button" wire:click="applyCoupon()"
                          class="flex items-center justify-center px-4 py-2 border border-gray-200 dark:border-gray-700 text-sm font-medium rounded-e-md bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-900 focus:z-10">
                          {{ __('Apply') }}
                        </button>
                      </div>
                    @endif
                    <x-shop::jet.input-error for="shop_coupon_id" class="mt-2" />
                  </div>
                </div>
                <div class="w-full mt-6">
                  <label class="font-medium inline-block mb-1 text-sm uppercase">{{ __('Instruction for Seller') }}</label>
                  <textarea wire:model="form.details"
                    class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-md sm:text-sm focus-default"></textarea>
                </div>
                <div class="mt-6 pt-6 border-t dark:border-gray-700">
                  <h4 class="text-lg font-bold mb-6">{{ __('Payment Method') }}</h4>
                  {{-- <div x-data="{
                    activeTab: 'paypal',
                    tabs: [
                        { value: 'paypal', label: 'PayPal'},
                        { value: 'stripe', label: 'Credit Card'},
                    ]
                }">
                    <ul class="flex justify-center items-center gap-4 my-4">
                        <template x-for="(tab, index) in tabs" :key="index">
                            <li class="cursor-pointer shadow py-2 px-6 rounded-full transition focus-default"
                                :class="activeTab == tab.value ? 'bg-blue-500 text-white' : ' text-gray-500'"
                                @click="activeTab = tab.value" x-html="tab.label"></li>
                        </template>
                    </ul>

                    <div class="w-80 bg-white p-16 text-center mx-auto border">
                        <div x-show="activeTab == 'paypal'">PayPal</div>
                        <div x-show="activeTab == 'stripe'">Stripe</div>
                    </div>
                </div> --}}
                  <div x-data="{ method: @entangle('form.payment_method') }">
                    <div class="rounded-md overflow-hidden mb-6 border dark:border-gray-700">
                      <div class="flex items-center leading-relaxed px-4 py-1 hover:bg-gray-100 dark:hover:bg-gray-800">
                        <input x-model="method" wire:model="form.payment_method" id="paypal" name="payment_method" value="PayPal"
                          type="radio"
                          class="focus:ring-blue-300 focus:ring-4 focus:ring-offset-0 h-5 w-5 text-blue-600 focus:border-0">
                        <label for="paypal"
                          class="ms-3 py-3 block text-sm font-medium text-gray-700 dark:text-gray-300 grow cursor-pointer sm:ps-2">
                          <div class="font-bold">{{ __('PayPal') }}</div>
                        </label>
                      </div>
                      @if ($settings['payment']['gateway'] ?? null)
                        <div
                          class="flex items-center leading-relaxed px-4 py-1 border-t dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800">
                          <input x-model="method" wire:model="form.payment_method" id="credit_card" name="payment_method"
                            value="Credit Card" type="radio"
                            class="focus:ring-blue-300 focus:ring-4 focus:ring-offset-0 h-5 w-5 text-blue-600 focus:border-0">
                          <label for="credit_card"
                            class="ms-3 py-3 block text-sm font-medium text-gray-700 dark:text-gray-300 grow cursor-pointer sm:ps-2">
                            <div class="font-bold">{{ __('Credit Card') }}</div>
                          </label>
                        </div>
                      @endif
                      @auth
                        {{-- <div
                          class="flex items-center leading-relaxed px-4 py-1 border-t dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800">
                          <input x-model="method" wire:model="form.payment_method" id="cod" name="payment_method"
                            value="Cash on Delivery" type="radio"
                            class="focus:ring-blue-300 focus:ring-4 focus:ring-offset-0 h-5 w-5 text-blue-600 focus:border-0">
                          <label for="cod"
                            class="ms-3 py-3 block text-sm font-medium text-gray-700 dark:text-gray-300 grow cursor-pointer sm:ps-2">
                            <div class="font-bold">{{ __('Cash on Delivery') }}</div>
                          </label>
                        </div> --}}
                        <div
                          class="flex items-center leading-relaxed px-4 py-1 border-t dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800">
                          <input x-model="method" wire:model="form.payment_method" id="balance" name="payment_method" value="Balance"
                            type="radio"
                            class="focus:ring-blue-300 focus:ring-4 focus:ring-offset-0 h-5 w-5 text-blue-600 focus:border-0">
                          <label for="balance"
                            class="ms-3 py-3 block text-sm font-medium text-gray-700 dark:text-gray-300 grow cursor-pointer sm:ps-2">
                            <div class="font-bold">{{ __('Pay with Balance') }}</div>
                          </label>
                        </div>
                      @endauth
                      <div
                        class="flex items-center leading-relaxed px-4 py-1 border-t dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800">
                        <input x-model="method" wire:model="form.payment_method" id="other" name="payment_method" value="Others"
                          type="radio"
                          class="focus:ring-blue-300 focus:ring-4 focus:ring-offset-0 h-5 w-5 text-blue-600 focus:border-0">
                        <label for="other"
                          class="ms-3 py-3 block text-sm font-medium text-gray-700 dark:text-gray-300 grow cursor-pointer sm:ps-2">
                          <div class="font-bold">{{ __('Offline & other payment method') }}</div>
                        </label>
                      </div>
                    </div>
                    <x-shop::jet.input-error for="form.payment_method" class="-mt-4 mb-6" />
                    {{-- <div x-show="method == 'stripe'" style="display: none" class="block sm:flex sm:items-strech mb-6 -m-1 p-1"
                    x-transition:enter="transition transform ease-out duration-300" x-transition:enter-start="opacity-0 -translate-y-8"
                    x-transition:enter-end="opacity-200 translate-y-0" x-transition:leave="transition transform ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-8">
                    <input type="text" id="cc_ no"
                      class="w-full sm:w-3/6 lg:w-4/6 grow py-2 px-3 rounded-t-md sm:rounded-t-none sm:rounded-s-md border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 sm:text-sm md:text-base focus:z-10 focus-default"
                      placeholder="{{ __('Card Number') }}">
                    <input type="month" id="cc_expiry"
                      class="w-full sm:w-2/6 inline-block py-2 px-3 border-s border-e border-t-0 border-b-0 sm:border-l-0 sm:border-r-0 sm:border-t sm:border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 sm:text-sm md:text-base focus:z-10 focus-default"
                      placeholder="{{ __('Expiry Month') }}">
                    <input type="text" id="cc_cvv"
                      class="w-full sm:w-1/6 inline-block py-2 px-3 rounded-b-md sm:rounded-b-none sm:rounded-e-md sm:rounded-none border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 sm:text-sm md:text-base focus:z-10 focus-default"
                      placeholder="{{ __('CVV') }}">
                  </div> --}}
                  </div>
                </div>
                <button type="button" wire:click="submit()" class="w-full btn-primary btn-lg justify-center">
                  <x-shop::shared.icon name="plane" class="w-6 h-6 -ms-2 me-4" />
                  {{ __('Submit') }}
                </button>
              </div>

              <div x-data="{ details: false }" class="w-full lg:w-1/4">
                <div class="sticky top-4">
                  <div class="flex items-center justify-between gap-4" :class="{ 'pb-4': details }">
                    <h4 class="text-lg font-bold">
                      {{ __('Order Summary') }}
                    </h4>
                    <button class="w-8 h-8 flex items-center justify-center rounded-md focus-default" @click="details = !details">
                      <x-shop::shared.icon x-show="details" name="up" class="w-5 h-5" />
                      <x-shop::shared.icon x-show="!details" name="down" class="w-5 h-5" />
                    </button>
                  </div>
                  <div x-show="details" x-transition style="display: none" class="pt-4 border-t dark:border-gray-700 text-sm">
                    @foreach ($data['items'] as $item)
                      @php
                        $cart_item = $cart_items->where('product_id', $item['product_id'])->first();
                      @endphp
                      @if ($cart_item->selected && ($cart_item->selected['variations'] ?? null))
                        @foreach ($cart_item->selected['variations'] as $v)
                          @php
                            $variation = $cart_item->product->variations->where('id', $v['id'])->first();
                          @endphp
                          <div wire:key="cartItem-variation-{{ $loop->index }}" class="flex items-start py-2">
                            @if ($cart_item->product->photo)
                              <a href="{{ route('shop.product', $cart_item->product->slug) }}" wire:navigate w.hover
                                class="shrink-0 w-10 h-10 flex items-center justify-center rounded focus-default">
                                <img class="max-w-full max-h-full rounded object-cover" src="{{ $cart_item->product->photo }}"
                                  alt="{{ $cart_item->product->name }}">
                              </a>
                            @endif
                            <div class="flex flex-col ms-4 grow">
                              <a href="{{ route('shop.product', $cart_item->product->slug) }}" wire:navigate w.hover
                                class="font-bold hover:text-blue-600 rounded focus-default">
                                {{ $cart_item->product->name }}
                                <p class="text-xs text-mute">
                                  @php
                                    $meta = [];
                                    foreach ($variation->meta as $variant => $option) {
                                        $meta[] = __($variant . ': ' . $option);
                                    }
                                  @endphp
                                  {{ implode(', ', $meta) }}
                                </p>
                              </a>
                              <div class="mt-0.5 flex item-center justify-between gap-4">
                                {{ $v['quantity'] ?? $cart_item->quantity }} x
                                {{ currency_value($v['unit_price'] ?? $cart_item->product->price) }}
                                <strong>
                                  {{ currency_value($v['total'] ?? ($v['price'] ?? $cart_item->product->price) * ($v['quantity'] ?? $cart_item->quantity)) }}
                                </strong>
                              </div>
                            </div>
                          </div>
                        @endforeach
                      @else
                        <div wire:key="cartItem-variation-{{ $loop->index }}" class="flex items-start py-2">
                          @if ($cart_item->product->photo)
                            <a href="{{ route('shop.product', $cart_item->product->slug) }}" wire:navigate w.hover
                              class="shrink-0 w-10 h-10 flex items-center justify-center rounded focus-default">
                              <img class="max-w-full max-h-full rounded object-cover" src="{{ $cart_item->product->photo }}"
                                alt="{{ $cart_item->product->name }}">
                            </a>
                          @endif
                          <div class="flex flex-col ms-4 grow">
                            <a href="{{ route('shop.product', $cart_item->product->slug) }}" wire:navigate w.hover
                              class="font-bold hover:text-blue-600 rounded focus-default">
                              {{ $cart_item->product->name }}
                            </a>
                            <div class="flex item-center justify-between gap-4">
                              {{ $item['quantity'] ?? $cart_item->quantity }} x
                              {{ currency_value($item['net_price'] ?? $cart_item->product->price) }}
                              {{ ($item['tax_amount'] ?? 0) > 0 ? '+ ' . currency_value($item['tax_amount']) : '' }}
                              <strong>
                                {{ currency_value($item['total'] ?? ($item['price'] ?? $cart_item->product->price) * ($item['quantity'] ?? $cart_item->quantity)) }}
                              </strong>
                            </div>
                          </div>
                        </div>
                      @endif
                    @endforeach
                  </div>

                  <div class="border-t mt-4 dark:border-gray-700 py-4">
                    <div class="flex font-bold justify-between py-2">
                      <span>{{ __('Total') }}</span>
                      <span>{{ currency_value(($this->data['subtotal'] ?? 0) + ($this->data['total_discount_amount'] ?? 0)) }}</span>
                    </div>
                    <div class="flex font-bold justify-between py-2">
                      <span>{{ __('Tax') }}</span>
                      <span>{{ currency_value($this->data['total_tax_amount'] ?? 0) }}</span>
                    </div>
                    @if ($this->data['total_discount_amount'] ?? null)
                      <div class="flex font-bold justify-between py-2">
                        <span>{{ __('Discount') }}</span>
                        <span>{{ currency_value($this->data['total_discount_amount']) }}</span>
                      </div>
                    @endif
                    @if ($shipping_method)
                      <div class="flex font-bold justify-between py-2">
                        <span>{{ __('Shipping') }}</span>
                        @if ($shipping_method->free_order_amount && $this->data['total'] >= $shipping_method->free_order_amount)
                          <span><del
                              class="decoration-red-500 decoration-1 decoration-wavy">{{ currency_value($shipping_method->rate) }}</del>
                            {{ currency_value(0) }}</span>
                        @else
                          <span>{{ currency_value($shipping_method->rate) }}</span>
                        @endif
                      </div>
                    @endif
                  </div>

                  <div class="border-t dark:border-gray-700">
                    <div class="flex font-bold justify-between text-lg py-6 uppercase">
                      <span>{{ __('Payable') }}</span>
                      <span>
                        {{ currency_value(($this->data['grand_total'] ?: $cart_items->sum(fn($item) => $item->oId ? 0 : $item->product->price * $item->quantity)) + ($shipping_method->rate ?? 0)) }}
                      </span>
                    </div>

                    <div class="flex font-bold justify-between text-lg uppercase">
                      <span>{{ __('Charge Amount') }}</span>
                      <span>
                        {{ currency_value(($this->data['grand_total'] ?: $cart_items->sum(fn($item) => $item->oId ? 0 : $item->product->price * $item->quantity)) + ($shipping_method->rate ?? 0), true) }}
                      </span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>
  @script
    <script>
      $wire.on('goto-top', () => {
        window.scrollTo({
          top: 0,
          behavior: 'smooth'
        });
      });
    </script>
  @endscript

  @once
    @push('scripts')
      <script>
        document.addEventListener('DOMContentLoaded', function() {
          document.addEventListener('livewire:init', function() {
            @unless (session()->has('cart_id'))
              var cartId = localStorage.getItem('cartId');
              if (cartId) {
                @this.cartId = cartId;
              }
            @endunless
            @this.on('goto-top', function() {
              window.scrollTo({
                top: 0,
                behavior: 'smooth'
              });
            });
          });
        });
      </script>
    @endpush
  @endonce
</div>
