<x-slot name="title">{{ config('app.name') }} — {{ __('Shop') }}</x-slot>
<x-slot name="metaDesc">{{ __('Browse all products') }}</x-slot>

<div>

  {{-- ═══════════════════════════════════════════════════════════════
       PAGE HEADER — compact, no hero image
  ════════════════════════════════════════════════════════════════ --}}
  <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-900 py-10 px-4 sm:px-6">
    <div class="mx-auto max-w-7xl">
      <h1 class="text-3xl sm:text-4xl font-extrabold text-white mb-2">{{ config('app.name') }}</h1>
      <p class="text-slate-400 text-sm mb-6">{{ __('All products — click any item to order instantly') }}</p>

      {{-- Search bar --}}
      <div class="relative max-w-lg">
        <svg xmlns="http://www.w3.org/2000/svg" class="absolute start-3 top-1/2 -translate-y-1/2 size-5 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
        </svg>
        <input
          wire:model.live.debounce.400ms="search"
          type="search"
          placeholder="{{ __('Search products…') }}"
          class="w-full bg-white/10 backdrop-blur border border-white/20 text-white placeholder-slate-400 rounded-xl ps-10 pe-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════════════
       PRODUCT GRID
  ════════════════════════════════════════════════════════════════ --}}
  <div class="bg-slate-50 min-h-screen">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 py-8">

      <div wire:loading.delay class="flex justify-center py-4">
        <svg class="animate-spin size-7 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
        </svg>
      </div>

      @if ($products->isEmpty())
        <div class="text-center py-24 text-slate-400">
          <svg xmlns="http://www.w3.org/2000/svg" class="size-16 mx-auto mb-4 opacity-30" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
          </svg>
          <p class="text-lg font-semibold">{{ __('No products found') }}</p>
          <button wire:click="$set('search','')" class="mt-3 text-emerald-600 text-sm underline">{{ __('Clear search') }}</button>
        </div>
      @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
          @foreach ($products as $product)
            <div class="group bg-white rounded-2xl shadow-sm hover:shadow-lg border border-slate-100 hover:border-emerald-200 overflow-hidden transition-all duration-200 flex flex-col">

              {{-- Product Image --}}
              <a href="{{ route('shop.quick-order', $product->slug) }}" class="block relative overflow-hidden aspect-square bg-slate-50">
                <img
                  src="{{ $product->photo ?? asset('img/products/dummy.jpg') }}"
                  alt="{{ $product->name }}"
                  class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out"
                  loading="lazy">

                {{-- Sale badge --}}
                @if ($product->validPromotions->count())
                  <span class="absolute top-2 end-2 bg-amber-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">
                    {{ __('Sale') }}
                  </span>
                @endif

                {{-- Quick order overlay --}}
                <div class="absolute inset-0 bg-emerald-600/0 group-hover:bg-emerald-600/10 transition-colors duration-300 flex items-center justify-center">
                  <span class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 bg-white text-emerald-700 font-bold text-xs px-3 py-1.5 rounded-full shadow-md">
                    {{ __('Order Now') }} →
                  </span>
                </div>
              </a>

              {{-- Product Info --}}
              <div class="flex flex-col flex-1 p-3">
                {{-- Category --}}
                @if ($product->category)
                  <span class="text-xs text-slate-400 truncate mb-1">{{ $product->category->name }}</span>
                @endif

                {{-- Name --}}
                <h3 class="text-sm font-semibold text-slate-800 line-clamp-2 flex-1 mb-2 leading-snug">
                  <a href="{{ route('shop.quick-order', $product->slug) }}" class="hover:text-emerald-600 transition-colors">
                    {{ $product->name }}
                  </a>
                </h3>

                {{-- Price + CTA --}}
                <div class="flex items-center justify-between gap-2 mt-auto">
                  <span class="text-base font-extrabold text-slate-900">{{ currency_value($product->price) }}</span>
                  <a href="{{ route('shop.quick-order', $product->slug) }}"
                     class="shrink-0 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold px-3 py-1.5 rounded-lg transition-colors">
                    {{ __('Buy') }}
                  </a>
                </div>
              </div>
            </div>
          @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-10 flex justify-center">
          {{ $products->links() }}
        </div>
      @endif

    </div>
  </div>

</div>
