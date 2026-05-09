<div class="flex flex-col h-full" style="max-height: calc(100dvh - 78px)">
  @if ($cart_items->isEmpty())
    <div class="text-center mt-12">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor"
        class="mx-auto size-12 text-mute">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
      </svg>
      <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">
        {{ __('Shopping Cart') }}
      </h3>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('Your cart is empty.') }}</p>
      <div class="mt-6">
        <a href="{{ route('shop.products') }}" wire:navigate w.hover type="button" class="btn-primary" @click="cartDrawer = false">
          {{ __('Browse Products') }}
        </a>
      </div>
    </div>
  @else
    <div class="flow-root grow overflow-y-auto scroll-box">
      <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700 px-6">
        @foreach ($cart_items as $cart_item)
          @if ($cart_item->selected && ($cart_item->selected['variations'] ?? null))
            @foreach ($cart_item->selected['variations'] as $v)
              @php
                $variation = $cart_item->product->variations->where('id', $v['id'])->first();
              @endphp
              <li class="flex py-6">
                <div class="size-20 shrink-0 overflow-hidden rounded-md border border-gray-200">
                  <img src="{{ $cart_item->product->photo }}" alt="{{ $cart_item->product->name }}" class="size-full object-cover" />
                </div>

                <div class="ms-4 flex flex-1 flex-col">
                  <div>
                    <div class="flex justify-between text-base font-medium">
                      <h3>
                        <a href="{{ route('shop.product', $cart_item->product->slug) }}" wire:navigate w.hover
                          class="link x-focus">{{ $cart_item->product->name }}</a>
                      </h3>
                      <p class="ms-4">{{ $cart_item->oId ? 'Free' : currency_value($cart_item->product->price) }}</p>
                    </div>
                    <p class="mt-1 text-sm text-mute">
                      @php
                        $meta = [];
                        foreach ($variation->meta as $variant => $option) {
                            $meta[] = __($variant . ': ' . $option);
                        }
                      @endphp
                      {{ implode(', ', $meta) }}
                    </p>
                  </div>
                  <div class="flex flex-1 items-end justify-between text-sm">
                    <p class="text-mute">{{ __('Qty') }} {{ $v['quantity'] }}</p>

                    @if (!$cart_item->oId)
                      <div class="flex">
                        <button type="button" wire:click="removeItem('{{ $cart_item->product_id }}', '{{ $variation->id }}')"
                          class="font-medium text-red-500 hover:text-red-600 dark:hover:text-red-400 x-focus">{{ __('Remove') }}</button>
                      </div>
                    @endif
                  </div>
                </div>
              </li>
            @endforeach
          @else
            <li class="flex py-6">
              <div class="size-20 shrink-0 overflow-hidden rounded-md border border-gray-200">
                <img src="{{ $cart_item->product->photo }}" alt="{{ $cart_item->product->name }}" class="size-full object-cover" />
              </div>

              <div class="ms-4 flex flex-1 flex-col">
                <div>
                  <div class="flex justify-between text-base font-medium">
                    <h3>
                      <a href="{{ route('shop.product', $cart_item->product->slug) }}" wire:navigate w.hover
                        class="link x-focus">{{ $cart_item->product->name }}</a>
                    </h3>
                    <p class="ms-4">{{ $cart_item->oId ? 'Free' : currency_value($cart_item->product->price) }}</p>
                  </div>
                  <p class="mt-1 text-sm text-mute">{{ $cart_item->product->category?->name }}</p>
                </div>
                <div class="flex flex-1 items-end justify-between text-sm">
                  <p class="text-mute">{{ __('Qty') }} {{ $cart_item->quantity }}</p>

                  @if (!$cart_item->oId)
                    <div class="flex">
                      <button type="button" wire:click="removeItem('{{ $cart_item->product_id }}')"
                        class="font-medium text-red-500 hover:text-red-600 dark:hover:text-red-400 x-focus">{{ __('Remove') }}</button>
                    </div>
                  @endif
                </div>
              </div>
            </li>
          @endif
        @endforeach
      </ul>
    </div>

    <div class="mt-auto shrink-0 border-t border-gray-200 dark:border-gray-700 px-4 py-6 sm:px-6">
      <div class="flex justify-between text-base font-medium">
        <p>{{ __('Subtotal') }}</p>
        <p>{{ currency_value($cart_items->sum(fn($item) => $item->oId ? 0 : $item->product->price * $item->quantity)) }}</p>
      </div>
      <p class="mt-0.5 text-sm text-mute">{{ __('Shipping and taxes calculated at checkout.') }}</p>
      <div class="mt-6">
        <a href="{{ route('shop.checkout') }}" wire:navigate w.hover
          class="flex items-center justify-center rounded-md border border-transparent bg-primary-600 px-6 py-3 text-base font-medium text-white shadow-xs hover:bg-primary-700">{{ __('Checkout') }}</a>
      </div>
      <div class="mt-4 flex justify-center text-center text-sm text-mute">
        <p>
          {{-- <button type="button" @click="cartDrawer = false" class="font-medium link x-focus">
            {{ __('Continue Shopping') }}
          </button>
          {{ __('or') }} --}}
          <a href="{{ route('shop.cart') }}" wire:navigate w.hover class="font-medium link x-focus">
            {{ __('View Shopping Cart') }}
            <span aria-hidden="true"> &rarr;</span>
          </a>
        </p>
      </div>
    </div>
  @endif
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      //   document.getElementById('cart-items-count').innerText = '{{ $cart_items->sum('quantity') ?: '' }}';
      window.addEventListener('cart-updated', event => {
        document.getElementById('cart-items-count').innerText = (event.detail && event.detail.length && event.detail[0] ? event
          .detail[0] : '');
      });
    });
  </script>
</div>
