@if ($brands && $brands->count() == 1)
  <x-slot name="title">{{ $brands->first()->name }}</x-slot>
  <x-slot name="metaDesc">{{ $brands->first()->description ?? '' }}</x-slot>
  <x-slot name="ogMetaData">
    <meta name="og:site_name" content="{{ $brands->first()->name }}" />
    <meta name="og:title" content="{{ $brands->first()->name }}" />
    <meta name="og:url" content="{{ route('shop.brands', ['brand' => $brands->first()->slug]) }}" />
    <meta name="og:image" content="{{ asset($brands->first()->photo ?: '/img/social/products.jpg') }}" />
    <meta name="twitter:card" content="summary" />
  </x-slot>
@elseif ($categories && $categories->count() == 1)
  <x-slot name="title">{{ $categories->first()->name }}</x-slot>
  <x-slot name="metaDesc">{{ $categories->first()->description ?? '' }}</x-slot>
  <x-slot name="ogMetaData">
    <meta name="og:site_name" content="{{ $categories->first()->name }}" />
    <meta name="og:title" content="{{ $categories->first()->name }}" />
    <meta name="og:url" content="{{ route('shop.categories', ['category' => $categories->first()->slug]) }}" />
    <meta name="og:image" content="{{ asset($categories->first()->photo ?: '/img/social/products.jpg') }}" />
    <meta name="twitter:card" content="summary" />
  </x-slot>
@elseif ($subcategories && $subcategories->count() == 1)
  <x-slot name="title">{{ $subcategories->first()->name }}</x-slot>
  <x-slot name="metaDesc">{{ $subcategories->first()->description ?? '' }}</x-slot>
  <x-slot name="ogMetaData">
    <meta name="og:site_name" content="{{ $subcategories->first()->name }}" />
    <meta name="og:title" content="{{ $subcategories->first()->name }}" />
    <meta name="og:url"
      content="{{ route('shop.categories', ['category' => $categories->first()->slug, 'subcategory' => $subcategories->first()->slug]) }}" />
    <meta name="og:image"
      content="{{ asset($subcategories->first()->photo ?: $categories->first()->photo ?: '/img/social/products.jpg') }}" />
    <meta name="twitter:card" content="summary" />
  </x-slot>
@else
  <x-slot name="title">{{ $seo['products_title'] ?? config('app.name') }}</x-slot>
  <x-slot name="metaDesc">{{ $seo['products_description'] ?? '' }}</x-slot>
  <x-slot name="ogMetaData">
    <meta name="og:site_name" content="{{ $seo['products_title'] ?? config('app.name') }}" />
    <meta name="og:title" content="{{ $seo['products_title'] ?? config('app.name') }}" />
    <meta name="og:url" content="{{ route('shop.products') }}" />
    <meta name="og:image" content="{{ asset('/img/social/products.jpg') }}" />
    <meta name="twitter:card" content="summary" />
  </x-slot>
@endif

