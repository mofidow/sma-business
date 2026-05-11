<x-slot name="title">{{ $seo['title'] ?? config('app.name') }}</x-slot>
<x-slot name="metaDesc">{{ $seo['description'] ?? '' }}</x-slot>
<x-slot name="ogMetaData">
  <meta name="og:site_name" content="{{ $seo['title'] ?? config('app.name') }}" />
  <meta name="og:title" content="{{ $seo['title'] ?? config('app.name') }}" />
  <meta name="og:url" content="{{ route('shop.home') }}" />
  <meta name="og:image" content="{{ asset('/img/social/home.jpg') }}" />
  <meta name="twitter:card" content="summary" />
</x-slot>

<x-slot:scripts>
  <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.0.7/dist/js/splide.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide-extension-grid@0.4.1/dist/js/splide-extension-grid.min.js"></script>
</x-slot:scripts>
<x-slot:styles>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.0.7/dist/css/splide.min.css">
</x-slot:styles>

<div>

  {{-- ── Hero / Slider ─────────────────────────────────────────────── --}}
  @if ($shop_index_settings['shop_slider'] ?? null)
    <div class="relative bg-gray-900 overflow-hidden" x-data="{
        timer: null,
        activeSlide: 0,
        slides: {{ json_encode($shop_index_settings['shop_slider']) }},
        init() { this.restartTimer(); this.$watch('activeSlide', () => this.restartTimer()); },
        restartTimer() {
            if (this.timer) clearInterval(this.timer);
            this.timer = setInterval(() => {
                this.activeSlide = this.activeSlide == this.slides.length - 1 ? 0 : this.activeSlide + 1;
            }, 5000);
        },
        changeSlide(id) { this.activeSlide = id; }
    }">
      <template x-for="(slide, si) in slides" :key="si">
        <div x-show="activeSlide === si" class="relative min-h-[480px] md:min-h-[600px] flex items-center">
          <div x-show="slide.bg_image" class="absolute inset-0 bg-cover bg-center"
            :style="{ backgroundImage: `url(${slide.bg_image})` }"></div>
          <div class="absolute inset-0 bg-gradient-to-r from-gray-950/80 to-gray-900/40"></div>
          <div class="relative z-10 mx-auto max-w-7xl px-6 lg:px-8 py-20 flex flex-col lg:flex-row items-center gap-12">
            <div class="flex-1 text-center lg:text-start">
              <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white leading-tight animate__animated animate__fadeInUp"
                x-html="slide.heading"></h1>
              <p class="mt-5 text-lg text-gray-300 max-w-xl animate__animated animate__fadeInUp animate__delay-1s"
                x-html="slide.description"></p>
              <div class="mt-8 animate__animated animate__fadeInUp animate__delay-2s"
                x-html="`<a href='${slide.button_link}' class='inline-flex items-center gap-2 rounded-xl bg-white text-gray-900 px-8 py-3.5 text-base font-semibold hover:bg-gray-50 transition-colors shadow-lg'>${slide.button_text} <svg xmlns=&quot;http://www.w3.org/2000/svg&quot; fill=&quot;none&quot; viewBox=&quot;0 0 24 24&quot; stroke-width=&quot;2&quot; stroke=&quot;currentColor&quot; class=&quot;size-4&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; d=&quot;M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3&quot; /></svg></a>`">
              </div>
            </div>
            <div class="shrink-0 max-w-sm w-full"
              x-html="`<img src='${slide.image}' alt='' class='w-full rounded-2xl shadow-2xl animate__animated animate__fadeInRight'>`">
            </div>
          </div>
        </div>
      </template>

      {{-- Slide dots --}}
      <div class="absolute bottom-6 inset-x-0 flex justify-center gap-2 z-20">
        <template x-for="(slide, si) in slides" :key="'dot-' + si">
          <button @click="changeSlide(si)"
            class="h-1.5 rounded-full transition-all duration-300 x-focus"
            :class="activeSlide === si ? 'bg-white w-8' : 'bg-white/40 w-4 hover:bg-white/60'"></button>
        </template>
      </div>

      {{-- Prev/Next --}}
      <button @click="changeSlide(activeSlide == 0 ? slides.length-1 : activeSlide - 1)"
        class="absolute start-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 flex items-center justify-center rounded-full bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white transition-colors x-focus">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5 rtl:rotate-180">
          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
        </svg>
      </button>
      <button @click="changeSlide(activeSlide == slides.length-1 ? 0 : activeSlide + 1)"
        class="absolute end-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 flex items-center justify-center rounded-full bg-white/20 backdrop-blur-sm hover:bg-white/30 text-white transition-colors x-focus">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-5 rtl:rotate-180">
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
        </svg>
      </button>
    </div>
  @else
    {{-- Fallback hero when no slider is configured --}}
    <div class="relative bg-gradient-to-br from-violet-600 via-violet-700 to-indigo-800 overflow-hidden">
      <div class="absolute inset-0 opacity-10">
        <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
          <defs>
            <pattern id="grid" width="8" height="8" patternUnits="userSpaceOnUse">
              <path d="M 8 0 L 0 0 0 8" fill="none" stroke="white" stroke-width="0.5"/>
            </pattern>
          </defs>
          <rect width="100" height="100" fill="url(#grid)"/>
        </svg>
      </div>
      <div class="relative mx-auto max-w-7xl px-6 lg:px-8 py-24 text-center">
        <p class="text-violet-200 text-sm font-semibold uppercase tracking-widest mb-4">{{ __('Welcome to') }}</p>
        <h1 class="text-4xl md:text-6xl font-bold text-white mb-6">{{ config('app.name') }}</h1>
        <p class="text-xl text-violet-200 max-w-2xl mx-auto mb-10">
          {{ __('Discover thousands of products at unbeatable prices. Quality you can trust, delivered to your door.') }}
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <a href="{{ route('shop.products') }}" wire:navigate w.hover
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-white text-violet-700 px-8 py-3.5 text-base font-semibold hover:bg-violet-50 transition-colors shadow-lg">
            {{ __('Shop Now') }}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
          </a>
          <a href="{{ route('shop.categories') }}" wire:navigate w.hover
            class="inline-flex items-center justify-center rounded-xl bg-white/10 backdrop-blur-sm text-white border border-white/20 px-8 py-3.5 text-base font-semibold hover:bg-white/20 transition-colors">
            {{ __('Browse Categories') }}
          </a>
        </div>
      </div>
    </div>
  @endif

  {{-- ── Trust badges ──────────────────────────────────────────────── --}}
  <div class="bg-white border-b border-gray-100">
    <div class="mx-auto max-w-7xl px-6 lg:px-8 py-6">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="flex items-center gap-3">
          <div class="shrink-0 w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-violet-600">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-900">{{ __('Fast Delivery') }}</p>
            <p class="text-xs text-gray-500">{{ __('On all orders') }}</p>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <div class="shrink-0 w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-violet-600">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-900">{{ __('Secure Payment') }}</p>
            <p class="text-xs text-gray-500">{{ __('100% protected') }}</p>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <div class="shrink-0 w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-violet-600">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-900">{{ __('Easy Returns') }}</p>
            <p class="text-xs text-gray-500">{{ __('30-day policy') }}</p>
          </div>
        </div>
        <div class="flex items-center gap-3">
          <div class="shrink-0 w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-violet-600">
              <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155" />
            </svg>
          </div>
          <div>
            <p class="text-sm font-semibold text-gray-900">{{ __('24/7 Support') }}</p>
            <p class="text-xs text-gray-500">{{ __('Always here to help') }}</p>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- ── Featured Products ─────────────────────────────────────────── --}}
  @if ($featured_products->isNotEmpty())
    <section class="py-16 bg-white">
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div x-data="{
            init() {
                var splide = new Splide('#featured', {
                    grid: { rows: 1, cols: 4, gap: { row: '0rem', col: '0rem' } },
                    breakpoints: {
                        1279: { grid: { rows: 1, cols: 3 } },
                        1023: { grid: { rows: 1, cols: 2 } },
                        639: { grid: false },
                    },
                });
                splide.on('mounted move', function() {
                    var bar = splide.root.querySelector('.progress-bar');
                    var end = splide.Components.Controller.getEnd() + 1;
                    bar.style.width = String(100 * Math.min((splide.index + 1) / end, 1)) + '%';
                });
                splide.mount(window.splide.Extensions);
            },
        }">
          <div class="splide relative" id="featured">
            <ul class="splide__pagination hidden!"></ul>
            <div class="flex items-end justify-between mb-8 splide__arrows">
              <div>
                <h2 class="section-title">{{ __('Featured Products') }}</h2>
                <p class="section-subtitle">{{ __('Hand-picked items just for you') }}</p>
              </div>
              <div class="flex items-center gap-3">
                <a href="{{ route('shop.products', ['filters' => ['featured' => true]]) }}" wire:navigate w.hover
                  class="hidden sm:inline-flex text-sm font-medium text-violet-600 hover:text-violet-700 transition-colors">
                  {{ __('View all') }} &rarr;
                </a>
                <div class="splide__arrows isolate inline-flex rounded-lg overflow-hidden shadow-sm ring-1 ring-gray-200">
                  <button class="splide__arrow splide__arrow--prev static! rounded-none! bg-white! p-2! text-gray-500! enabled:hover:bg-gray-50!">
                    <svg class="size-4!" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd"/>
                    </svg>
                  </button>
                  <button class="splide__arrow splide__arrow--next static! rounded-none! -ms-px! bg-white! p-2! text-gray-500! enabled:hover:bg-gray-50!">
                    <svg class="size-4!" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <div class="splide__track -mx-3 px-3!">
              <ul class="splide__list">
                @foreach ($featured_products as $product)
                  <li class="splide__slide px-3! pb-3!">
                    <x-shop::product :product="$product" :key="$product->id" />
                  </li>
                @endforeach
              </ul>
            </div>

            <div class="mt-4 bg-gray-100 rounded-full overflow-hidden h-0.5">
              <div class="progress-bar h-full bg-violet-500 transition-all duration-500 ease-in-out w-0 rounded-full"></div>
            </div>
          </div>
        </div>
      </div>
    </section>
  @endif

  {{-- ── Latest Products ───────────────────────────────────────────── --}}
  @if ($latest_products->isNotEmpty())
    <section class="py-16 bg-gray-50">
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div x-data="{
            init() {
                var splide = new Splide('#latest', {
                    grid: { rows: 1, cols: 4, gap: { row: '0rem', col: '0rem' } },
                    breakpoints: {
                        1279: { grid: { rows: 1, cols: 3 } },
                        1023: { grid: { rows: 1, cols: 2 } },
                        639: { grid: false },
                    },
                });
                splide.on('mounted move', function() {
                    var bar = splide.root.querySelector('.progress-bar');
                    var end = splide.Components.Controller.getEnd() + 1;
                    bar.style.width = String(100 * Math.min((splide.index + 1) / end, 1)) + '%';
                });
                splide.mount(window.splide.Extensions);
            },
        }">
          <div class="splide relative" id="latest">
            <ul class="splide__pagination hidden!"></ul>
            <div class="flex items-end justify-between mb-8 splide__arrows">
              <div>
                <h2 class="section-title">{{ __('Latest Products') }}</h2>
                <p class="section-subtitle">{{ __('Fresh arrivals, just in') }}</p>
              </div>
              <div class="flex items-center gap-3">
                <a href="{{ route('shop.products') }}" wire:navigate w.hover
                  class="hidden sm:inline-flex text-sm font-medium text-violet-600 hover:text-violet-700 transition-colors">
                  {{ __('View all') }} &rarr;
                </a>
                <div class="splide__arrows isolate inline-flex rounded-lg overflow-hidden shadow-sm ring-1 ring-gray-200">
                  <button class="splide__arrow splide__arrow--prev static! rounded-none! bg-white! p-2! text-gray-500! enabled:hover:bg-gray-50!">
                    <svg class="size-4!" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd"/>
                    </svg>
                  </button>
                  <button class="splide__arrow splide__arrow--next static! rounded-none! -ms-px! bg-white! p-2! text-gray-500! enabled:hover:bg-gray-50!">
                    <svg class="size-4!" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/>
                    </svg>
                  </button>
                </div>
              </div>
            </div>

            <div class="splide__track -mx-3 px-3!">
              <ul class="splide__list">
                @foreach ($latest_products as $product)
                  <li class="splide__slide px-3! pb-3!">
                    <x-shop::product :product="$product" :key="$product->id" />
                  </li>
                @endforeach
              </ul>
            </div>

            <div class="mt-4 bg-gray-200 rounded-full overflow-hidden h-0.5">
              <div class="progress-bar h-full bg-violet-500 transition-all duration-500 ease-in-out w-0 rounded-full"></div>
            </div>
          </div>
        </div>
      </div>
    </section>
  @endif

  {{-- ── Categories ─────────────────────────────────────────────────── --}}
  @if ($categories->isNotEmpty())
    <section class="py-16 bg-white">
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="text-center mb-10">
          <h2 class="section-title">{{ __('Shop by Category') }}</h2>
          <p class="section-subtitle mt-2">
            {{ __('Over {x} products across {n} categories', ['x' => $total_products, 'n' => $total_categories]) }}
          </p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
          @foreach ($categories as $category)
            <a href="{{ route('shop.category', $category->slug) }}" wire:navigate w.hover class="category-card text-center group">
              @if ($category->photo)
                <img src="{{ $category->photo }}" alt="{{ $category->name }}"
                  class="w-14 h-14 object-cover rounded-xl group-hover:scale-110 transition-transform duration-300">
              @else
                <div class="w-14 h-14 rounded-xl bg-violet-50 flex items-center justify-center group-hover:bg-violet-100 transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-7 text-violet-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                  </svg>
                </div>
              @endif
              <span class="text-sm font-semibold text-gray-800 group-hover:text-violet-600 transition-colors">{{ $category->name }}</span>
            </a>
          @endforeach
        </div>
        <div class="mt-8 text-center">
          <a href="{{ route('shop.categories') }}" wire:navigate w.hover
            class="inline-flex items-center gap-2 text-sm font-semibold text-violet-600 hover:text-violet-700 transition-colors">
            {{ __('Browse all categories') }}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
          </a>
        </div>
      </div>
    </section>
  @endif

  {{-- ── Brands ──────────────────────────────────────────────────────── --}}
  @if ($brands->isNotEmpty())
    <section class="py-16 bg-gray-50 border-y border-gray-100">
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="text-center mb-10">
          <h2 class="section-title">{{ __('Top Brands') }}</h2>
          <p class="section-subtitle mt-2">
            {{ __('Over {x} products from {n} brands', ['x' => $total_products, 'n' => $total_brands]) }}
          </p>
        </div>
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-4">
          @foreach ($brands as $brand)
            <a href="{{ route('shop.brand', $brand->slug) }}" wire:navigate w.hover
              class="flex flex-col items-center gap-2 p-4 bg-white rounded-xl ring-1 ring-gray-100 hover:ring-violet-200 hover:shadow-sm transition-all duration-200 group">
              @if ($brand->photo)
                <img src="{{ $brand->photo }}" alt="{{ $brand->name }}" class="h-10 w-auto object-contain grayscale group-hover:grayscale-0 transition-all">
              @else
                <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center">
                  <span class="text-base font-bold text-gray-400">{{ substr($brand->name, 0, 1) }}</span>
                </div>
              @endif
              <span class="text-xs font-medium text-gray-600 group-hover:text-violet-600 transition-colors text-center truncate w-full">{{ $brand->name }}</span>
            </a>
          @endforeach
        </div>
        <div class="mt-8 text-center">
          <a href="{{ route('shop.brands') }}" wire:navigate w.hover
            class="inline-flex items-center gap-2 text-sm font-semibold text-violet-600 hover:text-violet-700 transition-colors">
            {{ __('View all brands') }}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4">
              <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
            </svg>
          </a>
        </div>
      </div>
    </section>
  @endif

  {{-- ── CTA ──────────────────────────────────────────────────────── --}}
  @if (!empty($shop_index_settings['shop_cta'] ?? null))
    <section class="py-16 bg-white">
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-600 to-indigo-700 px-8 py-16 sm:px-16 text-center">
          @if ($shop_index_settings['shop_cta']['bg_image'] ?? null)
            <div class="absolute inset-0">
              <img src="{{ $shop_index_settings['shop_cta']['bg_image'] }}" alt="" class="w-full h-full object-cover opacity-20">
            </div>
          @endif
          <div class="absolute inset-0 bg-gradient-to-br from-violet-600/80 to-indigo-700/80"></div>
          <div class="relative z-10">
            <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">{{ $shop_index_settings['shop_cta']['heading'] }}</h2>
            <p class="text-lg text-violet-100 mb-8 max-w-2xl mx-auto">{{ $shop_index_settings['shop_cta']['description'] }}</p>
            <a href="{{ $shop_index_settings['shop_cta']['button_link'] }}"
              class="inline-flex items-center gap-2 rounded-xl bg-white text-violet-700 px-8 py-3.5 text-base font-semibold hover:bg-violet-50 transition-colors shadow-lg">
              {{ $shop_index_settings['shop_cta']['button_text'] }}
            </a>
          </div>
        </div>
      </div>
    </section>
  @endif

</div>
