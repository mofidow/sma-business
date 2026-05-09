@php
  $isListingOutOfStock = $size !== 'lg' && (
    ($product->type === 'Standard' && ! $product->has_variants && ($product->storeStock?->balance ?? 0) <= 0) ||
    ($product->has_variants && $product->variations->every(fn ($v) => ($v->storeStock?->balance ?? 0) <= 0))
  );
@endphp
<div class="w-full relative z-20" x-data="{ show: false, variationIsOutOfStock: false }" @click.outside="show = false" @cartUpdated.window="show = false" @variant-stock-changed.window="variationIsOutOfStock = $event.detail.isOutOfStock">
  @if ($product->has_variants)
    <div @if ($popup_variant) x-show="show" x-transition.origin.bottom @endif @class([
        'flex flex-col gap-6 w-full',
        'mb-6' => !$popup_variant,
        'absolute border dark:border-gray-700 bottom-full inset-x-0 bg-white dark:bg-gray-950 p-4 mb-2 rounded-lg' => $popup_variant,
    ])>
      @if ($popup_variant)
        <h3 class="font-bold text-sm -mb-2 text-prominent">{{ __('Please select variant & click add') }}</h3>
      @endif
      @forelse ($product->variants as $variant)
        @if (in_array(strtolower($variant['name']), ['color', 'colour']))
          <fieldset>
            <legend class="block text-sm/6 font-semibold text-prominent">
              {{ $variant['name'] }}
            </legend>
            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-2">
              @forelse ($variant['options'] as $option)
                <label @class([
                    'group relative flex items-center justify-center rounded-md border border-gray-200 dark:border-gray-700 has-checked:outline-2 has-checked:outline-offset-2 has-checked:outline-black has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-primary-600 has-disabled:bg-gray-200 has-disabled:opacity-25 dark:has-checked:outline-white dark:has-focus-visible:outline-primary-500 hover:cursor-pointer',
                    'size-8' => $popup_variant,
                    'size-10' => !$popup_variant,
                ]) style="background-color: {{ $option }};">
                  <input type="radio" name="{{ $variant['name'] }}" value="{{ $option }}"
                    wire:model="selected.variants.{{ $variant['name'] }}"
                    class="absolute inset-0 appearance-none focus:outline-none disabled:cursor-not-allowed hidden" />
                </label>
              @empty
              @endforelse
            </div>
          </fieldset>
        @else
          <fieldset>
            <legend class="block text-sm/6 font-semibold text-prominent">
              {{ $variant['name'] }}
            </legend>
            <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-2">
              @forelse ($variant['options'] as $option)
                <label @class([
                    'group relative flex items-center justify-center rounded-md border border-gray-200 dark:border-gray-700 has-checked:outline-2 has-checked:outline-offset-2 has-checked:outline-black has-focus-visible:outline-2 has-focus-visible:outline-offset-2 has-focus-visible:outline-primary-600 has-disabled:bg-gray-200 has-disabled:opacity-25 dark:has-checked:outline-white dark:has-focus-visible:outline-primary-500 hover:cursor-pointer',
                    'p-1 px-2' => $popup_variant,
                    'py-2 px-4' => !$popup_variant,
                ])>
                  <input type="radio" name="{{ $variant['name'] }}" value="{{ $option }}"
                    wire:model="selected.variants.{{ $variant['name'] }}"
                    class="absolute inset-0 appearance-none focus:outline-none disabled:cursor-not-allowed hidden" />
                  <span class="text-sm font-medium uppercase">{{ $option }}</span>
                </label>
              @empty
              @endforelse
            </div>
          </fieldset>
        @endif
      @empty
      @endforelse
    </div>
  @endif

  <div @class([
      'flex items-center justify-between gap-4',
      'justify-start!' => $size == 'lg',
      'xl:justify-center' =>
          ($shop_settings ?? null) &&
          ($shop_settings['general']['cart_button'] ?? null) == 'hover',
  ])>
    @if ($isListingOutOfStock)
      <a href="{{ route('shop.product', $product->slug) }}" wire:navigate
        class="group/btn btn-primary btn-{{ $size }} inline-flex items-center">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
          class="shrink-0 group-hover/btn:stroke-2 size-5 me-2 lg:hidden xl:block">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
        </svg>
        {{ __('Notify Me') }}
      </a>
    @else
      @if ($popup_variant && $product->has_variants)
        <button x-show="!show" type="button" @click="show = true; variationIsOutOfStock = false" class="group/btn btn-primary btn-{{ $size }}">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            @class([
                'shrink-0 group-hover/btn:stroke-2',
                'size-5 me-2 lg:hidden xl:block' => $size == 'normal',
                'size-6 me-3' => $size == 'lg',
            ])>
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
          </svg>
          {{ __('Add to Cart') }}
        </button>
        <a x-show="variationIsOutOfStock" x-cloak href="{{ route('shop.product', $product->slug) }}" wire:navigate
          class="group/btn btn-primary btn-{{ $size }} inline-flex items-center">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            class="shrink-0 group-hover/btn:stroke-2 size-5 me-2 lg:hidden xl:block">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
          </svg>
          {{ __('Notify Me') }}
        </a>
      @endif
      <button @if ($popup_variant && $product->has_variants) x-show="show && !variationIsOutOfStock" @endif type="button" wire:click="submit" wire:loading.attr="disabled"
        wire:target="submit" class="group/btn btn-primary btn-{{ $size }}">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
          @class([
              'shrink-0 group-hover/btn:stroke-2',
              'size-5 me-2 lg:hidden xl:block' => $size == 'normal',
              'size-6 me-3' => $size == 'lg',
          ])>
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
        </svg>
        {{ __('Add to Cart') }}
        <svg wire:loading viewBox="0 0 38 38" stroke="currentColor" xmlns="http://www.w3.org/2000/svg"
          class="size-5 stroke-current inline ms-2">
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
      </button>
    @endif

    <livewire:components.cart.add-to-wishlist :productId="$product->id" :key="$product->id" />
  </div>
</div>
