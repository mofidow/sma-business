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
  <style>
    /* Dot pattern overlay */
    .hero-dots {
      background-image: radial-gradient(circle, rgba(255,255,255,0.06) 1px, transparent 1px);
      background-size: 28px 28px;
    }
    /* Product section gradient top border */
    .section-accent::before {
      content: '';
      display: block;
      height: 3px;
      background: linear-gradient(90deg, #10b981, #14b8a6, #10b981);
      border-radius: 999px;
      width: 48px;
      margin-bottom: 12px;
    }
    /* Highlight number stats */
    .stat-number {
      font-size: 2rem;
      font-weight: 800;
      background: linear-gradient(135deg, #10b981, #14b8a6);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
  </style>
</x-slot:styles>

<div>

  {{-- ═══════════════════════════════════════════════════════════════
       HERO — Slate-900 + Emerald gradient mesh
  ════════════════════════════════════════════════════════════════ --}}
  @if ($shop_index_settings['shop_slider'] ?? null)
    <div class="shop-hero relative overflow-hidden" x-data="{
        timer: null, activeSlide: 0,
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
      <div class="shop-hero-blob-1"></div>
      <div class="shop-hero-blob-2"></div>
      <div class="hero-dots absolute inset-0 z-0"></div>

      <template x-for="(slide, si) in slides" :key="si">
        <div x-show="activeSlide === si" class="relative z-10 min-h-[520px] md:min-h-[620px] flex items-center">
          <div x-show="slide.bg_image" class="absolute inset-0 bg-cover bg-center opacity-20"
            :style="{ backgroundImage: `url(${slide.bg_image})` }"></div>

          <div class="relative mx-auto max-w-7xl px-6 lg:px-8 py-20 flex flex-col lg:flex-row items-center gap-12 w-full">
            <div class="flex-1 text-center lg:text-start">
              <div class="inline-flex items-center gap-2 bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 text-xs font-semibold px-3 py-1.5 rounded-full mb-6 animate__animated animate__fadeIn">
                <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></span>
                {{ __('Now Live') }} &mdash; {{ config('app.name') }}
              </div>
              <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-white leading-tight mb-6 animate__animated animate__fadeInUp"
                x-html="slide.heading"></h1>
              <p class="text-lg text-slate-300 max-w-xl mb-8 animate__animated animate__fadeInUp"
                x-html="slide.description"></p>
              <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start animate__animated animate__fadeInUp"
                x-html="`<a href='${slide.button_link}' class='inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white px-8 py-3.5 text-base font-bold transition-all shadow-lg shadow-emerald-500/25'>${slide.button_text} <svg xmlns=&quot;http://www.w3.org/2000/svg&quot; fill=&quot;none&quot; viewBox=&quot;0 0 24 24&quot; stroke-width=&quot;2.5&quot; stroke=&quot;currentColor&quot; width=&quot;16&quot; height=&quot;16&quot;><path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; d=&quot;M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3&quot;/></svg></a>`">
              </div>
            </div>

            <div class="shrink-0 w-full max-w-sm">
              <div class="relative">
                <div class="absolute inset-0 bg-emerald-400/20 rounded-3xl blur-2xl scale-110"></div>
                <div class="relative bg-white/10 backdrop-blur-sm rounded-3xl p-2 border border-white/20"
                  x-html="`<img src='${slide.image}' alt='' class='w-full rounded-2xl object-cover animate__animated animate__fadeInRight'>`">
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>

      {{-- Slide dots --}}
      <div class="absolute bottom-6 inset-x-0 flex justify-center gap-2 z-20">
        <template x-for="(slide, si) in slides" :key="'dot-' + si">
          <button @click="changeSlide(si)" class="h-1.5 rounded-full transition-all duration-300 x-focus"
            :class="activeSlide === si ? 'bg-emerald-400 w-8' : 'bg-white/30 w-4 hover:bg-white/50'"></button>
        </template>
      </div>
      <button @click="changeSlide(activeSlide == 0 ? slides.length-1 : activeSlide - 1)"
        class="absolute start-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 flex items-center justify-center rounded-full bg-white/10 backdrop-blur border border-white/20 hover:bg-white/20 text-white transition-all x-focus">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-4 rtl:rotate-180"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/></svg>
      </button>
      <button @click="changeSlide(activeSlide == slides.length-1 ? 0 : activeSlide + 1)"
        class="absolute end-4 top-1/2 -translate-y-1/2 z-20 w-10 h-10 flex items-center justify-center rounded-full bg-white/10 backdrop-blur border border-white/20 hover:bg-white/20 text-white transition-all x-focus">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-4 rtl:rotate-180"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
      </button>
    </div>

  @else
    {{-- Fallback hero --}}
    <div class="shop-hero relative overflow-hidden min-h-[580px] flex items-center">
      <div class="shop-hero-blob-1"></div>
      <div class="shop-hero-blob-2"></div>
      <div class="hero-dots absolute inset-0 z-0"></div>

      <div class="relative z-10 mx-auto max-w-7xl px-6 lg:px-8 py-24 flex flex-col lg:flex-row items-center gap-16 w-full">
        <div class="flex-1 text-center lg:text-start">
          <div class="inline-flex items-center gap-2 bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 text-xs font-semibold px-3 py-1.5 rounded-full mb-6">
            <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></span>
            {{ __('Welcome to') }} {{ config('app.name') }}
          </div>
          <h1 class="text-5xl md:text-6xl lg:text-7xl font-extrabold text-white leading-none mb-6">
            {{ __('Shop') }}
            <span style="background: linear-gradient(135deg,#10b981,#14b8a6); -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;">
              {{ __('Smarter') }}
            </span><br>{{ __('Live Better') }}
          </h1>
          <p class="text-xl text-slate-300 max-w-lg mb-10">
            {{ __('Thousands of products. Trusted brands. Delivered to your door.') }}
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
            <a href="{{ route('shop.products') }}" wire:navigate w.hover
              class="inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white px-8 py-4 text-base font-bold transition-all shadow-lg shadow-emerald-500/30">
              {{ __('Shop Now') }}
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </a>
            <a href="{{ route('shop.categories') }}" wire:navigate w.hover
              class="inline-flex items-center justify-center rounded-xl bg-white/10 backdrop-blur border border-white/20 text-white px-8 py-4 text-base font-semibold hover:bg-white/20 transition-all">
              {{ __('Browse Categories') }}
            </a>
          </div>
          {{-- Stats row --}}
          <div class="mt-12 flex flex-wrap gap-8 justify-center lg:justify-start">
            <div>
              <div class="stat-number">{{ $total_products }}+</div>
              <div class="text-slate-400 text-sm">{{ __('Products') }}</div>
            </div>
            <div class="w-px bg-white/10 hidden sm:block"></div>
            <div>
              <div class="stat-number">{{ $total_brands }}+</div>
              <div class="text-slate-400 text-sm">{{ __('Brands') }}</div>
            </div>
            <div class="w-px bg-white/10 hidden sm:block"></div>
            <div>
              <div class="stat-number">{{ $total_categories }}+</div>
              <div class="text-slate-400 text-sm">{{ __('Categories') }}</div>
            </div>
          </div>
        </div>

        {{-- Hero illustration --}}
        <div class="shrink-0 w-full max-w-xs lg:max-w-sm hidden md:block">
          <div class="relative">
            <div class="absolute inset-0 bg-emerald-400/20 rounded-3xl blur-3xl scale-110"></div>
            <div class="relative grid grid-cols-2 gap-3">
              @foreach ($featured_products->take(4) as $fp)
                <div class="bg-white/10 backdrop-blur-sm border border-white/20 rounded-2xl p-3 flex flex-col items-center gap-2">
                  <img src="{{ $fp->photo ?? asset('img/products/dummy.jpg') }}" alt="{{ $fp->name }}"
                    class="w-16 h-16 object-cover rounded-xl">
                  <span class="text-white text-xs font-semibold text-center line-clamp-1">{{ $fp->name }}</span>
                  <span class="text-emerald-400 text-xs font-bold">{{ currency_value($fp->price) }}</span>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif

  {{-- ═══════════════════════════════════════════════════════════════
       TRUST BADGES — Pure white, elevated cards
  ════════════════════════════════════════════════════════════════ --}}
  <div class="bg-white border-b border-slate-100 shadow-sm">
    <div class="mx-auto max-w-7xl px-6 lg:px-8 py-8">
      <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        @foreach ([
          ['icon' => 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12', 'label' => 'Fast Delivery', 'sub' => 'On all orders'],
          ['icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z', 'label' => 'Secure Payment', 'sub' => '100% protected'],
          ['icon' => 'M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99', 'label' => 'Easy Returns', 'sub' => '30-day policy'],
          ['icon' => 'M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155', 'label' => '24/7 Support', 'sub' => 'Always here to help'],
        ] as $badge)
          <div class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 hover:bg-emerald-50 transition-colors">
            <div class="shrink-0 w-11 h-11 rounded-xl bg-emerald-100 flex items-center justify-center">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5 text-emerald-700">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $badge['icon'] }}" />
              </svg>
            </div>
            <div>
              <p class="text-sm font-bold text-slate-900">{{ __($badge['label']) }}</p>
              <p class="text-xs text-slate-500">{{ __($badge['sub']) }}</p>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  {{-- ═══════════════════════════════════════════════════════════════
       FEATURED PRODUCTS — Pure white
  ════════════════════════════════════════════════════════════════ --}}
  @if ($featured_products->isNotEmpty())
    <section class="py-16 bg-white">
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div x-data="{
            init() {
                var splide = new Splide('#featured', {
                    grid: { rows:1, cols:4, gap:{ row:'0', col:'0' } },
                    breakpoints: { 1279:{grid:{rows:1,cols:3}}, 1023:{grid:{rows:1,cols:2}}, 639:{grid:false} },
                });
                splide.on('mounted move', function() {
                    var bar = splide.root.querySelector('.progress-bar');
                    var end = splide.Components.Controller.getEnd() + 1;
                    bar.style.width = String(100 * Math.min((splide.index + 1) / end, 1)) + '%';
                });
                splide.mount(window.splide.Extensions);
            }
        }">
          <div class="splide" id="featured">
            <ul class="splide__pagination hidden!"></ul>
            <div class="flex items-end justify-between mb-8 splide__arrows">
              <div class="section-accent">
                <h2 class="section-title">{{ __('Featured Products') }}</h2>
                <p class="section-subtitle">{{ __('Hand-picked just for you') }}</p>
              </div>
              <div class="flex items-center gap-4">
                <a href="{{ route('shop.products', ['filters' => ['featured' => true]]) }}" wire:navigate w.hover
                  class="hidden sm:inline-flex items-center gap-1 text-sm font-semibold text-emerald-600 hover:text-emerald-700 transition-colors">
                  {{ __('View all') }} &rarr;
                </a>
                <div class="splide__arrows isolate inline-flex rounded-xl overflow-hidden shadow-sm ring-1 ring-slate-200">
                  <button class="splide__arrow splide__arrow--prev static! rounded-none! bg-white! p-2.5! text-slate-500! enabled:hover:bg-emerald-50! enabled:hover:text-emerald-700!">
                    <svg class="size-4!" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd"/></svg>
                  </button>
                  <button class="splide__arrow splide__arrow--next static! rounded-none! -ms-px! bg-white! p-2.5! text-slate-500! enabled:hover:bg-emerald-50! enabled:hover:text-emerald-700!">
                    <svg class="size-4!" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
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
            <div class="mt-5 bg-slate-100 rounded-full overflow-hidden h-1">
              <div class="progress-bar h-full rounded-full transition-all duration-500 ease-in-out w-0"
                style="background: linear-gradient(90deg,#10b981,#14b8a6)"></div>
            </div>
          </div>
        </div>
      </div>
    </section>
  @endif

  {{-- ═══════════════════════════════════════════════════════════════
       LATEST PRODUCTS — Light slate background
  ════════════════════════════════════════════════════════════════ --}}
  @if ($latest_products->isNotEmpty())
    <section class="py-16 bg-slate-50">
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div x-data="{
            init() {
                var splide = new Splide('#latest', {
                    grid: { rows:1, cols:4, gap:{ row:'0', col:'0' } },
                    breakpoints: { 1279:{grid:{rows:1,cols:3}}, 1023:{grid:{rows:1,cols:2}}, 639:{grid:false} },
                });
                splide.on('mounted move', function() {
                    var bar = splide.root.querySelector('.progress-bar');
                    var end = splide.Components.Controller.getEnd() + 1;
                    bar.style.width = String(100 * Math.min((splide.index + 1) / end, 1)) + '%';
                });
                splide.mount(window.splide.Extensions);
            }
        }">
          <div class="splide" id="latest">
            <ul class="splide__pagination hidden!"></ul>
            <div class="flex items-end justify-between mb-8 splide__arrows">
              <div class="section-accent">
                <h2 class="section-title">{{ __('Latest Products') }}</h2>
                <p class="section-subtitle">{{ __('Fresh arrivals, just in') }}</p>
              </div>
              <div class="flex items-center gap-4">
                <a href="{{ route('shop.products') }}" wire:navigate w.hover
                  class="hidden sm:inline-flex items-center gap-1 text-sm font-semibold text-emerald-600 hover:text-emerald-700 transition-colors">
                  {{ __('View all') }} &rarr;
                </a>
                <div class="splide__arrows isolate inline-flex rounded-xl overflow-hidden shadow-sm ring-1 ring-slate-200">
                  <button class="splide__arrow splide__arrow--prev static! rounded-none! bg-white! p-2.5! text-slate-500! enabled:hover:bg-emerald-50! enabled:hover:text-emerald-700!">
                    <svg class="size-4!" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd"/></svg>
                  </button>
                  <button class="splide__arrow splide__arrow--next static! rounded-none! -ms-px! bg-white! p-2.5! text-slate-500! enabled:hover:bg-emerald-50! enabled:hover:text-emerald-700!">
                    <svg class="size-4!" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
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
            <div class="mt-5 bg-slate-200 rounded-full overflow-hidden h-1">
              <div class="progress-bar h-full rounded-full transition-all duration-500 ease-in-out w-0"
                style="background: linear-gradient(90deg,#10b981,#14b8a6)"></div>
            </div>
          </div>
        </div>
      </div>
    </section>
  @endif

  {{-- ═══════════════════════════════════════════════════════════════
       CATEGORIES — White with emerald icon circles
  ════════════════════════════════════════════════════════════════ --}}
  @if ($categories->isNotEmpty())
    <section class="py-20 bg-white">
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="text-center mb-12">
          <p class="text-emerald-600 text-sm font-bold uppercase tracking-widest mb-3">{{ __('Browse') }}</p>
          <h2 class="text-3xl font-extrabold text-slate-900 mb-3">{{ __('Shop by Category') }}</h2>
          <p class="text-slate-400 max-w-lg mx-auto">
            {{ __('Over :x products across :n categories', ['x' => $total_products, 'n' => $total_categories]) }}
          </p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
          @foreach ($categories as $category)
            <a href="{{ route('shop.category', $category->slug) }}" wire:navigate w.hover class="category-card text-center">
              @if ($category->photo)
                <img src="{{ $category->photo }}" alt="{{ $category->name }}"
                  class="w-14 h-14 object-cover rounded-xl transition-transform duration-300">
              @else
                <div class="w-14 h-14 rounded-xl bg-emerald-100 flex items-center justify-center transition-colors">
                  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-7 text-emerald-600">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/>
                  </svg>
                </div>
              @endif
              <span class="text-sm font-semibold text-slate-700">{{ $category->name }}</span>
            </a>
          @endforeach
        </div>
        <div class="mt-10 text-center">
          <a href="{{ route('shop.categories') }}" wire:navigate w.hover
            class="inline-flex items-center gap-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold px-6 py-3 text-sm transition-colors">
            {{ __('Browse all categories') }}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
          </a>
        </div>
      </div>
    </section>
  @endif

  {{-- ═══════════════════════════════════════════════════════════════
       BRANDS — Emerald-50 tinted background
  ════════════════════════════════════════════════════════════════ --}}
  @if ($brands->isNotEmpty())
    <section class="py-20" style="background:#f0fdf4;">
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="text-center mb-12">
          <p class="text-emerald-600 text-sm font-bold uppercase tracking-widest mb-3">{{ __('Trusted') }}</p>
          <h2 class="text-3xl font-extrabold text-slate-900 mb-3">{{ __('Top Brands') }}</h2>
          <p class="text-slate-400">
            {{ __(':x products from :n top brands', ['x' => $total_products, 'n' => $total_brands]) }}
          </p>
        </div>
        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-4">
          @foreach ($brands as $brand)
            <a href="{{ route('shop.brand', $brand->slug) }}" wire:navigate w.hover
              class="flex flex-col items-center gap-2.5 p-4 bg-white rounded-2xl shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-200 group"
              style="border: 1px solid #d1fae5;">
              @if ($brand->photo)
                <img src="{{ $brand->photo }}" alt="{{ $brand->name }}"
                  class="h-10 w-auto object-contain grayscale group-hover:grayscale-0 transition-all duration-300">
              @else
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                  <span class="text-base font-black text-emerald-600">{{ substr($brand->name, 0, 1) }}</span>
                </div>
              @endif
              <span class="text-xs font-semibold text-slate-500 group-hover:text-emerald-600 transition-colors text-center truncate w-full">{{ $brand->name }}</span>
            </a>
          @endforeach
        </div>
        <div class="mt-10 text-center">
          <a href="{{ route('shop.brands') }}" wire:navigate w.hover
            class="inline-flex items-center gap-2 rounded-xl bg-white hover:bg-emerald-50 text-emerald-700 font-semibold px-6 py-3 text-sm transition-colors shadow-sm"
            style="border: 1px solid #a7f3d0;">
            {{ __('View all brands') }}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
          </a>
        </div>
      </div>
    </section>
  @endif

  {{-- ═══════════════════════════════════════════════════════════════
       CTA — Emerald-to-Teal gradient
  ════════════════════════════════════════════════════════════════ --}}
  @if (!empty($shop_index_settings['shop_cta'] ?? null))
    <section class="py-16 bg-white">
      <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-3xl px-8 py-20 sm:px-16 text-center"
          style="background: linear-gradient(135deg, #064e3b 0%, #065f46 40%, #0f766e 100%);">
          <div class="shop-hero-blob-1" style="top:-6rem;right:-6rem;width:20rem;height:20rem;"></div>
          <div class="shop-hero-blob-2" style="bottom:-6rem;left:-6rem;width:18rem;height:18rem;"></div>
          <div class="hero-dots absolute inset-0"></div>
          @if ($shop_index_settings['shop_cta']['bg_image'] ?? null)
            <img src="{{ $shop_index_settings['shop_cta']['bg_image'] }}" alt="" class="absolute inset-0 w-full h-full object-cover opacity-10">
          @endif
          <div class="relative z-10">
            <p class="text-emerald-400 text-sm font-bold uppercase tracking-widest mb-4">{{ __('Special Offer') }}</p>
            <h2 class="text-3xl sm:text-5xl font-extrabold text-white mb-5">{{ $shop_index_settings['shop_cta']['heading'] }}</h2>
            <p class="text-lg text-emerald-200 mb-10 max-w-2xl mx-auto">{{ $shop_index_settings['shop_cta']['description'] }}</p>
            <a href="{{ $shop_index_settings['shop_cta']['button_link'] }}"
              class="inline-flex items-center gap-2 rounded-2xl bg-white text-emerald-800 px-10 py-4 text-base font-bold hover:bg-emerald-50 transition-colors shadow-2xl shadow-black/30">
              {{ $shop_index_settings['shop_cta']['button_text'] }}
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="size-4"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
            </a>
          </div>
        </div>
      </div>
    </section>
  @endif

</div>
