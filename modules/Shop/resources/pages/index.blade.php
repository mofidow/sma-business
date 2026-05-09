<x-slot name="title">{{ $seo['title'] ?? config('app.name') }}</x-slot>
<x-slot name="metaDesc">{{ $seo['description'] ?? '' }}</x-slot>
<x-slot name="ogMetaData">
  <meta name="og:site_name" content="{{ $seo['title'] ?? config('app.name') }}" />
  <meta name="og:title" content="{{ $seo['title'] ?? config('app.name') }}" />
  <meta name="og:url" content="{{ route('shop.home') }}" />
  <meta name="og:image" content="{{ asset('/img/social/home.jpg') }}" />
  <meta name="twitter:card" content="summary" />
</x-slot>

<div>
  @if ($shop_index_settings['shop_slider'] ?? null)
    <div>
      <div class="bg-gray-100 flex flex-col justify-center items-center">
        <div class="relative w-full overflow-hidden" x-data="{
            timer: null,
            activeSlide: 0,
            slides: {{ json_encode($shop_index_settings['shop_slider']) }},
            init() {
                this.restartTimer();
                this.$watch('activeSlide', () => {
                    this.restartTimer();
                });
            },
            restartTimer() {
                if (this.timer) {
                    clearInterval(this.timer);
                }

                this.timer = setInterval(() => {
                    this.activeSlide = this.activeSlide == this.slides.length - 1 ? 0 : this.activeSlide + 1;
                }, 5000);
            },
            changeSlide(slideId) {
                this.activeSlide = slideId;
            }
        }">
          <!-- Slides -->
          <template x-for="(slide, si) in slides" :key="si">
            <div x-show="activeSlide === si" class="h-full flex items-center">
              <div class="w-full bg-gray-900">
                <div x-show="slide.bg_image" class="absolute inset-0 bg-cover bg-center overflow-hidden"
                  :style="{ backgroundImage: `url(${slide.bg_image})` }">
                  {{-- x-html="`<img src='${slide.bg_image}' alt='' class='object-cover object-center'>`" --}}
                </div>
                <div class="absolute inset-0 dark:bg-gray-950 opacity-50"></div>

                <div
                  class="relative flex flex-col lg:flex-row gap-y-6 gap-x-12 items-center mx-auto max-w-5xl px-6 pt-12 pb-16 sm:py-24 lg:px-0">
                  <div class="shrink-0 w-full h-full max-w-96 max-h-96"
                    x-html="`<img src='${slide.image}' alt='' class='max-w-full max-h-full rounded-md shrink-0 animate__animated animate__slideInLeft'>`">
                  </div>
                  <div class="text-center lg:text-left">
                    <h1 class="text-2xl font-bold tracking-tight text-white lg:text-4xl animate__animated animate__slideInDown">
                      <span x-html="slide.heading"></span>
                    </h1>
                    <p class="mt-4 text-xl text-white animate__animated animate__lightSpeedInRight" x-html="slide.description"></p>
                    <div class="animate__animated animate__slideInUp"
                      x-html="`<a href='${slide.button_link}' class='mt-8 inline-block rounded-md border border-transparent bg-white px-8 py-3 text-base font-medium text-gray-900 hover:bg-gray-100 hover:outline-none hover:ring-2 hover:ring-white hover:ring-offset-2 hover:ring-offset-black'>${slide.button_text}</a>`">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </template>

          <!-- Prev/Next Arrows -->
          <button
            class="absolute start-0 top-0 h-full flex group items-center justify-start ps-2 pe-6 ltr:hover:bg-linear-to-r rtl:hover:bg-linear-to-l hover:from-gray-500/50 hover:to-transparent x-focus"
            x-on:click="changeSlide(activeSlide == 0 ? slides.length-1 : activeSlide - 1)">
            {{ str(session('shop_rtl') ? '&#8594;' : '&#8592;')->toHtmlString() }}
          </button>
          <button
            class="absolute end-0 top-0 h-full flex items-center justify-end ps-6 pe-2 ltr:hover:bg-linear-to-l rtl:hover:bg-linear-to-r hover:from-gray-500/50 hover:to-transparent x-focus"
            x-on:click="changeSlide(activeSlide == slides.length-1 ? 0 : activeSlide + 1)">
            {{ str(session('shop_rtl') ? '&#8592;' : '&#8594;')->toHtmlString() }}
          </button>

          <!-- Buttons -->
          <div class="absolute bottom-2 inset-x-0  w-full flex items-center justify-center px-4">
            <div class="mx-auto max-w-7xl">
              <template x-for="(slide, si) in slides" :key="'slide-' + si">
                <button
                  class="flex-1 w-4 h-4 mx-2 mb-0 rounded-full overflow-hidden transition-colors duration-200 ease-out hover:bg-primary-600 hover:shadow-lg x-focus"
                  :class="{
                      'bg-primary-600 cursor-default': activeSlide === si,
                      'bg-gray-300 dark:bg-gray-700': activeSlide !== si
                  }"
                  x-on:click="activeSlide = si"></button>
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>
  @endif

  {{-- Featured products --}}
  <div class="px-4 py-12 sm:px-6 sm:py-24 lg:px-8 lg:py-32">
    <x-slot:scripts>
      <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.0.7/dist/js/splide.min.js"></script>
      <script src="https://cdn.jsdelivr.net/npm/@splidejs/splide-extension-grid@0.4.1/dist/js/splide-extension-grid.min.js"></script>
    </x-slot:scripts>

    <x-slot:styles>
      <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.0.7/dist/css/splide.min.css">
    </x-slot:styles>


    @if ($featured_products->isNotEmpty())
      <div x-data="{
          init() {
              var splide = new Splide('#featured', {
                  grid: {
                      rows: 1,
                      cols: 4,
                      gap: {
                          row: '0rem',
                          col: '0rem',
                      },
                  },
                  breakpoints: {
                      1279: {
                          grid: {
                              rows: 1,
                              cols: 3,
                          },
                      },
                      1023: {
                          grid: {
                              rows: 1,
                              cols: 2,
                          },
                      },
                      639: {
                          grid: false,
                      },
                  },
              });

              var bar = splide.root.querySelector('.progress-bar');
              splide.on('mounted move', function() {
                  var end = splide.Components.Controller.getEnd() + 1;
                  var rate = Math.min((splide.index + 1) / end, 1);
                  bar.style.width = String(100 * rate) + '%';
              });

              splide.mount(window.splide.Extensions);
          },
      }">
        <div class="splide relative mx-auto max-w-7xl" id="featured">
          <ul class="splide__pagination hidden!"></ul>

          <div class="flex justify-between splide__arrows">
            <h4 class="text-2xl font-bold">
              {{ __('Featured Products') }}
            </h4>

            <div class="splide__arrows isolate absolute end-0 top-4 inline-flex">
              <button
                class="splide__arrow splide__arrow--prev x-focus static! rounded-s-md! rounded-e-none! bg-white! dark:bg-gray-900! p-2! text-gray-400! ring-1! ring-inset! ring-gray-300! dark:ring-gray-500! enabled:hover:bg-gray-50! dark:enabled:hover:bg-gray-950! focus:z-10!">
                <svg class="size-5! fill-gray-600! dark:fill-gray-300!" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"
                  data-slot="icon">
                  <path fill-rule="evenodd"
                    d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z"
                    clip-rule="evenodd" />
                </svg>
              </button>
              <button
                class="splide__arrow splide__arrow--next x-focus static! rounded-e-md! rounded-s-none! bg-white! dark:bg-gray-900! p-2! text-gray-400! -ms-px! ring-1! ring-inset! ring-gray-300! dark:ring-gray-500! enabled:hover:bg-gray-50! dark:enabled:hover:bg-gray-950! focus:z-10!">
                <svg class="size-5! fill-gray-600! dark:fill-gray-300!" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"
                  data-slot="icon">
                  <path fill-rule="evenodd"
                    d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z"
                    clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>

          <div class="splide__track -mx-4 sm:-mx-6 lg:-mx-8 px-4! py-6!">
            <ul class="splide__list">
              @foreach ($featured_products as $product)
                <li class="splide__slide p-4!">
                  <div class="transform rounded-xl shadow-xl transition duration-300 hover:scale-105">
                    <div class="flex h-full justify-center items-center">
                      <x-shop::product :product="$product" :key="$product->id" />
                    </div>
                  </div>
                </li>
              @endforeach
            </ul>
          </div>

          <!-- Add the progress bar element -->
          <div class="bg-gray-100 dark:bg-gray-800">
            <div class="progress-bar h-0.5 bg-primary-500 transition-all duration-500 ease-in-out w-0"></div>
          </div>
        </div>
      </div>
    @endif
    @if ($latest_products->isNotEmpty())
      <div x-data="{
          init() {
              var splide = new Splide('#latest', {
                  grid: {
                      rows: 1,
                      cols: 4,
                      gap: {
                          row: '0rem',
                          col: '0rem',
                      },
                  },
                  breakpoints: {
                      1279: {
                          grid: {
                              rows: 1,
                              cols: 3,
                          },
                      },
                      1023: {
                          grid: {
                              rows: 1,
                              cols: 2,
                          },
                      },
                      639: {
                          grid: false,
                      },
                  },
              });

              var bar = splide.root.querySelector('.progress-bar');
              splide.on('mounted move', function() {
                  var end = splide.Components.Controller.getEnd() + 1;
                  var rate = Math.min((splide.index + 1) / end, 1);
                  bar.style.width = String(100 * rate) + '%';
              });

              splide.mount(window.splide.Extensions);
          },
      }" @class(['mt-16 lg:mt-24' => $featured_products->isNotEmpty()])>
        <div class="splide relative mx-auto max-w-7xl" id="latest">
          <ul class="splide__pagination hidden!"></ul>

          <div class="flex justify-between splide__arrows">
            <h4 class="text-2xl font-bold">
              {{ __('Latest Products') }}
            </h4>

            <div class="splide__arrows isolate absolute end-0 top-4 inline-flex">
              <button
                class="splide__arrow splide__arrow--prev static! rounded-s-md! rounded-e-none! bg-white! dark:bg-gray-900! p-2! text-gray-400! ring-1! ring-inset! ring-gray-300! dark:ring-gray-500! enabled:hover:bg-gray-50! dark:enabled:hover:bg-gray-950! focus:z-10!">
                <svg class="size-5! fill-gray-600! dark:fill-gray-300!" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"
                  data-slot="icon">
                  <path fill-rule="evenodd"
                    d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z"
                    clip-rule="evenodd" />
                </svg>
              </button>
              <button
                class="splide__arrow splide__arrow--next static! rounded-e-md! rounded-s-none! bg-white! dark:bg-gray-900! p-2! text-gray-400! -ms-px! ring-1! ring-inset! ring-gray-300! dark:ring-gray-500! enabled:hover:bg-gray-50! dark:enabled:hover:bg-gray-950! focus:z-10!">
                <svg class="size-5! fill-gray-600! dark:fill-gray-300!" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"
                  data-slot="icon">
                  <path fill-rule="evenodd"
                    d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z"
                    clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>

          <div class="splide__track -mx-4 sm:-mx-6 lg:-mx-8 px-4! py-6!">
            <ul class="splide__list">
              @foreach ($latest_products as $product)
                <li class="splide__slide p-4!">
                  <div class="transform rounded-xl shadow-xl transition duration-300 hover:scale-105">
                    <div class="flex h-full justify-center items-center">
                      <x-shop::product :product="$product" :key="$product->id" />
                    </div>
                  </div>
                </li>
              @endforeach
            </ul>
          </div>

          <!-- Add the progress bar element -->
          <div class="bg-gray-100 dark:bg-gray-800">
            <div class="progress-bar h-0.5 bg-primary-500 transition-all duration-500 ease-in-out w-0"></div>
          </div>
        </div>
      </div>
    @endif
  </div>

  {{-- Featured Categories --}}
  <div class="bg-gray-100 dark:bg-gray-800 py-24 sm:py-32">
    <div class="mx-auto max-w-2xl text-center mb-24">
      <h2 class="text-3xl font-semibold tracking-tight sm:text-4xl">{{ __('Top Brands & Categories') }}</h2>
      <p class="mt-4 text-pretty text-lg font-medium text-gray-400 sm:text-xl/8">
        {{ __('We have {p} products for {b} brands and {c} categories.', ['p' => $total_products, 'b' => $total_brands, 'c' => $total_categories]) }}
        {{ __('Please click any brand or category below to load the products.') }}
      </p>
    </div>
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
      <div
        class="mx-auto grid max-w-lg grid-cols-4 items-center gap-x-8 gap-y-12 sm:max-w-xl sm:grid-cols-6 sm:gap-x-10 sm:gap-y-14 lg:mx-0 lg:max-w-none lg:grid-cols-5">
        @foreach ($brands as $brand)
          <a href="{{ route('shop.brand', $brand->slug) }}" wire:navigate w.hover
            class="col-span-2 min-h-12 text-sm font-bold rounded-md w-full lg:col-span-1 flex flex-col items-center gap-2 link">
            @if ($brand->photo)
              <img class="max-h-16 rounded-lg" src="{{ $brand->photo }}" alt="{{ $brand->name }}">
            @endif
            {{ $brand->name }}
          </a>
        @endforeach
      </div>
      <div class="mt-16 flex justify-center">
        <p
          class="relative rounded-full bg-gray-50 dark:bg-gray-900 px-6 sm:px-4 py-1.5 text-sm/6 text-mute ring-1 ring-inset ring-gray-900/5">
          <span
            class="hidden sm:inline">{{ __('Over {x} products for {n} brands.', ['x' => $total_products, 'n' => $total_brands]) }}</span>
          <a href="{{ route('shop.brands') }}" wire:navigate w.hover class="link"><span class="absolute inset-0"
              aria-hidden="true"></span>
            {{ __('View all brands') }}
            <span aria-hidden="true">&rarr;</span></a>
        </p>
      </div>
    </div>

    <div class="mx-auto max-w-7xl px-6 lg:px-8 pt-24 sm:pt-32">
      <div
        class="mx-auto grid max-w-lg grid-cols-4 items-center gap-x-8 gap-y-12 sm:max-w-xl sm:grid-cols-6 sm:gap-x-10 sm:gap-y-14 lg:mx-0 lg:max-w-none lg:grid-cols-5">
        @foreach ($categories as $category)
          <a href="{{ route('shop.category', $category->slug) }}" wire:navigate w.hover
            class="col-span-2 min-h-12 text-sm font-bold rounded-md w-full lg:col-span-1 flex flex-col items-center gap-2 link">
            @if ($category->photo)
              <img class="max-h-16 rounded-lg" src="{{ $category->photo }}" alt="{{ $category->name }}">
            @endif
            {{ $category->name }}
          </a>
        @endforeach
      </div>
      <div class="mt-16 flex justify-center">
        <p
          class="relative rounded-full bg-gray-50 dark:bg-gray-900 px-6 sm:px-4 py-1.5 text-sm/6 text-mute ring-1 ring-inset ring-gray-900/5">
          <span
            class="hidden sm:inline">{{ __('Over {x} products for {n} categories.', ['x' => $total_products, 'n' => $total_categories]) }}</span>
          <a href="{{ route('shop.categories') }}" wire:navigate w.hover class="link"><span class="absolute inset-0"
              aria-hidden="true"></span>
            {{ __('View all categories') }} <span aria-hidden="true">&rarr;</span></a>
        </p>
      </div>
    </div>
  </div>

  <!-- CTA Section -->
  @if (!empty($shop_index_settings['shop_cta'] ?? null))
    <section aria-labelledby="comfort-heading" class="mx-auto max-w-7xl px-4 py-12 sm:px-6 sm:py-24 lg:px-8">
      <div class="relative overflow-hidden rounded-lg">
        @if ($shop_index_settings['shop_cta']['bg_image'] ?? null)
          <div class="absolute inset-0">
            <img src="{{ $shop_index_settings['shop_cta']['bg_image'] }}" alt="" class="size-full object-cover">
          </div>
        @endif
        <div class="relative bg-gray-900/75 px-6 py-32 sm:px-12 sm:py-40 lg:px-16">
          <div class="relative mx-auto flex max-w-3xl flex-col items-center text-center">
            <h2 id="comfort-heading" class="text-3xl font-bold tracking-tight text-white sm:text-4xl">
              {{ $shop_index_settings['shop_cta']['heading'] }}</h2>
            <p class="mt-3 text-xl text-white">{{ $shop_index_settings['shop_cta']['description'] }}</p>
            <a href="{{ $shop_index_settings['shop_cta']['button_link'] }}"
              class="mt-8 btn-primary btn-lg">{{ $shop_index_settings['shop_cta']['button_text'] }}</a>
          </div>
        </div>
      </div>
    </section>
  @endif
</div>