<div x-data="{ mobileFilter: false }">
  <div class="min-h-screen lg:hidden fixed top-0 z-40" x-show="mobileFilter" style="display: none">
    <div class="relative z-40">
      {{-- Overlay, show/hide based on off-canvas filters state. --}}
      <div x-show="mobileFilter" @click="mobileFilter = false" x-transition.opacity
        class="fixed inset-0 bg-gray-50/50 dark:bg-gray-700/80 transition-opacity" aria-hidden="true"></div>

      <div class="fixed z-40 inset-y-0 end-0 max-w-xs w-full max-h-dvh min-h-dvh">
        <div class="relative ms-auto flex size-full flex-col overflow-y-auto bg-white dark:bg-gray-950 pt-4 pb-6 shadow-xl"
          x-show="mobileFilter" style="display: none" x-trap.noscroll.inert="mobileFilter"
          x-transition:enter="transform transition ease-in-out duration-250" x-transition:enter-start="translate-x-full"
          x-transition:enter-end="translate-x-0" x-transition:leave="transform transition ease-in-out duration-250"
          x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
          <div class="flex items-center justify-between px-4">
            <h2 class="text-lg font-medium text-prominent">{{ __('Filters') }}</h2>
            <button type="button" @click="mobileFilter = false"
              class="relative -me-2 flex size-10 items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-50 focus:ring-2 focus:ring-primary-500 focus:outline-hidden">
              <span class="absolute -inset-0.5"></span>
              <span class="sr-only">{{ __('Close') }}</span>
              <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"
                data-slot="icon">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Filters -->
          <form class="mt-4">
            <div class="border-t border-gray-200 dark:border-gray-700 pt-4 pb-4">
              <fieldset>
                <legend class="w-full px-2">
                  <!-- Expand/collapse section button -->
                  <button type="button" class="flex w-full items-center justify-between p-2 text-gray-400 hover:text-gray-500 x-focus">
                    <span class="text-sm font-medium text-prominent">{{ __('Availabilty') }}</span>
                    <span class="ms-6 flex h-7 items-center">
                      <!--
                        Expand/collapse icon, toggle classes based on section open state.

                        Open: "-rotate-180", Closed: "rotate-0"
                      -->
                      <svg class="size-5 rotate-0 transform" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                        <path fill-rule="evenodd"
                          d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z"
                          clip-rule="evenodd" />
                      </svg>
                    </span>
                  </button>
                </legend>
                <div class="px-4 pt-4 pb-2">
                  <div class="space-y-6">
                    <div class="flex gap-3">
                      <div class="flex h-5 shrink-0 items-center">
                        <div class="group grid size-4 grid-cols-1">
                          <input id="in_stock" name="in_stock" value="1" wire:model.live="filters.in_stock" type="checkbox"
                            class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 checked:border-primary-600 checked:bg-primary-600 indeterminate:border-primary-600 indeterminate:bg-primary-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                          <svg
                            class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25"
                            viewBox="0 0 14 14" fill="none">
                            <path class="opacity-0 group-has-checked:opacity-100" d="M3 8L6 11L11 3.5" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round" />
                            <path class="opacity-0 group-has-indeterminate:opacity-100" d="M3 7H11" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round" />
                          </svg>
                        </div>
                      </div>
                      <label for="in_stock" class="text-sm text-mute">{{ __('Available Products') }}</label>
                    </div>
                    <div class="flex gap-3">
                      <div class="flex h-5 shrink-0 items-center">
                        <div class="group grid size-4 grid-cols-1">
                          <input id="featured" name="featured" value="1" wire:model.live="filters.featured" type="checkbox"
                            class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 checked:border-primary-600 checked:bg-primary-600 indeterminate:border-primary-600 indeterminate:bg-primary-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                          <svg
                            class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25"
                            viewBox="0 0 14 14" fill="none">
                            <path class="opacity-0 group-has-checked:opacity-100" d="M3 8L6 11L11 3.5" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round" />
                            <path class="opacity-0 group-has-indeterminate:opacity-100" d="M3 7H11" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round" />
                          </svg>
                        </div>
                      </div>
                      <label for="featured" class="text-sm text-mute">{{ __('Featured Products') }}</label>
                    </div>
                    <div class="flex gap-3">
                      <div class="flex h-5 shrink-0 items-center">
                        <div class="group grid size-4 grid-cols-1">
                          <input id="on_promo" name="on_promo" value="1" wire:model.live="filters.on_promo" type="checkbox"
                            class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 checked:border-primary-600 checked:bg-primary-600 indeterminate:border-primary-600 indeterminate:bg-primary-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                          <svg
                            class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25"
                            viewBox="0 0 14 14" fill="none">
                            <path class="opacity-0 group-has-checked:opacity-100" d="M3 8L6 11L11 3.5" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round" />
                            <path class="opacity-0 group-has-indeterminate:opacity-100" d="M3 7H11" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round" />
                          </svg>
                        </div>
                      </div>
                      <label for="on_promo" class="text-sm text-mute">{{ __('Promotional Products') }}</label>
                    </div>
                  </div>
                </div>
              </fieldset>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700 pt-4 pb-4">
              <fieldset>
                <legend class="w-full px-2">
                  <button type="button" class="flex w-full items-center justify-between p-2 text-gray-400 hover:text-gray-500 x-focus">
                    <span class="text-sm font-medium text-prominent">{{ __('Price') }}</span>
                    <span class="ms-6 flex h-7 items-center">
                      <!--
                        Expand/collapse icon, toggle classes based on section open state.

                        Open: "-rotate-180", Closed: "rotate-0"
                      -->
                      <svg class="size-5 rotate-0 transform" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"
                        data-slot="icon">
                        <path fill-rule="evenodd"
                          d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z"
                          clip-rule="evenodd" />
                      </svg>
                    </span>
                  </button>
                </legend>
                <div class="px-4 pt-4 pb-2 gap-3 flex">
                  <x-shop::jet.input type="number" placeholder="{{ __('Minimum') }}" wire:model.live="filters.min_price"
                    class="pe-0" />
                  <x-shop::jet.input type="number" placeholder="{{ __('Maximum') }}" wire:model.live="filters.max_price"
                    class="pe-0" />
                </div>
              </fieldset>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-4 pb-4">
              <fieldset>
                <legend class="w-full px-2">
                  <button type="button" class="flex w-full items-center justify-between p-2 text-gray-400 hover:text-gray-500 x-focus">
                    <span class="text-sm font-medium text-prominent">{{ __('Categories') }}</span>
                    <span class="ms-6 flex h-7 items-center">
                      <!--
                        Expand/collapse icon, toggle classes based on section open state.

                        Open: "-rotate-180", Closed: "rotate-0"
                      -->
                      <svg class="size-5 rotate-0 transform" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"
                        data-slot="icon">
                        <path fill-rule="evenodd"
                          d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z"
                          clip-rule="evenodd" />
                      </svg>
                    </span>
                  </button>
                </legend>
                <div class="px-4 pt-4 pb-2 flex flex-col gap-y-5">
                  @forelse ($categoriesMenu as $category)
                    <div class="flex gap-3 -mx-2 -my-2.5 p-3">
                      <div class="flex h-5 shrink-0 items-center">
                        <div class="group grid size-4 grid-cols-1">
                          <input id="{{ $category->slug }}" name="categories[]" value="{{ $category->id }}"
                            wire:model.live="filters.categories" type="checkbox"
                            class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 checked:border-primary-600 checked:bg-primary-600 indeterminate:border-primary-600 indeterminate:bg-primary-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                          <svg
                            class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25"
                            viewBox="0 0 14 14" fill="none">
                            <path class="opacity-0 group-has-checked:opacity-100" d="M3 8L6 11L11 3.5" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round" />
                            <path class="opacity-0 group-has-indeterminate:opacity-100" d="M3 7H11" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round" />
                          </svg>
                        </div>
                      </div>
                      <label for="{{ $category->slug }}" class="text-sm text-mute">{{ $category->name }}</label>
                    </div>
                    @forelse ($category->children as $subcategory)
                      <div class="flex gap-3 ps-4 -my-2.5 py-3">
                        <div class="flex h-5 shrink-0 items-center">
                          <div class="group grid size-4 grid-cols-1">
                            <input id="{{ $subcategory->slug }}" name="categories[]" value="{{ $subcategory->id }}"
                              wire:model.live="filters.subcategories" type="checkbox"
                              class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 checked:border-primary-600 checked:bg-primary-600 indeterminate:border-primary-600 indeterminate:bg-primary-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                            <svg
                              class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25"
                              viewBox="0 0 14 14" fill="none">
                              <path class="opacity-0 group-has-checked:opacity-100" d="M3 8L6 11L11 3.5" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" />
                              <path class="opacity-0 group-has-indeterminate:opacity-100" d="M3 7H11" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                          </div>
                        </div>
                        <label for="{{ $subcategory->slug }}" class="text-sm text-mute">{{ $subcategory->name }}</label>
                      </div>
                    @empty
                    @endforelse
                  @empty
                  @endforelse
                </div>
              </fieldset>
            </div>
            <div class="border-t border-gray-200 dark:border-gray-700 pt-4 pb-4">
              <fieldset>
                <legend class="w-full px-2">
                  <button type="button" class="flex w-full items-center justify-between p-2 text-gray-400 hover:text-gray-500 x-focus">
                    <span class="text-sm font-medium text-prominent">{{ __('Brands') }}</span>
                    <span class="ms-6 flex h-7 items-center">
                      <!--
                        Expand/collapse icon, toggle classes based on section open state.

                        Open: "-rotate-180", Closed: "rotate-0"
                      -->
                      <svg class="size-5 rotate-0 transform" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"
                        data-slot="icon">
                        <path fill-rule="evenodd"
                          d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z"
                          clip-rule="evenodd" />
                      </svg>
                    </span>
                  </button>
                </legend>
                <div class="px-4 pt-4 pb-2 flex flex-col gap-y-5">
                  @forelse ($brandsMenu as $brand)
                    <div class="flex gap-3 -mx-2 -my-2.5 p-3">
                      <div class="flex h-5 shrink-0 items-center">
                        <div class="group grid size-4 grid-cols-1">
                          <input id="{{ $brand->slug }}" name="brands[]" value="{{ $brand->id }}" wire:model.live="filters.brands"
                            type="checkbox"
                            class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 checked:border-primary-600 checked:bg-primary-600 indeterminate:border-primary-600 indeterminate:bg-primary-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                          <svg
                            class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25"
                            viewBox="0 0 14 14" fill="none">
                            <path class="opacity-0 group-has-checked:opacity-100" d="M3 8L6 11L11 3.5" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round" />
                            <path class="opacity-0 group-has-indeterminate:opacity-100" d="M3 7H11" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round" />
                          </svg>
                        </div>
                      </div>
                      <label for="{{ $brand->slug }}" class="text-sm text-mute">{{ $brand->name }}</label>
                    </div>
                  @empty
                  @endforelse
                </div>
              </fieldset>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="mx-auto max-w-7xl sm:px-6 pt-8 sm:pt-16 lg:px-8">
    <div class="mx-auto max-w-2xl lg:max-w-full px-4 md:px-0">
      <div class="border-b border-gray-200 dark:border-gray-700 pb-10 flex flex-wrap items-end justify-between gap-4">
        <div>
          <h1 class="text-4xl font-bold tracking-tight ">
            {{ $brands && $brands->count() == 1 ? $brands->first()->name : ($categories && $categories->count() == 1 ? ($subcategories && $subcategories->count() == 1 ? $subcategories->first()->name . ' / ' : '') . $categories->first()->name : __('Products')) }}
            <svg wire:loading class="pointer-events-none size-6 text-gray-700 dark:text-gray-300" viewBox="0 0 38 38"
              stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
              <g fill="none" fill-rule="evenodd">
                <g transform="translate(1 1)" stroke-width="2">
                  <circle stroke-opacity=".5" cx="18" cy="18" r="18" />
                  <path d="M36 18c0-9.94-8.06-18-18-18">
                    <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s"
                      repeatCount="indefinite" />
                  </path>
                </g>
              </g>
            </svg>
          </h1>
          @if ($search ?? null)
            <p class="mt-2 text-base text-mute">
              {{ __('{x} products matched search term `{query}`, please view them below', ['query' => $search, 'x' => $products->total()]) }}
            </p>
          @else
            <p class="mt-2 text-base text-mute">{{ __('Please check our products below, you can filter them for your choice.') }}</p>
          @endif
        </div>

        <form method="GET" action="{{ route('shop.products') }}" class="grid grid-cols-1 relative w-full lg:w-auto">
          <input type="text" wire:model.live.debounce.250ms="search" placeholder="{{ __('Search') }}..." class="input">
          @if ($search ?? null)
            <svg wire:loading class="absolute end-3 pointer-events-none size-5 self-center text-gray-400" viewBox="0 0 38 38"
              stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
              <g fill="none" fill-rule="evenodd">
                <g transform="translate(1 1)" stroke-width="2">
                  <circle stroke-opacity=".5" cx="18" cy="18" r="18" />
                  <path d="M36 18c0-9.94-8.06-18-18-18">
                    <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s"
                      repeatCount="indefinite" />
                  </path>
                </g>
              </g>
            </svg>
          @endif
        </form>
      </div>

      @if (
          $filters['in_stock'] ||
              $filters['featured'] ||
              $filters['on_promo'] ||
              $filters['min_price'] ||
              $filters['max_price'] ||
              $brands ||
              $categories ||
              $subcategories)
        <nav aria-label="Breadcrumb" class="flex border-b border-gray-200 dark:border-gray-700">
          <div class="p-2">{{ __('Filters') }}:</div>
          <div role="list" class="flex flex-wrap w-full space-x-4 px-4 py-2">
            @if ($filters['in_stock'])
              <div class="font-bold">
                {{ __('Available') }}
              </div>
            @endif
            @if ($filters['featured'])
              <div class="font-bold">
                {{ __('Featured') }}
              </div>
            @endif
            @if ($filters['on_promo'])
              <div class="font-bold">
                {{ __('On Promo') }}
              </div>
            @endif
            @if ($filters['min_price'] || $filters['max_price'])
              <div class="font-bold">
                {{ __('Price') }} <span
                  class="text-mute font-normal">({{ ($filters['min_price'] ? __('Min.') . ' ' . currency_value($filters['min_price'], true) : '') . ($filters['max_price'] ? ($filters['min_price'] ? ' & ' : '') . __('Max.') . ' ' . currency_value($filters['max_price'], true) : '') }})</span>
              </div>
            @endif
            @if ($brands)
              <div class="flex items-center font-bold">
                {{ __('Brands') }}
                <span class="ms-2 text-mute font-normal">
                  @foreach ($brands as $brand)
                    {{ $brand->name . ($loop->last ? '' : ',') }}
                  @endforeach
                </span>
              </div>
            @endif
            @if ($categories)
              <div class="flex items-center font-bold">
                {{ __('Categories') }}
                <span class="ms-2 text-mute font-normal">
                  @foreach ($categories as $category)
                    {{ $category->name . ($loop->last ? '' : ',') }}
                  @endforeach
                </span>
              </div>
            @endif
            @if ($subcategories)
              <div class="flex items-center font-bold">
                {{ __('Subcategories') }}
                <span class="ms-2 text-mute font-normal">
                  @foreach ($subcategories as $subcategory)
                    {{ $subcategory->name . ($loop->last ? '' : ',') }}
                  @endforeach
                </span>
              </div>
            @endif

            </ol>
        </nav>
      @endif


      <div class="pt-12 pb-24 lg:grid lg:grid-cols-4 lg:gap-x-8 xl:grid-cols-5">
        <aside>
          <div @class(['sticky top-8' => $shop_settings['sticky_sidebar'] ?? false])>
            <h2 class="sr-only">{{ __('Filters') }}</h2>

            <!-- Mobile filter dialog toggle, controls the 'mobileFilterDialogOpen' state. -->
            <button @click="mobileFilter = true" type="button" class="inline-flex items-center lg:hidden x-focus">
              <span class="text-sm font-medium ">{{ __('Filters') }}</span>
              <svg class="ms-1 size-5 shrink-0 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"
                data-slot="icon">
                <path
                  d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
              </svg>
            </button>

            <div class="hidden lg:block">
              <form method="post" class="divide-y divide-gray-200 dark:divide-gray-700">
                <div class="pt-6 pb-4 first:pt-0 last:pb-0">
                  <fieldset>
                    <legend class="block text-sm font-medium text-prominent pb-1">{{ __('Availabilty') }}</legend>
                    <div class="space-y-3 py-3 max-h-80 overflow-y-auto">
                      <div class="flex gap-3 -mx-2 -my-2.5 p-3">
                        <div class="flex h-5 shrink-0 items-center">
                          <div class="group grid size-4 grid-cols-1">
                            <input id="in_stock" name="in_stock" value="1" wire:model.live="filters.in_stock" type="checkbox"
                              class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 checked:bg-primary-600 indeterminate:border-primary-600 indeterminate:bg-primary-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                            <svg
                              class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25"
                              viewBox="0 0 14 14" fill="none">
                              <path class="opacity-0 group-has-checked:opacity-100" d="M3 8L6 11L11 3.5" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" />
                              <path class="opacity-0 group-has-indeterminate:opacity-100" d="M3 7H11" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                          </div>
                        </div>
                        <label for="in_stock" class="text-sm text-mute">{{ __('Available Products') }}</label>
                      </div>
                      <div class="flex gap-3 -mx-2 -my-2.5 p-3">
                        <div class="flex h-5 shrink-0 items-center">
                          <div class="group grid size-4 grid-cols-1">
                            <input id="featured" name="featured" value="1" wire:model.live="filters.featured" type="checkbox"
                              class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 checked:border-primary-600 checked:bg-primary-600 indeterminate:border-primary-600 indeterminate:bg-primary-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                            <svg
                              class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25"
                              viewBox="0 0 14 14" fill="none">
                              <path class="opacity-0 group-has-checked:opacity-100" d="M3 8L6 11L11 3.5" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" />
                              <path class="opacity-0 group-has-indeterminate:opacity-100" d="M3 7H11" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                          </div>
                        </div>
                        <label for="featured" class="text-sm text-mute">{{ __('Featured Products') }}</label>
                      </div>
                      <div class="flex gap-3 -mx-2 -my-2.5 p-3">
                        <div class="flex h-5 shrink-0 items-center">
                          <div class="group grid size-4 grid-cols-1">
                            <input id="on_promo" name="on_promo" value="1" wire:model.live="filters.on_promo" type="checkbox"
                              class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 checked:border-primary-600 checked:bg-primary-600 indeterminate:border-primary-600 indeterminate:bg-primary-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                            <svg
                              class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25"
                              viewBox="0 0 14 14" fill="none">
                              <path class="opacity-0 group-has-checked:opacity-100" d="M3 8L6 11L11 3.5" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" />
                              <path class="opacity-0 group-has-indeterminate:opacity-100" d="M3 7H11" stroke-width="2"
                                stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                          </div>
                        </div>
                        <label for="on_promo" class="text-sm text-mute">{{ __('Promotional Products') }}</label>
                      </div>
                    </div>
                  </fieldset>
                </div>
                <div class="pt-6 pb-4 first:pt-0 last:pb-0">
                  <fieldset>
                    <legend class="block text-sm font-medium text-prominent pb-1">{{ __('Price') }}</legend>
                    <div class="gap-3 py-3 flex">
                      <x-shop::jet.input type="number" placeholder="{{ __('Minimum') }}" wire:model.live="filters.min_price"
                        class="pe-0" />
                      <x-shop::jet.input type="number" placeholder="{{ __('Maximum') }}" wire:model.live="filters.max_price"
                        class="pe-0" />
                    </div>
                  </fieldset>
                </div>
                <div class="pt-6 pb-4 first:pt-0 last:pb-0">
                  <fieldset>
                    <legend class="block text-sm font-medium text-prominent pb-1">{{ __('Brands') }}</legend>
                    <div class="space-y-3 py-3 max-h-80 overflow-y-auto">
                      @forelse ($brandsMenu as $brand)
                        <div class="flex gap-3 -mx-2 -my-2.5 p-3">
                          <div class="flex h-5 shrink-0 items-center">
                            <div class="group grid size-4 grid-cols-1">
                              <input id="{{ $brand->slug }}" name="brands[]" value="{{ $brand->id }}"
                                wire:model.live="filters.brands" type="checkbox"
                                class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 checked:border-primary-600 checked:bg-primary-600 indeterminate:border-primary-600 indeterminate:bg-primary-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                              <svg
                                class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25"
                                viewBox="0 0 14 14" fill="none">
                                <path class="opacity-0 group-has-checked:opacity-100" d="M3 8L6 11L11 3.5" stroke-width="2"
                                  stroke-linecap="round" stroke-linejoin="round" />
                                <path class="opacity-0 group-has-indeterminate:opacity-100" d="M3 7H11" stroke-width="2"
                                  stroke-linecap="round" stroke-linejoin="round" />
                              </svg>
                            </div>
                          </div>
                          <label for="{{ $brand->slug }}" class="text-sm text-mute">{{ $brand->name }}</label>
                        </div>
                      @empty
                      @endforelse
                    </div>
                  </fieldset>
                </div>
                <div class="pt-6 pb-4 first:pt-0 last:pb-0">
                  <fieldset>
                    <legend class="block text-sm font-medium text-prominent pb-1">{{ __('Categories') }}</legend>
                    <div class="space-y-3 py-3 max-h-80 overflow-y-auto">
                      @forelse ($categoriesMenu as $category)
                        <div class="flex gap-3 -mx-2 -my-2.5 p-3">
                          <div class="flex h-5 shrink-0 items-center">
                            <div class="group grid size-4 grid-cols-1">
                              <input id="{{ $category->slug }}" name="categories[]" value="{{ $category->id }}"
                                wire:model.live="filters.categories" type="checkbox"
                                class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 checked:border-primary-600 checked:bg-primary-600 indeterminate:border-primary-600 indeterminate:bg-primary-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                              <svg
                                class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25"
                                viewBox="0 0 14 14" fill="none">
                                <path class="opacity-0 group-has-checked:opacity-100" d="M3 8L6 11L11 3.5" stroke-width="2"
                                  stroke-linecap="round" stroke-linejoin="round" />
                                <path class="opacity-0 group-has-indeterminate:opacity-100" d="M3 7H11" stroke-width="2"
                                  stroke-linecap="round" stroke-linejoin="round" />
                              </svg>
                            </div>
                          </div>
                          <label for="{{ $category->slug }}" class="text-sm text-mute">{{ $category->name }}</label>
                        </div>
                        @forelse ($category->children as $subcategory)
                          <div class="flex gap-3 ps-4 -my-2.5 py-3">
                            <div class="flex h-5 shrink-0 items-center">
                              <div class="group grid size-4 grid-cols-1">
                                <input id="{{ $subcategory->slug }}" name="categories[]" value="{{ $subcategory->id }}"
                                  wire:model.live="filters.subcategories" type="checkbox"
                                  class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 checked:border-primary-600 checked:bg-primary-600 indeterminate:border-primary-600 indeterminate:bg-primary-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                                <svg
                                  class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25"
                                  viewBox="0 0 14 14" fill="none">
                                  <path class="opacity-0 group-has-checked:opacity-100" d="M3 8L6 11L11 3.5" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                  <path class="opacity-0 group-has-indeterminate:opacity-100" d="M3 7H11" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                              </div>
                            </div>
                            <label for="{{ $subcategory->slug }}" class="text-sm text-mute">{{ $subcategory->name }}</label>
                          </div>
                        @empty
                        @endforelse
                      @empty
                      @endforelse
                    </div>
                  </fieldset>
                </div>
                {{-- <div class="pt-6 pb-4 first:pt-0 last:pb-0">
                <fieldset>
                  <legend class="block text-sm font-medium text-prominent pb-1">Sizes</legend>
                  <div class="space-y-3 pt-6">
                    <div class="flex gap-3">
                      <div class="flex h-5 shrink-0 items-center">
                        <div class="group grid size-4 grid-cols-1">
                          <input id="sizes-0" name="sizes[]" value="xs" type="checkbox"
                            class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 checked:border-primary-600 checked:bg-primary-600 indeterminate:border-primary-600 indeterminate:bg-primary-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                          <svg
                            class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25"
                            viewBox="0 0 14 14" fill="none">
                            <path class="opacity-0 group-has-checked:opacity-100" d="M3 8L6 11L11 3.5" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round" />
                            <path class="opacity-0 group-has-indeterminate:opacity-100" d="M3 7H11" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round" />
                          </svg>
                        </div>
                      </div>
                      <label for="sizes-0" class="text-sm text-mute">XS</label>
                    </div>
                    <div class="flex gap-3">
                      <div class="flex h-5 shrink-0 items-center">
                        <div class="group grid size-4 grid-cols-1">
                          <input id="sizes-1" name="sizes[]" value="s" type="checkbox"
                            class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 checked:border-primary-600 checked:bg-primary-600 indeterminate:border-primary-600 indeterminate:bg-primary-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                          <svg
                            class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25"
                            viewBox="0 0 14 14" fill="none">
                            <path class="opacity-0 group-has-checked:opacity-100" d="M3 8L6 11L11 3.5" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round" />
                            <path class="opacity-0 group-has-indeterminate:opacity-100" d="M3 7H11" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round" />
                          </svg>
                        </div>
                      </div>
                      <label for="sizes-1" class="text-sm text-mute">S</label>
                    </div>
                    <div class="flex gap-3">
                      <div class="flex h-5 shrink-0 items-center">
                        <div class="group grid size-4 grid-cols-1">
                          <input id="sizes-2" name="sizes[]" value="m" type="checkbox"
                            class="col-start-1 row-start-1 appearance-none rounded-sm border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 checked:border-primary-600 checked:bg-primary-600 indeterminate:border-primary-600 indeterminate:bg-primary-600 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-600 disabled:border-gray-300 disabled:bg-gray-100 disabled:checked:bg-gray-100 forced-colors:appearance-auto" />
                          <svg
                            class="pointer-events-none col-start-1 row-start-1 size-3.5 self-center justify-self-center stroke-white group-has-disabled:stroke-gray-950/25"
                            viewBox="0 0 14 14" fill="none">
                            <path class="opacity-0 group-has-checked:opacity-100" d="M3 8L6 11L11 3.5" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round" />
                            <path class="opacity-0 group-has-indeterminate:opacity-100" d="M3 7H11" stroke-width="2"
                              stroke-linecap="round" stroke-linejoin="round" />
                          </svg>
                        </div>
                      </div>
                      <label for="sizes-2" class="text-sm text-mute">M</label>
                    </div>
                  </div>
                </fieldset>
              </div> --}}
              </form>
            </div>
          </div>
        </aside>

        <section aria-labelledby="product-heading" class="mt-6 lg:col-span-3 lg:mt-0 xl:col-span-4">
          <h2 id="product-heading" class="sr-only">{{ __('Products') }}</h2>

          <div class="grid grid-cols-1 gap-y-4 sm:grid-cols-2 sm:gap-x-6 sm:gap-y-8 lg:gap-x-8 xl:grid-cols-3">
            <div wire:loading class="col-span-full rounded-lg bg-white/50 dark:bg-gray-900/50">
              <div class="p-10 flex justify-center">
                <svg class="pointer-events-none size-10 text-gray-700 dark:text-gray-300" viewBox="0 0 38 38" stroke="currentColor"
                  xmlns="http://www.w3.org/2000/svg">
                  <g fill="none" fill-rule="evenodd">
                    <g transform="translate(1 1)" stroke-width="2">
                      <circle stroke-opacity=".5" cx="18" cy="18" r="18" />
                      <path d="M36 18c0-9.94-8.06-18-18-18">
                        <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s"
                          repeatCount="indefinite" />
                      </path>
                    </g>
                  </g>
                </svg>
              </div>
            </div>
            @foreach ($products as $product)
              <x-shop::product :product="$product" :key="$product->id" />
            @endforeach
          </div>

          <div class="mt-6">{{ $products->links() }}</div>
        </section>
      </div>
    </div>
  </div>
</div>
