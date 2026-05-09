<x-slot name="title">{{ __('Shopping Cart') }}</x-slot>
<x-slot name="metaDesc">{{ $seo['description'] ?? '' }}</x-slot>
<div wire:key="cart" class="mx-auto max-w-7xl px-4 sm:px-6 py-8 sm:py-16 lg:px-8">
  @if (!$cart_items || $cart_items->isEmpty())
    <div class="flex items-center justify-between mb-6">
      <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
        {{ __('Shopping Cart') }}
      </h2>
      <a href="{{ route('shop.products') }}" wire:navigate w.hover class="link x-focus text-sm">
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
          <a href="{{ route('shop.products') }}" wire:navigate w.hover class="btn-primary mt-6">
            {{ __('Continue Shopping') }}
          </a>
        </div>
      </div>
    </div>
  @else
    <div class="block sm:flex items-end justify-between mb-6 text-center sm:text-left">
      <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100 sm:text-3xl sm:truncate">
          {{ __('Shopping Cart') }}
        </h2>
        <div class="text-sm text-mute mt-2">
          {{ __('You have :items items with total quantity of :quantity in your cart.', ['items' => $cart_items->count(), 'quantity' => $cart_items->sum('quantity')]) }}
        </div>
      </div>
      <div class="flex items-center justify-center gap-4 mt-4 sm:mt-0">
        <a href="{{ route('shop.products') }}" wire:navigate w.hover class="link x-focus text-sm">
          <span aria-hidden="true"> &larr;</span> {{ __('Continue Shopping') }}
        </a>
        <div x-data="{ open: false }">
          <x-shop::jet.danger-button type="button" @click="open = true" class="x-focus">
            {{ __('Empty Cart') }}
          </x-shop::jet.danger-button>
          <div x-show="open" style="display: none;">
            <x-shop::alpine.modal :backdrop="false">
              <h2 class="font-medium text-prominent">{{ __('Empty Cart') }}</h2>
              <p class="mt-2 text-mute max-w-xs">{{ __('Are you sure you want to empty your cart?') }}</p>

              <div class="mt-6 flex justify-end gap-2">
                <button type="button"@click="open = false" class="x-focus btn-primary">
                  {{ __('No, Cancel') }}
                </button>
                <button type="button" @click="() => { $wire.removeAll(); open = false; }" class="x-focus btn-danger">
                  {{ __('Yes, Delete') }}
                </button>
              </div>
            </x-shop::alpine.modal>
          </div>
        </div>
      </div>
    </div>

    <div wire:key="cart-view" class="flex flex-col lg:flex-row items-stretch gap-y-6 gap-x-8">
      <div class="w-full lg:w-3/4 px-4 sm:px-6 lg:px-8 pt-4 sm:pt-6 lg:pt-8 lg:pb-2 border bg-white dark:bg-gray-900 rounded-lg">
        <div class="hidden md:grid grid-flow-row grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-4 mb-4">
          <h3 class="font-semibold text-gray-500 text-xs uppercase col-span-2">
            {{ __('Product') }}
          </h3>
          <h3 class="font-semibold text-center text-gray-500 text-xs uppercase">
            {{ __('Quantity') }}
          </h3>
          <h3 class="font-semibold text-end text-gray-500 text-xs uppercase">
            {{ __('Price') }}
          </h3>
          <h3 class="hidden md:block font-semibold text-end text-gray-500 text-xs uppercase">
            {{ __('Subtotal') }}
          </h3>
        </div>
        {{-- <div>{{ json_encode($data['items']) }}</div> --}}
        @foreach ($data['items'] as $item)
          @php
            $cart_item = $cart_items->where('product_id', $item['product_id'])->first();
          @endphp
          @if ($cart_item->selected && ($cart_item->selected['variations'] ?? null))
            @foreach ($item['variations'] as $v)
              {{-- @foreach ($cart_item->selected['variations'] as $v) --}}
              {{-- For Variants --}}
              @php
                $variation = $cart_item->product->variations->where('id', $v['id'])->first();
              @endphp
              <div wire:key="cartItem-variation-{{ $loop->index }}"
                class="grid grid-flow-row grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-4 items-center py-4 lg:py-6 {{ $cart_item->oId ? '' : 'border-t dark:border-gray-700' }}">
                <div class="flex items-center col-span-2">
                  @if ($cart_item->product->photo)
                    <a href="{{ route('shop.product', $cart_item->product->slug) }}" wire:navigate w.hover
                      class="shrink-0 mt-1 w-16 h-16 flex items-center justify-center rounded focus-default">
                      <img class="max-w-full max-h-full rounded object-cover hover:shadow-md" src="{{ $cart_item->product->photo }}"
                        alt="{{ __($cart_item->product->name) }}">
                    </a>
                  @endif
                  <div class="flex flex-col items-start mx-4 grow">
                    <a href="{{ route('shop.product', $cart_item->product->slug) }}" wire:navigate w.hover
                      class="font-bold md:text-lg link x-focus">
                      {{ __($cart_item->product->name) }}
                    </a>

                    @php
                      $meta = [];
                      foreach ($variation->meta as $variant => $option) {
                          $meta[] = __($variant . ': ' . $option);
                      }
                    @endphp
                    {{ implode(', ', $meta) }}
                    @if ($cart_item->oId)
                      <span class="mt-2 text-xs text-green-600">{{ __('Free Item') }}</span>
                    @else
                      <button type="button" wire:click="removeItem('{{ $cart_item->product_id }}', '{{ $variation->id }}')"
                        class="mt-2 font-semibold hover:text-red-500 text-gray-500 rounded text-xs">
                        {{ __('Remove') }}
                      </button>
                    @endif
                  </div>
                </div>

                @if ($cart_item->oId)
                  <input readonly disabled type="number" value="{{ $v['quantity'] }}"
                    class="no-input-arrows text-opacity-50 bg-opacity-50 block w-full px-7 text-center text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100 sm:text-sm border-gray-300 rounded-md focus-default">
                @else
                  <div wire:key="cart-item-variations{{ $variation->id }}" class="relative rounded-md shadow-sm" x-data="{ qty: {{ $v['quantity'] }}, old_qty: {{ $v['quantity'] }}, product_id: '{{ $cart_item->product_id }}', variation_id: '{{ $variation->id }}', loading: false }"
                    x-init="$watch('qty', async value => {
                        if (value > 0) {
                            loading = true;
                            let res = await $wire.updateQty(product_id, value, variation_id);
                            if (res === false) {
                                $refs.input.value = parseFloat(old_qty);
                            } else {
                                old_qty = parseFloat(value);
                            }
                            loading = false;
                        }
                    })">
                    <button type="button" @click="qty = old_qty > 1 ? parseFloat(old_qty) - 1 : old_qty" :disabled="loading"
                      class="absolute inset-y-0 start-0 px-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg cursor-pointer focus-default">
                      <svg class="fill-current w-3" viewBox="0 0 448 512">
                        <path
                          d="M416 208H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h384c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z" />
                      </svg>
                    </button>
                    <input x-model.lazy="qty" type="number" min="1" x-ref="input"
                      class="no-input-arrows block w-full px-7 text-center text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100 sm:text-sm border-gray-300 rounded-md focus-default">
                    <button type="button" @click="qty = parseFloat(old_qty) + 1" :disabled="loading"
                      class="absolute inset-y-0 end-0 px-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg cursor-pointer focus-default">
                      <svg class="fill-current w-3" viewBox="0 0 448 512">
                        <path
                          d="M416 208H272V64c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v144H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h144v144c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V304h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z" />
                      </svg>
                    </button>
                  </div>
                @endif
                @if ($cart_item->oId)
                  <span></span>
                  <span></span>
                @else
                  <span class="text-end text-sm">
                    {{ currency_value($v['net_price'] ?? $cart_item->product->price) }}
                    @if (($v['tax_amount'] ?? 0) > 0)
                      <div>
                        + {{ currency_value($v['tax_amount']) }}
                      </div>
                    @endif
                  </span>
                  <span class="hidden md:block text-end font-bold">
                    {{ currency_value($v['total'] ?? ($v['price'] ?? $cart_item->product->price) * ($v['quantity'] ?? $cart_item->quantity)) }}
                  </span>
                @endif
              </div>
            @endforeach
          @else
            {{-- No Variation & No Portions --}}
            <div wire:key="cart-item-{{ $cart_item->product_id }}"
              class="grid grid-flow-row grid-cols-2 sm:grid-cols-4 md:grid-cols-5 gap-4 items-center py-4 lg:py-6 {{ $cart_item->oId ? '' : 'border-t dark:border-gray-700' }}">
              <div class="flex items-center col-span-2">
                @if ($cart_item->product->photo)
                  <a href="{{ route('shop.product', $cart_item->product->slug) }}" wire:navigate w.hover
                    class="shrink-0 w-16 h-16 flex items-center justify-center rounded focus-default">
                    <img class="max-w-full max-h-full rounded object-cover hover:shadow-md" src="{{ $cart_item->product->photo }}"
                      alt="{{ __($cart_item->product->name) }}">
                  </a>
                @endif
                <div class="flex flex-col mx-4 grow">
                  <a href="{{ route('shop.product', $cart_item->product->slug) }}" wire:navigate w.hover
                    class="font-bold md:text-lg link x-focus">
                    {{ __($cart_item->product->name) }}
                  </a>
                  <span class="mt-2">
                    @if ($cart_item->oId)
                      <span class="text-xs text-green-600">{{ __('Free Item') }}</span>
                    @else
                      <button type="button" wire:click="removeItem('{{ $cart_item->product_id }}')"
                        class="font-semibold hover:text-red-500 text-gray-500 rounded text-xs">
                        {{ __('Remove') }}
                      </button>
                    @endif
                  </span>
                </div>
              </div>
              @if ($cart_item->oId)
                <input readonly disabled type="number" value="{{ $cart_item->quantity }}"
                  class="no-input-arrows text-opacity-50 bg-opacity-50 block w-full px-7 text-center text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100 sm:text-sm border-gray-300 rounded-md focus-default">
              @else
                {{-- this.$wire.updateQty(this.product_id, this.qty); --}}
                <div wire:key="'item-{{ $cart_item->id }}" class="relative rounded-md shadow-sm" x-data="{ qty: {{ $cart_item->quantity }}, old_qty: {{ $cart_item->quantity }}, product_id: '{{ $cart_item->product_id }}', loading: false }"
                  x-init="$watch('qty', async value => {
                      if (value > 0) {
                          loading = true;
                          let res = await $wire.updateQty(product_id, value);
                          if (res === false) {
                              $refs.input.value = parseFloat(old_qty);
                          } else {
                              old_qty = parseFloat(value);
                          }
                          loading = false;
                      }
                  })">
                  <button type="button" @click="qty = old_qty > 1 ? parseFloat(old_qty) - 1 : old_qty" :disabled="loading"
                    class="absolute inset-y-0 start-0 px-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg cursor-pointer focus-default">
                    <svg class="fill-current w-3" viewBox="0 0 448 512">
                      <path
                        d="M416 208H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h384c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z" />
                    </svg>
                  </button>
                  <input x-model.lazy="qty" type="number" min="1" x-ref="input"
                    class="no-input-arrows block w-full px-7 text-center text-gray-800 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-100 sm:text-sm border-gray-300 rounded-md focus-default">
                  <button type="button" @click="qty = parseFloat(old_qty) + 1" :disabled="loading"
                    class="absolute inset-y-0 end-0 px-3 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 rounded-lg cursor-pointer focus-default">
                    <svg class="fill-current w-3" viewBox="0 0 448 512">
                      <path
                        d="M416 208H272V64c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v144H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h144v144c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V304h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z" />
                    </svg>
                  </button>
                </div>
              @endif
              @if ($cart_item->oId)
                <span></span>
                <span></span>
              @else
                <div class="text-end text-sm">
                  {{ currency_value($item['net_price'] ?? $cart_item->product->price) }}
                  @if (($item['tax_amount'] ?? 0) > 0)
                    <div>
                      + {{ currency_value($item['tax_amount']) }}
                    </div>
                  @endif
                </div>
                <div class="text-end font-bold col-span-2 sm:col-span-4 md:col-span-1">
                  {{ currency_value($item['total'] ?? $cart_item->product->price * $cart_item->quantity) }}
                </div>
              @endif
            </div>
          @endif
        @endforeach
      </div>

      <div class="w-full lg:w-1/4">
        <div class="sticky top-4">
          <div>
            <x-shop::jet.label for="shop_shipping_method_id" value="{{ __('Shipping Method') }}" />
            <x-shop::jet.select id="shop_shipping_method_id" class="input" type="text" name="shop_shipping_method_id"
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
          </div>

          <div class="py-4">
            <label for="promo" class="font-semibold inline-block mb-1 text-sm">{{ __('Coupon Code') }}</label>
            @if ($this->form['coupon_code'])
              <div class="flex items-stretch w-full">
                <div
                  class="block w-full py-2 px-3 border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-s-md sm:text-sm select-none">
                  {{ $this->form['coupon_code'] }}</div>
                <button type="button" wire:click="applyCoupon(true)"
                  class="flex items-center justify-center px-4 py-2 border border-gray-700 text-sm font-medium rounded-e-md text-white bg-gray-700 hover:bg-red-700 hover:border-red-700  hover:-m-px focus-default">
                  {{ __('Remove') }}
                </button>
              </div>
            @else
              <div class="flex items-stretch w-full">
                <input type="text" wire:model="form.coupon_code" class="block input rounded-e-none!" />
                <button type="button" wire:click="applyCoupon()"
                  class="flex focus:z-10 items-center justify-center px-4 py-2 border border-gray-700 text-sm font-medium rounded-e-md text-white bg-gray-700 hover:bg-gray-900 focus-default">
                  {{ __('Apply') }}
                </button>
              </div>
            @endif
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
                  <span><del class="decoration-red-500 decoration-1 decoration-wavy">{{ currency_value($shipping_method->rate) }}</del>
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
            <a href="{{ route('shop.checkout') }}" wire:navigate w.hover class="btn-primary btn-lg w-full justify-center">
              {{ __('Checkout') }}
            </a>
          </div>
        </div>
      </div>
    </div>

  @endif
</div>
