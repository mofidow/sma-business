@props(['product'])

<div class="product-card group w-full">
  {{-- Image area — links to quick-order --}}
  <div class="product-card-image">
    <a href="{{ route('shop.quick-order', $product->slug ?? '#') }}" class="block w-full h-full">
      <img src="{{ $product->photo ?? asset('img/products/dummy.jpg') }}"
           alt="{{ $product->name }}"
           class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
    </a>

    {{-- Promo badges --}}
    @if ($product->validPromotions->count())
      <div class="absolute top-3 end-3 flex flex-col items-end gap-1 z-10">
        @foreach ($product->validPromotions as $promotion)
          <span class="bg-amber-500 text-white text-xs font-bold px-2.5 py-1 rounded-full whitespace-nowrap shadow-sm">
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

    {{-- Quick-order overlay on hover --}}
    <div class="absolute inset-0 bg-emerald-600/0 group-hover:bg-emerald-600/10 flex items-center justify-center transition-colors duration-300">
      <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-white text-emerald-700 font-bold text-xs px-3 py-1.5 rounded-full shadow-md">
        {{ __('Order Now') }} →
      </span>
    </div>
  </div>

  {{-- Product info --}}
  <div class="product-card-body">
    {{-- Category breadcrumb --}}
    <div class="text-xs text-gray-400 truncate flex items-center gap-1">
      @if ($product->brand?->slug)
        <a href="{{ route('shop.brand', $product->brand->slug) }}" class="hover:text-emerald-600 transition-colors">
          {{ $product->brand->name }}
        </a>
        <span class="text-gray-300">/</span>
      @endif
      @if ($product->category?->slug)
        <a href="{{ route('shop.category', $product->category->slug) }}" class="hover:text-emerald-600 transition-colors">
          {{ $product->category->name }}
        </a>
      @endif
    </div>

    {{-- Product name → quick-order --}}
    <h3 class="font-semibold text-gray-900 text-sm leading-snug line-clamp-2 flex-1">
      <a href="{{ route('shop.quick-order', $product->slug ?? '#') }}" class="hover:text-emerald-600 transition-colors">
        {{ $product->name }}
      </a>
    </h3>

    {{-- Price --}}
    <div class="flex items-center justify-between mt-1">
      <span class="text-base font-bold text-gray-900">{{ currency_value($product->price) }}</span>
      @if ($product->validPromotions->count())
        <span class="text-xs text-amber-700 font-bold bg-amber-100 px-2 py-0.5 rounded-full">{{ __('On sale') }}</span>
      @endif
    </div>

    {{-- Buy Now button --}}
    <div class="mt-2">
      <a href="{{ route('shop.quick-order', $product->slug ?? '#') }}"
         class="block w-full text-center bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold py-2 rounded-lg transition-colors">
        {{ __('Buy Now') }}
      </a>
    </div>
  </div>
</div>
