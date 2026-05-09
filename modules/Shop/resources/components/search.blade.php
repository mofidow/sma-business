<div class="grow relative group mx-full" :class="openSearch ? 'z-30' : 'z-20'" x-data="{
    query: '',
    openSearch: false,
    init() {
        this.$watch('query', value => {
            {{-- this.$wire.updateSearch(value); --}}
            if (value.length > 0) {
                this.openSearch = true;
            } else if (window.innerWidth >= 768) {
                this.openSearch = false;
            }
        });
        this.$watch('openSearch', open => {
            if (open) {
                document.getElementById('header-search-input').focus();
            }
        });
    }
}">
  <div class="grow relative z-30 group mx-full max-w-xl min-h-12" :class="openSearch ? 'flex' : 'hidden md:flex'">
    <div x-show="openSearch" style="display: none" x-transition.opacity
      class="fixed inset-0 bg-gray-50/50 dark:bg-gray-700/80 transition-opacity" @click="openSearch = false"></div>
    <div :class="{ 'fixed inset-x-4 top-4 md:absolute md:inset-x-auto md:top-0': openSearch }"
      class="mx-auto md:w-full transform divide-y divide-gray-100 dark:divide-gray-900 overflow-hidden rounded-md bg-white dark:bg-gray-800 focus-within:shadow-2xl dark:shadow-gray-600/50 transition-all border-1 border-gray-200 dark:border-gray-800">
      <form method="GET" action="{{ route('shop.products') }}" class="grid grid-cols-1">
        <input id="header-search-input" type="text" name="search" x-model="query" wire:model.live.debounce.250ms="search"
          placeholder="{{ __('Search') }}..."
          class="border-0 x-focus bg-gray-100 dark:bg-gray-900 col-start-1 row-start-1 h-12 w-full ps-11 pe-4 text-base outline-none placeholder:text-gray-400 sm:text-sm">
        <svg class="pointer-events-none col-start-1 row-start-1 ms-4 size-5 self-center text-gray-400" viewBox="0 0 20 20"
          fill="currentColor" aria-hidden="true" data-slot="icon">
          <path fill-rule="evenodd"
            d="M9 3.5a5.5 5.5 0 1 0 0 11 5.5 5.5 0 0 0 0-11ZM2 9a7 7 0 1 1 12.452 4.391l3.328 3.329a.75.75 0 1 1-1.06 1.06l-3.329-3.328A7 7 0 0 1 2 9Z"
            clip-rule="evenodd" />
        </svg>
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
      </form>

      {{-- {{ json_encode($results) }} --}}
      @if (!empty($results))
        <!-- Results, show/hide based on command palette state. -->
        <ul x-show="openSearch" class="transform-gpu scroll-py-10 scroll-pb-2 space-y-4 overflow-y-auto px-4"
          style="max-height: calc(100vh - 85px); display: none;">
          <li>
            <ul class="-mx-4 text-sm divide-y divide-gray-100 dark:divide-gray-900">
              @foreach ($results as $product)
                <li class="group flex cursor-default select-none items-center">
                  <a href="{{ route('shop.product', $product->slug) }}" wire:navigate w.hover
                    class="flex x-focus items-center w-full gap-x-3 px-4 py-2 text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-900">
                    @if ($product->photo)
                      <img src="{{ $product->photo }}" alt="{{ $product->name }}" class="size-12 flex-none rounded-full object-cover"
                        loading="lazy">
                    @else
                      <svg class="size-12 flex-none" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        aria-hidden="true" data-slot="icon">
                        <path stroke-linecap="round" stroke-linejoin="round"
                          d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                      </svg>
                    @endif
                    <div class="grow">
                      <p class="truncate font-bold">{{ $product->name }}</p>
                      @if ($product->description)
                        <p class="line-clamp-2 mt-1 text-sm leading-tight">{{ $product->description }}</p>
                      @endif
                    </div>
                  </a>
                </li>
              @endforeach
            </ul>
          </li>
        </ul>
      @endif

      @if (!$search && empty($results))
        <!-- Help, show/hide based on command palette state. -->
        <div x-show="openSearch" x-trap.noscroll.inert="openSearch" class="px-6 py-14 text-center text-sm sm:px-14" style="display: none;">
          <svg class="mx-auto size-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            aria-hidden="true" data-slot="icon">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M16.712 4.33a9.027 9.027 0 0 1 1.652 1.306c.51.51.944 1.064 1.306 1.652M16.712 4.33l-3.448 4.138m3.448-4.138a9.014 9.014 0 0 0-9.424 0M19.67 7.288l-4.138 3.448m4.138-3.448a9.014 9.014 0 0 1 0 9.424m-4.138-5.976a3.736 3.736 0 0 0-.88-1.388 3.737 3.737 0 0 0-1.388-.88m2.268 2.268a3.765 3.765 0 0 1 0 2.528m-2.268-4.796a3.765 3.765 0 0 0-2.528 0m4.796 4.796c-.181.506-.475.982-.88 1.388a3.736 3.736 0 0 1-1.388.88m2.268-2.268 4.138 3.448m0 0a9.027 9.027 0 0 1-1.306 1.652c-.51.51-1.064.944-1.652 1.306m0 0-3.448-4.138m3.448 4.138a9.014 9.014 0 0 1-9.424 0m5.976-4.138a3.765 3.765 0 0 1-2.528 0m0 0a3.736 3.736 0 0 1-1.388-.88 3.737 3.737 0 0 1-.88-1.388m2.268 2.268L7.288 19.67m0 0a9.024 9.024 0 0 1-1.652-1.306 9.027 9.027 0 0 1-1.306-1.652m0 0 4.138-3.448M4.33 16.712a9.014 9.014 0 0 1 0-9.424m4.138 5.976a3.765 3.765 0 0 1 0-2.528m0 0c.181-.506.475-.982.88-1.388a3.736 3.736 0 0 1 1.388-.88m-2.268 2.268L4.33 7.288m6.406 1.18L7.288 4.33m0 0a9.024 9.024 0 0 0-1.652 1.306A9.025 9.025 0 0 0 4.33 7.288" />
          </svg>
          <p class="mt-4 font-semibold">{{ __('Search Products') }}</p>
          <p class="mt-2 text-gray-500">{{ __('Please type to quickly search for products.') }}</p>
        </div>
      @endif

      @if ($search && count($results) <= 0)
        <!-- Empty state, show/hide based on command palette state. -->
        <div x-show="openSearch" class="px-6 py-14 text-center text-sm sm:px-14" style="display: none;">
          <svg class="mx-auto size-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            aria-hidden="true" data-slot="icon">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
          </svg>
          <p class="mt-4 font-semibold">{{ __('No results found') }}</p>
          <p class="mt-2 text-gray-500">{{ __("We couldn't find anything with term ':term'. Please try again.", ['term' => $search]) }}</p>
        </div>
      @endif

      {{-- <div x-show="openSearch" style="display: none"
            class="flex flex-wrap items-center bg-gray-50 dark:bg-gray-950 px-4 py-2.5 text-xs text-gray-700">Type
            <kbd class="mx-1 flex size-5 items-center justify-center rounded border border-gray-400 bg-white font-semibold sm:mx-2">#</kbd>
            <span class="sm:hidden">for projects,</span><span class="hidden sm:inline">to access projects,</span> <kbd
              class="mx-1 flex size-5 items-center justify-center rounded border border-gray-400 bg-white font-semibold sm:mx-2">&gt;</kbd>
            for users, and <kbd
              class="mx-1 flex size-5 items-center justify-center rounded border border-gray-400 bg-white font-semibold sm:mx-2">?</kbd>
            for help.
          </div> --}}
    </div>
  </div>

  <div class="flex items-center justify-end gap-x-1 sm:gap-x-4">
    <button type="button" @click="openSearch = true"
      class="md:hidden relative shrink-0 rounded-full p-1 text-gray-500 hover:text-gray-700">
      <span class="absolute -inset-1.5"></span>
      <span class="sr-only">{{ __('Search') }}</span>
      <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"
        data-slot="icon">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"></path>
      </svg>
    </button>
  </div>
</div>
