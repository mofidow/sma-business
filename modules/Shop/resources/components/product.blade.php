@props(['product'])

<div class="product-card group w-full">
  {{-- Image area --}}
  <div class="product-card-image">
    <a href="{{ route('shop.product', $product->slug ?? '#') }}" wire:navigate w.hover class="block w-full h-full">
      <img src="{{ $product->photo ?? asset('img/products/dummy.jpg') }}"
           alt="{{ $product->name }}"
           class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
    </a>

    {{-- Promo badges --}}
    @if ($product->validPromotions->count())
      <div class="absolute top-3 end-3 flex flex-col items-end gap-1 z-10">
        @foreach ($product->validPromotions as $promotion)
          <span class="bg-violet-600 text-white text-xs font-semibold px-2.5 py-1 rounded-full whitespace-nowrap">
            @if ($promotion->type == 'simple')
              {{ format_number($promotion->discount, 0) }}{{ $promotion->discount_method == 'percentage' ? '%' : '' }} off
            @elseif ($promotion->type == 'advance' && $promotion->quantity_to_buy)
              Buy {{ format_number($promotion->quantity_to_buy, 0) }} get {{ format_number($promotion->discount, 0) }}{{ $promotion->discount_method == 'percentage' ? '%' : '' }} off
            @else
              {{ $promotion->name }}
            @endif
          </span>
        @endforeach
      </div>
    @endif

    {{-- Wishlist button (visible on hover) --}}
    <div class="absolute top-3 start-3 z-10 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
      <a href="{{ route('shop.wishlist') }}" wire:navigate w.hover
         class="flex items-center justify-center w-8 h-8 rounded-full bg-white shadow-md hover:bg-gray-50 text-gray-400 hover:text-violet-600 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
        </svg>
      </a>
    </div>
  </div>

  {{-- Product info --}}
  <div class="product-card-body">
    {{-- Category breadcrumb --}}
    <div class="text-xs text-gray-400 truncate flex items-center gap-1">
      @if ($product->brand?->slug)
        <a href="{{ route('shop.brand', $product->brand->slug) }}" class="hover:text-violet-600 transition-colors">
          {{ $product->brand->name }}
        </a>
        <span class="text-gray-300">/</span>
      @endif
      @if ($product->category?->slug)
        <a href="{{ route('shop.category', $product->category->slug) }}" class="hover:text-violet-600 transition-colors">
          {{ $product->category->name }}
        </a>
      @endif
    </div>

    {{-- Product name --}}
    <h3 class="font-semibold text-gray-900 text-sm leading-snug line-clamp-2 flex-1">
      <a href="{{ route('shop.product', $product->slug ?? '#') }}" wire:navigate w.hover class="hover:text-violet-600 transition-colors">
        {{ $product->name }}
      </a>
    </h3>

    {{-- Price --}}
    <div class="flex items-center justify-between mt-1">
      <span class="text-base font-bold text-gray-900">{{ currency_value($product->price) }}</span>
      @if ($product->validPromotions->count())
        <span class="text-xs text-violet-600 font-medium bg-violet-50 px-2 py-0.5 rounded-full">On sale</span>
      @endif
    </div>

    {{-- Add to cart --}}
    <div class="mt-2">
      @if (($shop_settings ?? null) && ($shop_settings['general']['cart_button'] ?? null) == 'hover')
        <div key="add_to_cart" class="xl:hidden xl:group-hover:block animate__faster animate__animated animate__fadeIn">
          <livewire:components.cart.add :product="$product" :popup_variant="true" :key="'to_cart_' . $product->id" />
        </div>
      @else
        <livewire:components.cart.add :product="$product" :popup_variant="true" :key="'add_to_cart_' . $product->id" />
      @endif
    </div>
  </div>
</div>
