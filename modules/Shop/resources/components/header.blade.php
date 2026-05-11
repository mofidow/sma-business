<header class="bg-white tracking-normal print:hidden border-b border-gray-100 shadow-sm sticky top-0 z-40" x-data="{
    mobileMenu: false,
    categoriesMenu: false,
    brandsMenu: false,
    recentMenu: false,
    cartDrawer: false,
    iconMenu: false,
    showLogin() {
        $dispatch('open-login');
        $nextTick(() => {
            document.getElementById('login-username').focus();
        });
    },
    logout() {
        axios.post('/logout')
            .then(({ data }) => {
                $dispatch('notification', { content: data.message, type: 'success' });
                window.location.reload();
            })
            .catch(({ response }) => ($dispatch('notification', { content: response.data.message, type: 'error' })));
    }
}">

  @if ($shop_header_settings['notification']['message'] ?? null)
    <div class="text-white" style="background:linear-gradient(90deg,#064e3b,#065f46,#0f766e)">
      <div class="mx-auto max-w-7xl py-2.5 md:flex md:items-center md:justify-center px-4 sm:px-6 lg:px-8 text-sm/5 text-center">
        {{ $shop_header_settings['notification']['message'] }}
        @if (($shop_header_settings['notification']['button_text'] ?? null) && ($shop_header_settings['notification']['button_link'] ?? null))
          <a href="{{ $shop_header_settings['notification']['button_link'] }}"
            class="group relative text-xs font-bold hover:underline hover:decoration-dotted hover:decoration-1 hover:underline-offset-4 whitespace-nowrap ms-2">
            {{ $shop_header_settings['notification']['button_text'] }} <span aria-hidden="true"
              class="hidden group-hover:block ms-1 absolute start-full top-0">&rarr;</span>
          </a>
        @endif
      </div>
    </div>
  @endif

  {{-- Mobile Menus --}}
  <div x-show="mobileMenu" style="display: none" class="relative z-40 lg:hidden" role="dialog" aria-modal="true">
    <div class="fixed inset-0 flex rtl:flex-row-reverse">
      <div x-show="mobileMenu" x-transition.opacity class="fixed inset-0 bg-gray-50/50 dark:bg-gray-700/80 transition-opacity"
        @click="mobileMenu = false">
      </div>

      <div x-show="mobileMenu" x-transition:enter="transition ease-in-out duration-300 transform"
        x-transition:enter-start="ltr:-translate-x-full rtl:translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="ltr:-translate-x-full rtl:translate-x-full"
        class="relative flex w-full max-w-xs flex-col overflow-y-auto bg-white pb-4 shadow-2xl">
        <div class="flex justify-between gap-x-6 px-4 pb-2 py-5">
          <a href="{{ route('shop.home') }}" class="text-white x-focus max-w-48">
            @if (demo())
              <img src="/img/sma-icon.svg" alt="Logo" class="h-8 w-auto dark:invert">
            @else
              <img src="{{ $shop_header_settings['logo'] ?? '/img/sma-icon.svg' }}" alt="Logo" class="h-8 w-auto dark:hidden">
              <img src="{{ $shop_header_settings['logo_dark'] ?? '/img/sma-icon.svg' }}" alt="Logo"
                class="h-8 w-auto hidden dark:block">
            @endif
          </a>
          <button type="button" @click="mobileMenu = false"
            class="-m-2 inline-flex items-center justify-center rounded-md p-2 text-gray-400">
            <span class="sr-only">{{ __('Close') }}</span>
            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"
              data-slot="icon">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="space-y-6 px-4 py-6">
          <div class="flow-root">
            <a href="{{ route('shop.home') }}" class="-m-2 block p-2 font-medium x-focus">{{ __('Home') }}</a>
          </div>
          <div class="flow-root">
            <a href="{{ route('shop.products') }}" wire:navigate w.hover
              class="-m-2 block p-2 font-medium x-focus">{{ __('Products') }}</a>
          </div>
          <div class="flow-root">
            <a href="{{ route('shop.brands') }}" wire:navigate w.hover class="-m-2 block p-2 font-medium x-focus">{{ __('Brands') }}</a>
          </div>
          <div class="flow-root">
            <a href="{{ route('shop.categories') }}" wire:navigate w.hover
              class="-m-2 block p-2 font-medium x-focus">{{ __('Categories') }}</a>
          </div>
          <div class="flow-root">
            <a href="{{ route('shop.products', ['filters' => ['on_promo' => true]]) }}" wire:navigate w.hover
              class="-m-2 block p-2 font-medium x-focus">{{ __('Promotions') }}</a>
          </div>
        </div>

        @if ($shop_header_settings['page_menus'] ?? null)
          <div class="space-y-6 border-t border-gray-200 dark:border-gray-700 px-4 py-6">
            @foreach ($shop_header_settings['page_menus'] as $menu)
              <div class="flow-root">
                <a href="{{ $menu['link'] }}" class="-m-2 block p-2 font-medium x-focus">{{ $menu['label'] }}</a>
              </div>
            @endforeach
          </div>
        @endif

        <div class="space-y-6 border-t border-gray-200 dark:border-gray-700 px-4 py-6">
          @auth
            <div class="flow-root font-bold text-sm text-mute">
              {{ __('Hi! {x}', ['x' => auth()->user()->name]) }}
            </div>

            @if (auth()->user() && auth()->user()->employee)
              <div class="flow-root">
                <a href="{{ route('dashboard') }}" class="-m-2 block p-2 font-medium x-focus">{{ __('Dashboard') }}</a>
              </div>
              @if (auth()->user()?->can('read-settings'))
                <div class="flow-root">
                  <a href="{{ route('settings.index') }}" class="-m-2 block p-2 font-medium x-focus">{{ __('Admin Settings') }}</a>
                </div>
              @endif
            @endif
            <div class="flow-root">
              <a href="{{ route('shop.orders') }}" wire:navigate w.hover class="-m-2 block p-2 font-medium x-focus">
                {{ __('Orders') }}
              </a>
            </div>
            <div class="flow-root">
              <a href="{{ route('shop.addresses') }}" wire:navigate w.hover class="-m-2 block p-2 font-medium x-focus">
                {{ __('Addresses') }}
              </a>
            </div>
            <div class="flow-root">
              <a href="{{ route('shop.billing') }}" wire:navigate w.hover class="-m-2 block p-2 font-medium x-focus">
                {{ __('Billing Details') }}
              </a>
            </div>

            <div class="flow-root">
              <a href="{{ route('shop.profile') }}" wire:navigate w.hover class="-m-2 block p-2 font-medium x-focus">{{ __('Profile') }}</a>
            </div>

            <div class="flow-root">
              <button type="button" @click="logout()" class="-m-2 block p-2 font-medium x-focus">
                {{ __('Sign out') }}
              </button>
            </div>
          @else
            @if ($shop_header_settings['general']['user_registration'] ?? false)
              <div class="flow-root">
                <a href="{{ route('shop.signup') }}" wire:navigate w.hover
                  class="-m-2 block p-2 font-medium x-focus">{{ __('Create an account') }}</a>
              </div>
            @endif
            <div class="flow-root">
              <a href="{{ route('shop.signin') }}" wire:navigate w.hover class="-m-2 block p-2 font-medium x-focus">{{ __('Sign in') }}</a>
            </div>
          @endauth
        </div>
      </div>
    </div>
  </div>

  <!-- Cart Drawer -->
  <div x-show="cartDrawer" style="display: none" x-cloak class="fixed inset-0 overflow-hidden z-50">
    <div x-show="cartDrawer" x-transition.opacity class="fixed inset-0 bg-gray-50/50 dark:bg-gray-700/80 transition-opacity"
      @click="cartDrawer = false"></div>
    <!-- Cart -->
    <div class="fixed z-40 inset-y-0 end-0 max-w-md w-full max-h-dvh min-h-dvh">
      <div x-show="cartDrawer" x-trap.noscroll.inert="cartDrawer"
        x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
        x-transition:enter-start="ltr:translate-x-full rtl:-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="ltr:translate-x-full rtl:-translate-x-full" class="h-full w-full">
        <div class="h-full min-h-screen bg-white shadow-xl">
          <div class="flex items-start justify-between p-6 border-b border-gray-200 dark:border-gray-700">
            <a href="{{ route('shop.cart') }}" wire:navigate w.hover class="group flex items-center gap-2 x-focus">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
                class="size-6">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
              </svg>
              <h2 class="font-bold text-lg text-prominent">{{ __('Shopping Cart') }}</h2>
            </a>
            <div class="ms-3 flex h-7 items-center">
              <button type="button" @click="cartDrawer = false"
                class="relative -m-2 p-2 hover:text-prominent x-focus rounded-md hover:bg-gray-50 dark:hover:bg-gray-800">
                <span class="absolute -inset-0.5"></span>
                <span class="sr-only">{{ __('Close') }}</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true"
                  class="size-6">
                  <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </button>
            </div>
          </div>

          <livewire:components.cart.drawer />
        </div>
      </div>
    </div>
  </div>

  <!-- Categories Drawer -->
  <div x-show="categoriesMenu" style="display: none" x-cloak class="fixed inset-0 overflow-hidden z-50">
    <div x-show="categoriesMenu" x-transition.opacity class="fixed inset-0 bg-gray-50/50 dark:bg-gray-700/80 transition-opacity"
      @click="categoriesMenu = false">
    </div>
    <div class="fixed z-40 inset-y-0 start-0 max-w-xs w-full max-h-dvh min-h-dvh">
      <div x-show="categoriesMenu" x-trap.noscroll.inert="categoriesMenu"
        x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
        x-transition:enter-start="ltr:-translate-x-full rtl:translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="ltr:-translate-x-full rtl:translate-x-full" class="h-full w-full">
        <div class="h-full flex flex-col bg-white shadow-2xl">
          <!-- Close Button -->
          <div class="absolute end-0 top-0 me-3 mt-3">
            <button type="button" @click="categoriesMenu = false"
              class="relative inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md bg-transparent p-1.5 font-medium text-gray-400 hover:bg-gray-800/10 hover:text-gray-800">
              <span class="sr-only">{{ __('Close') }}</span>
              <svg class="size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path
                  d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z">
                </path>
              </svg>
            </button>
          </div>

          <div class="h-full">
            <h2 class="font-medium px-6 py-4 border-b border-gray-200 dark:border-gray-800 text-prominent">
              {{ __('Shop by Categories') }}
            </h2>

            {{-- <script>
              document.addEventListener('alpine:init', () => {
                Alpine.data('categories', () => ({
                  selected: null,
                  categories: @json($categoriesMenu),
                  showSubcategories(cId) {
                    this.selected = this.categories.find(category => category.id === cId);
                  }
                }))
              })
            </script> --}}
            {{-- x-data="categories" --}}
            <nav class="flex flex-1 flex-col overflow-y-auto" style="height: calc(100vh - 57px)" aria-label="Categories Sidebar">
              <ul role="list" class="w-full flex flex-1 flex-col h-full gap-y-7 px-6 py-4">
                <li>
                  <ul role="list" class="-mx-2 space-y-1">
                    @forelse ($categoriesMenu as $category)
                      <li>
                        @if ($category->children->isEmpty())
                          <a href="{{ route('shop.category', $category->slug) }}" wire:navigate w.hover
                            class="group w-full flex items-center justify-between gap-x-3 rounded-md p-2 text-sm/6 font-semibold link hover:bg-gray-100 dark:hover:bg-gray-800">
                            @if ($category->photo)
                              <img src="{{ $category->photo }}" alt="{{ $category->name }}"
                                class="size-6 shrink-0 rounded-md bg-gray-100 dark:bg-gray-900 object-cover">
                            @else
                              <svg class="size-6 shrink-0 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" aria-hidden="true" data-slot="icon">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                  d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                              </svg>
                            @endif

                            <span>{{ $category->name }}</span>
                            <span
                              class="ms-auto min-w-max whitespace-nowrap rounded-full bg-gray-50 dark:bg-gray-900 px-2.5 py-0.5 text-center text-xs/5 font-medium text-gray-600 dark:text-gray-400 ring-1 ring-inset ring-gray-200 dark:ring-gray-800">
                              {{ $category->products_count }} </span>
                          </a>
                        @else
                          {{-- @click="showSubcategories('{{ $category->id }}')" --}}
                          <div x-data="{ show: false }" class="relative">
                            <button type="button" @click="show = !show"
                              class="group w-full flex items-center justify-between gap-x-3 rounded-md p-2 text-sm/6 font-semibold link hover:bg-gray-100 dark:hover:bg-gray-800">
                              <span class="flex items-center gap-x-3">
                                @if ($category->photo)
                                  <img src="{{ $category->photo }}" alt="{{ $category->name }}"
                                    class="size-6 shrink-0 rounded-md bg-gray-100 dark:bg-gray-900 object-cover">
                                @else
                                  <svg class="size-6 shrink-0 text-primary-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" aria-hidden="true" data-slot="icon">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                      d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                  </svg>
                                @endif
                                <span>{{ $category->name }}</span>
                              </span>
                              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="size-4 text-mute">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                              </svg>
                            </button>

                            <ul x-show="show" x-transition role="list" class="w-full flex flex-1 flex-col h-full gap-y-7">
                              <li class="block">
                                {{-- <div
                                x-html="`<a href='/${selected?.slug}' class='grow link'>${selected?.name || ''}</a> <button type='button' @click='selected = null'><svg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke-width='1.5' stroke='currentColor' class='size-4 text-mute'><path stroke-linecap='round' stroke-linejoin='round' d='M6 18 18 6M6 6l12 12' /></svg></button>`"
                                class="px-6 py-3 text-sm font-bold bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                              </div> --}}
                                <ul role="list" class="-mx-2 space-y-1 ps-4 py-2 overflow-y-auto">
                                  <li>
                                    <a href="{{ route('shop.category', $category->slug) }}" wire:navigate w.hover
                                      class="group w-full flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold link hover:bg-gray-100 dark:hover:bg-gray-800">
                                      @if ($category->photo)
                                        <img src="{{ $category->photo }}" alt="{{ $category->name }}"
                                          class="size-6 shrink-0 rounded-md bg-gray-100 dark:bg-gray-900 object-cover">
                                      @else
                                        <svg class="size-6 shrink-0 text-primary-600" fill="none" viewBox="0 0 24 24"
                                          stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                                          <path stroke-linecap="round" stroke-linejoin="round"
                                            d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                        </svg>
                                      @endif

                                      <span>{{ __('All Products') }}</span>
                                      {{-- <span>{{ $category->name }}</span> --}}
                                      <span
                                        class="ms-auto min-w-max whitespace-nowrap rounded-full bg-gray-50 dark:bg-gray-900 px-2.5 py-0.5 text-center text-xs/5 font-medium text-gray-600 dark:text-gray-400 ring-1 ring-inset ring-gray-200 dark:ring-gray-800">
                                        {{ $category->products_count }}
                                      </span>
                                    </a>
                                  </li>
                                  @forelse ($category->children as $subcategory)
                                    <li>
                                      <a href="{{ route('shop.subcategory', [$category->slug, $subcategory->slug]) }}" wire:navigate
                                        w.hover
                                        class="group w-full flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold link hover:bg-gray-100 dark:hover:bg-gray-800">
                                        @if ($subcategory->photo)
                                          <img src="{{ $subcategory->photo }}" alt="{{ $subcategory->name }}"
                                            class="size-6 shrink-0 rounded-md bg-gray-100 dark:bg-gray-900 object-cover">
                                        @else
                                          <svg class="size-6 shrink-0 text-primary-600" fill="none" viewBox="0 0 24 24"
                                            stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                              d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                          </svg>
                                        @endif

                                        <span>{{ $subcategory->name }}</span>
                                        <span
                                          class="ms-auto min-w-max whitespace-nowrap rounded-full bg-gray-50 dark:bg-gray-900 px-2.5 py-0.5 text-center text-xs/5 font-medium text-gray-600 dark:text-gray-400 ring-1 ring-inset ring-gray-200 dark:ring-gray-800">
                                          {{ $subcategory->child_products_count }}
                                        </span>
                                      </a>
                                    </li>
                                  @empty
                                  @endforelse
                                </ul>
                              </li>
                            </ul>
                          </div>
                        @endif
                      </li>
                    @empty
                      <li class="text-gray-500 text-sm">No categories found</li>
                    @endforelse
                  </ul>
                </li>

                {{-- <li class="mt-auto pb-2">
                  <div class="text-xs/6 font-semibold text-gray-400">{{ __('Featured') }}</div>
                  <ul role="list" class="-mx-2 mt-2 space-y-1">
                    <li>
                      <!-- Current: "bg-gray-50 dark:bg-gray-900 text-primary-600", Default:  link hover:bg-gray-100 dark:hover:bg-gray-800" -->
                      <a href="#"
                        class="group flex gap-x-3 rounded-md p-2 text-sm/6 font-semibold hover:bg-gray-100 dark:hover:bg-gray-800 link">
                        <span
                          class="flex size-6 shrink-0 items-center justify-center rounded-lg border border-gray-200 bg-white text-[0.625rem] font-medium text-gray-400 group-hover:border-primary-600 group-link">W</span>
                        <span class="truncate">Website redesign</span>
                      </a>
                    </li>
                  </ul>
                </li> --}}
              </ul>
            </nav>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Top navigation -->
  <div class="bg-gray-50 text-gray-600 border-b border-gray-100 hidden sm:block lg:hidden">
    <div class="mx-auto flex h-10 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
      @if (($shop_header_settings['general']['phone'] ?? null) && ($shop_header_settings['general']['email'] ?? null))
        <div class="flex items-center gap-x-4 text-sm">
          @if ($shop_header_settings['general']['phone'] ?? null)
            <div class="flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="currentColor" viewBox="0 0 256 256">
                <path
                  d="M222.37,158.46l-47.11-21.11-.13-.06a16,16,0,0,0-15.17,1.4,8.12,8.12,0,0,0-.75.56L134.87,160c-15.42-7.49-31.34-23.29-38.83-38.51l20.78-24.71c.2-.25.39-.5.57-.77a16,16,0,0,0,1.32-15.06l0-.12L97.54,33.64a16,16,0,0,0-16.62-9.52A56.26,56.26,0,0,0,32,80c0,79.4,64.6,144,144,144a56.26,56.26,0,0,0,55.88-48.92A16,16,0,0,0,222.37,158.46ZM176,208A128.14,128.14,0,0,1,48,80,40.2,40.2,0,0,1,82.87,40a.61.61,0,0,0,0,.12l21,47L83.2,111.86a6.13,6.13,0,0,0-.57.77,16,16,0,0,0-1,15.7c9.06,18.53,27.73,37.06,46.46,46.11a16,16,0,0,0,15.75-1.14,8.44,8.44,0,0,0,.74-.56L168.89,152l47,21.05h0s.08,0,.11,0A40.21,40.21,0,0,1,176,208Z">
                </path>
              </svg>
              {{ $shop_header_settings['general']['phone'] }}
            </div>
          @endif
          @if ($shop_header_settings['general']['email'] ?? null)
            <div class="flex items-center gap-2">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                class="size-5">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
              </svg>
              {{ $shop_header_settings['general']['email'] }}
            </div>
          @endif
        </div>
      @endif

      <div class="flex items-center gap-x-4">
        <!-- Language & Currency selector -->
        <livewire:components.language-selector />
        <livewire:components.currency-selector />

        @auth
          <!-- User Menu -->
          <div x-data="{ open: false }" x-on:click.stop.outside="open = false" class="relative z-30">

            <button @click="open = ! open"
              class="relative flex items-center whitespace-nowrap justify-center gap-2 py-1 px-2.5 rounded-full text-gray-700 text-sm hover:bg-gray-100">
              <span class="sr-only">{{ __('User Menu') }}</span>
              {{ auth()->user()->name }}
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                class="size-3">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
              </svg>
            </button>

            <div x-show="open" style="display: none"
              class="absolute z-10 end-0 min-w-40 rounded-lg shadow-xl mt-3 origin-top-right bg-white dark:bg-gray-800 text-sm p-1.5 outline-none ring-1 ring-gray-900/5">

              @if (auth()->user() && auth()->user()->employee)
                <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-900 x-focus">
                  {{ __('Dashboard') }}
                </a>
                @if (auth()->user()?->can('read-settings'))
                  <a href="{{ route('settings.index') }}" class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-900 x-focus">
                    {{ __('Admin Settings') }}
                  </a>
                @endif
              @endif

              <a href="{{ route('shop.orders') }}" wire:navigate w.hover
                class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-900 x-focus">
                {{ __('Orders') }}
              </a>
              <a href="{{ route('shop.addresses') }}" wire:navigate w.hover
                class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-900 x-focus">
                {{ __('Addresses') }}
              </a>
              <a href="{{ route('shop.billing') }}" wire:navigate w.hover
                class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-900 x-focus">
                {{ __('Billing Details') }}
              </a>

              <a href="{{ route('shop.profile') }}" wire:navigate w.hover @class([
                  'px-2 lg:py-1.5 py-2 w-full flex items-center rounded-md transition-colors focus:outline-none text-start hover:bg-gray-100 dark:hover:bg-gray-900 x-focus',
                  'bg-gray-50 dark:bg-gray-900' =>
                      Route::currentRouteName() === 'shop.profile',
              ])>
                {{ __('Profile') }}
              </a>


              <button type="button" @click="logout()"
                class="px-2 lg:py-1.5 py-2 w-full flex items-center rounded-md transition-colors focus:outline-none text-start hover:bg-gray-100 dark:hover:bg-gray-900 x-focus">
                {{ __('Sign out') }}
              </button>
            </div>
          </div>
        @else
          <!-- Medium Screen: Login & Register -->
          <div class="flex gap-x-4">
            {{-- Modal --}}
            {{-- <button type="button" @click="showLogin()" class="text-sm font-medium text-white hover:text-gray-100">
              {{ __('Sign in') }}
            </button> --}}
            <a href="{{ route('shop.signin') }}" wire:navigate w.hover class="text-sm font-medium text-gray-600 hover:text-violet-600 transition-colors">
              {{ __('Sign in') }}
            </a>
            @if ($shop_header_settings['general']['user_registration'] ?? false)
              <a href="{{ route('shop.signup') }}" wire:navigate w.hover
                class="text-sm font-medium text-gray-600 hover:text-violet-600 transition-colors">{{ __('Sign up') }}</a>
            @endif
          </div>
        @endauth
      </div>
    </div>
  </div>

  <!-- Main Header Section -->
  <div class="mx-auto flex min-h-20 max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="w-full flex justify-between items-center gap-x-3 sm:gap-x-6">
      <button type="button" @click="mobileMenu = true" class="md:hidden -ms-2 p-2">
        <span class="sr-only">Open menu</span>
        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"
          data-slot="icon">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
        </svg>
      </button>

      <div class="flex items-center gap-4">
        <div class="max-w-40 lg:max-w-56 grow-0">
          <a href="{{ route('shop.home') }}" class="text-white x-focus">
            @if (demo())
              <img src="/img/sma-icon.svg" alt="Logo" class="h-8 w-auto dark:invert">
            @else
              <img src="{{ $shop_header_settings['logo'] ?? '/img/sma-icon.svg' }}" alt="Logo" class="h-8 w-auto dark:hidden">
              <img src="{{ $shop_header_settings['logo_dark'] ?? '/img/sma-icon.svg' }}" alt="Logo"
                class="h-8 w-auto hidden dark:block">
            @endif
          </a>
        </div>
        @if (($shop_header_settings['general']['phone'] ?? null) && ($shop_header_settings['general']['email'] ?? null))
          <div class="hidden lg:flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-8" fill="currentColor" viewBox="0 0 256 256">
              <path
                d="M222.37,158.46l-47.11-21.11-.13-.06a16,16,0,0,0-15.17,1.4,8.12,8.12,0,0,0-.75.56L134.87,160c-15.42-7.49-31.34-23.29-38.83-38.51l20.78-24.71c.2-.25.39-.5.57-.77a16,16,0,0,0,1.32-15.06l0-.12L97.54,33.64a16,16,0,0,0-16.62-9.52A56.26,56.26,0,0,0,32,80c0,79.4,64.6,144,144,144a56.26,56.26,0,0,0,55.88-48.92A16,16,0,0,0,222.37,158.46ZM176,208A128.14,128.14,0,0,1,48,80,40.2,40.2,0,0,1,82.87,40a.61.61,0,0,0,0,.12l21,47L83.2,111.86a6.13,6.13,0,0,0-.57.77,16,16,0,0,0-1,15.7c9.06,18.53,27.73,37.06,46.46,46.11a16,16,0,0,0,15.75-1.14,8.44,8.44,0,0,0,.74-.56L168.89,152l47,21.05h0s.08,0,.11,0A40.21,40.21,0,0,1,176,208Z">
              </path>
            </svg>
            <div class="ms-1 flex flex-auto flex-col-reverse">
              @if ($shop_header_settings['general']['email'] ?? null)
                <h3 class="text-sm text-gray-500">{{ $shop_header_settings['general']['email'] }}</h3>
              @endif
              @if ($shop_header_settings['general']['phone'] ?? null)
                <p class="font-medium">{{ $shop_header_settings['general']['phone'] }}</p>
              @endif
            </div>
          </div>
        @endif
      </div>

      <div class="relative grow flex-1" @click.away="iconMenu = false">
        <div class="grow order-0 flex justify-end sm:hidden">
          <button type="button" @click="iconMenu = !iconMenu" class="group flex items-center gap-x-2 rounded-full p-1 text-sm link">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" fill="currentColor" viewBox="0 0 256 256">
              <path
                d="M128,96a32,32,0,1,0,32,32A32,32,0,0,0,128,96Zm0,48a16,16,0,1,1,16-16A16,16,0,0,1,128,144ZM48,96a32,32,0,1,0,32,32A32,32,0,0,0,48,96Zm0,48a16,16,0,1,1,16-16A16,16,0,0,1,48,144ZM208,96a32,32,0,1,0,32,32A32,32,0,0,0,208,96Zm0,48a16,16,0,1,1,16-16A16,16,0,0,1,208,144Z">
              </path>
            </svg>
          </button>
        </div>

        <div class="grow"
          :class="iconMenu ?
              'absolute top-full z-10 mt-4 -end-3 order-1 sm:static flex items-stretch bg-white dark:bg-gray-950 border sm:border-0 border-gray-200 dark:border-gray-700 px-6 py-2 sm:px-0 sm:py-0 sm:gap-x-4 rounded-full' :
              'hidden sm:flex sm:items-stretch sm:gap-x-4'">
          <div class="grow self-stretch flex items-center">
            {{-- <x-shop::search /> --}}
            <livewire:components.search />
          </div>

          @if (auth()->user() && auth()->user()->employee)
            <div class="hidden md:flex items-center justify-center relative shrink-0">
              <a href="{{ route('dashboard') }}" class="group flex items-center relative shrink-0 rounded-full p-2 text-white">
                <span class="absolute inset-1 rounded-full animate-ping bg-green-500 group-hover:animate-none"></span>
                <span class="absolute -inset-0.5 rounded-full bg-gradient-to-br from-green-500 to-blue-500"></span>
                <svg xmlns="http://www.w3.org/2000/svg" class="relative inline-flex size-5.5" viewBox="0 0 24 24" fill="none"
                  stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                  class="lucide lucide-gauge-icon lucide-gauge">
                  <path d="m12 14 4-4" />
                  <path d="M3.34 19a10 10 0 1 1 17.32 0" />
                </svg>
              </a>
            </div>
          @endif

          <div class="relative z-20 flex items-stretch gap-x-1 sm:gap-x-4">
            <!-- Profile dropdown -->
            @auth
              <div x-data="{ open: false }" @click.away="open = false" class="hidden lg:block lg:h-full ms-2 relative shrink-0">
                <div class="h-full flex items-center justify-center">
                  <button type="button" @click="open = ! open"
                    class="relative group flex items-center gap-x-2 rounded-full hover:-m-1 hover:p-1 text-sm link">
                    <span class="absolute -inset-1.5"></span>
                    <span class="sr-only">{{ __('user menu') }}</span>
                    <img class="size-8 rounded-full" src="{{ auth()->user()->profile_photo_url }}" alt="">
                    <span class="hidden xl:inline-flex xl:items-center">
                      {{ auth()->user()->name }}
                      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                        class="size-3 mx-1">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                      </svg>
                    </span>
                  </button>
                </div>

                <div x-show="open" style="display: none" x-transition:enter="transition ease-out duration-100"
                  x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100"
                  x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100"
                  x-transition:leave-end="transform opacity-0 scale-95"
                  class="absolute end-0 z-10 mt-2 w-40 origin-top-right rounded-md bg-white dark:bg-gray-800 py-2 text-sm/6 shadow-lg ring-1 ring-gray-900/5"
                  role="menu" tabindex="-1">
                  @if (auth()->user() && auth()->user()->employee)
                    <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-900 x-focus">
                      {{ __('Dashboard') }}
                    </a>
                    @if (auth()->user()?->can('read-settings'))
                      <a href="{{ route('settings.index') }}"
                        class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-900 x-focus">
                        {{ __('Admin Settings') }}
                      </a>
                    @endif
                  @endif
                  <a href="{{ route('shop.orders') }}" wire:navigate w.hover
                    class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-900 x-focus">
                    {{ __('Orders') }}
                  </a>
                  <a href="{{ route('shop.addresses') }}" wire:navigate w.hover
                    class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-900 x-focus">
                    {{ __('Addresses') }}
                  </a>
                  <a href="{{ route('shop.billing') }}" wire:navigate w.hover
                    class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-900 x-focus">
                    {{ __('Billing Details') }}
                  </a>
                  <a href="{{ route('shop.profile') }}" wire:navigate w.hover
                    class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-900 x-focus">
                    {{ __('Profile') }}
                  </a>
                  <div class="border dark:border-gray-700 my-1"></div>
                  <button type="button" @click="logout()"
                    class="block w-full text-start px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-900 x-focus">
                    {{ __('Sign out') }}
                  </button>
                </div>
              </div>
            @else
              <span class="hidden isolate lg:inline-flex rounded-md border border-gray-200 dark:border-gray-800">
                {{-- <button type="button" @click="showLogin()" @class([
                    'relative link inline-flex items-center rounded-s-md px-4 py-1 focus:z-10',
                    'bg-gray-50 dark:bg-gray-900 ' =>
                        $shop_header_settings['general']['user_registration'] ?? false,
                ]) class="">
                  <span>{{ __('Sign in') }}</span>
                </button> --}}
                <a href="{{ route('shop.signin') }}" wire:navigate w.hover @class([
                    'relative link inline-flex items-center rounded-s-md px-4 py-1 focus:z-10',
                    'bg-gray-100 dark:bg-gray-900 ' =>
                        $shop_header_settings['general']['user_registration'] ?? false,
                ])>
                  {{ __('Sign in') }}
                </a>
                @if ($shop_header_settings['general']['user_registration'] ?? false)
                  <a href="{{ route('shop.signup') }}" wire:navigate w.hover
                    class="relative link border-s border-gray-200 dark:border-gray-800 inline-flex items-center rounded-e-md px-4 py-1 bg-gray-100 dark:bg-gray-900 focus:z-10">
                    <span>{{ __('Sign up') }}</span>
                  </a>
                @endif
              </span>
              {{-- <button type="button" @click="showLogin()" --}}
              <a href="{{ route('shop.signin') }}" wire:navigate w.hover
                class="relative link inline-flex sm:hidden items-center text-mute rounded-md px-3 py-1 hover:bg-gray-50 dark:hover:bg-gray-950 focus:z-10">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                  <path fill-rule="evenodd"
                    d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z"
                    clip-rule="evenodd" />
                </svg>
              </a>
            @endauth

            <x-shop::shared.theme-selector />

            <div class="flex me-2 sm:me-0 sm:hidden lg:flex items-center gap-x-1 sm:gap-x-4">
              <!-- Language & Currency selector -->
              <livewire:components.language-selector />
              <livewire:components.currency-selector />
            </div>

            <div class="flex items-center">
              <a href="{{ route('shop.wishlist') }}" wire:navigate w.hover
                class="hidden link sm:inline relative shrink-0 rounded-full p-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <span class="absolute -inset-1.5"></span>
                <span class="sr-only">{{ __('Wishlist') }}</span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                  class="size-6">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                </svg>
              </a>
            </div>

            <div class="flex items-center justify-center">
              <!-- Trigger -->
              <button type="button" @click="cartDrawer = true"
                class="relative link shrink-0 rounded-full p-1 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <span class="absolute -inset-1.5"></span>
                <span class="sr-only">{{ __('Cart') }}</span>
                <div id="cart-items-count" class="absolute -top-1.5 -end-1.5 px-1 bg-primary-500 text-white rounded-full text-xs">
                  {{ cart_items_quantity() ?: '' }}</div>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                  class="size-6">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  {{-- Add Login Modal --}}
  {{-- <div x-data="{ open: false }" @close-login.window="open = false" @open-login.window="open = true" x-trap="open">
    <x-shop::alpine.modal maxWidth='sm:max-w-3xl' backdrop='true' name="login">
      <x-shop::login />
    </x-shop::alpine.modal>
  </div> --}}


  <!-- Navigation -->
  <div class="relative z-10 border-t border-gray-100 bg-white hidden md:block">
    <nav aria-label="Top">
      <!-- Menus navigation -->
      <div>
        <div class="relative isolate z-50">
          <div class="">
            <div class="h-12 mx-auto max-w-7xl px-6 lg:px-8 flex items-stretch gap-x-6">
              <button type="button" @click="categoriesMenu = !categoriesMenu"
                class="link x-focus inline-flex items-center gap-x-2 text-sm/6 font-semibold pe-4 border-e border-gray-200 dark:border-gray-800">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                  class="size-5">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                </svg>
                <span>
                  <span class="hidden lg:inline">{{ __('Shop by') }}</span> {{ __('Category') }}
                </span>
              </button>

              <a href="{{ route('shop.home') }}" @class([
                  'link x-focus hidden lg:inline-flex items-center gap-x-1 text-sm/6 font-semibold',
                  'active-link' => Route::is('shop.home'),
              ])>
                {{ __('Home') }}
              </a>

              <a href="{{ route('shop.products') }}" wire:navigate w.hover @class([
                  'link x-focus inline-flex items-center gap-x-1 text-sm/6 font-semibold',
                  'active-link' => Route::is('shop.products'),
              ])>
                {{ __('Products') }}
              </a>

              <button type="button" @click="brandsMenu = !brandsMenu" :class="brandsMenu ? 'active-link' : ''"
                class="link x-focus inline-flex items-center gap-x-1 text-sm/6 font-semibold">
                {{ __('Brands') }}
                <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                  <path fill-rule="evenodd"
                    d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z"
                    clip-rule="evenodd" />
                </svg>
              </button>

              {{-- <div x-data="{ open: false }" @click.away="open = false" class="relative flex self-stretch">
                <button type="button" @click="open = !open" :class="open ? 'active-link' : ''"
                  class="link x-focus self-stretch inline-flex items-center gap-x-1 text-sm/6 font-semibold" aria-expanded="false">
                  <span>{{ __('Services') }}</span>
                  <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                    <path fill-rule="evenodd"
                      d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z"
                      clip-rule="evenodd" />
                  </svg>
                </button>

                <!-- Solutions Menu -->
                <div x-show="open" style="display: none"
                  class="absolute top-full start-1/2 z-10 mt-1 flex w-screen max-w-max ltr:-translate-x-1/2 rtl:translate-x-1/2 px-4"
                  x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1"
                  x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-out duration-200"
                  x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-1">
                  <div
                    class="w-screen max-w-md flex-auto overflow-hidden rounded-md bg-white dark:bg-gray-800 text-sm/6 shadow-lg ring-1 ring-gray-900/5 lg:max-w-3xl">
                    <div class="grid grid-cols-1 gap-x-6 gap-y-1 p-4 lg:grid-cols-2">
                      <div class="group relative flex gap-x-6 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-950">
                        <div
                          class="mt-1 flex size-11 flex-none items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-900 group-hover:bg-white">
                          <svg class="size-6 text-gray-600 dark:text-gray-400 group-link" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z" />
                          </svg>
                        </div>
                        <div>
                          <a href="#" class="font-semibold">
                            Analytics
                            <span class="absolute inset-0"></span>
                          </a>
                          <p class="mt-1 text-gray-600 dark:text-gray-400">Get a better understanding of your traffic</p>
                        </div>
                      </div>
                      <div class="group relative flex gap-x-6 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-950">
                        <div
                          class="mt-1 flex size-11 flex-none items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-900 group-hover:bg-white">
                          <svg class="size-6 text-gray-600 dark:text-gray-400 group-link" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                            <path stroke-linecap="round" stroke-linejoin="round"
                              d="M13.5 16.875h3.375m0 0h3.375m-3.375 0V13.5m0 3.375v3.375M6 10.5h2.25a2.25 2.25 0 0 0 2.25-2.25V6a2.25 2.25 0 0 0-2.25-2.25H6A2.25 2.25 0 0 0 3.75 6v2.25A2.25 2.25 0 0 0 6 10.5Zm0 9.75h2.25A2.25 2.25 0 0 0 10.5 18v-2.25a2.25 2.25 0 0 0-2.25-2.25H6a2.25 2.25 0 0 0-2.25 2.25V18A2.25 2.25 0 0 0 6 20.25Zm9.75-9.75H18a2.25 2.25 0 0 0 2.25-2.25V6A2.25 2.25 0 0 0 18 3.75h-2.25A2.25 2.25 0 0 0 13.5 6v2.25a2.25 2.25 0 0 0 2.25 2.25Z" />
                          </svg>
                        </div>
                        <div>
                          <a href="#" class="font-semibold">
                            Integrations
                            <span class="absolute inset-0"></span>
                          </a>
                          <p class="mt-1 text-gray-600 dark:text-gray-400">Connect with third-party tools and find out expectations</p>
                        </div>
                      </div>
                      <div class="group relative flex gap-x-6 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-950">
                        <div
                          class="mt-1 flex size-11 flex-none items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-900 group-hover:bg-white">
                          <svg class="size-6 text-gray-600 dark:text-gray-400 group-link" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                            <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15.042 21.672 13.684 16.6m0 0-2.51 2.225.569-9.47 5.227 7.917-3.286-.672ZM12 2.25V4.5m5.834.166-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243-1.59-1.59" />
                          </svg>
                        </div>
                        <div>
                          <a href="#" class="font-semibold">
                            Engagement
                            <span class="absolute inset-0"></span>
                          </a>
                          <p class="mt-1 text-gray-600 dark:text-gray-400">Speak directly to your customers with our engagement tool</p>
                        </div>
                      </div>
                      <div class="group relative flex gap-x-6 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-950">
                        <div
                          class="mt-1 flex size-11 flex-none items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-900 group-hover:bg-white">
                          <svg class="size-6 text-gray-600 dark:text-gray-400 group-link" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                            <path stroke-linecap="round" stroke-linejoin="round"
                              d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                          </svg>
                        </div>
                        <div>
                          <a href="#" class="font-semibold">
                            Automations
                            <span class="absolute inset-0"></span>
                          </a>
                          <p class="mt-1 text-gray-600 dark:text-gray-400">Build strategic funnels that will convert</p>
                        </div>
                      </div>
                      <div class="group relative flex gap-x-6 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-950">
                        <div
                          class="mt-1 flex size-11 flex-none items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-900 group-hover:bg-white">
                          <svg class="size-6 text-gray-600 dark:text-gray-400 group-link" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                            <path stroke-linecap="round" stroke-linejoin="round"
                              d="M7.864 4.243A7.5 7.5 0 0 1 19.5 10.5c0 2.92-.556 5.709-1.568 8.268M5.742 6.364A7.465 7.465 0 0 0 4.5 10.5a7.464 7.464 0 0 1-1.15 3.993m1.989 3.559A11.209 11.209 0 0 0 8.25 10.5a3.75 3.75 0 1 1 7.5 0c0 .527-.021 1.049-.064 1.565M12 10.5a14.94 14.94 0 0 1-3.6 9.75m6.633-4.596a18.666 18.666 0 0 1-2.485 5.33" />
                          </svg>
                        </div>
                        <div>
                          <a href="#" class="font-semibold">
                            Security
                            <span class="absolute inset-0"></span>
                          </a>
                          <p class="mt-1 text-gray-600 dark:text-gray-400">Your customers&#039; data will be safe and secure</p>
                        </div>
                      </div>
                      <div class="group relative flex gap-x-6 rounded-lg p-4 hover:bg-gray-50 dark:hover:bg-gray-950">
                        <div
                          class="mt-1 flex size-11 flex-none items-center justify-center rounded-lg bg-gray-50 dark:bg-gray-900 group-hover:bg-white">
                          <svg class="size-6 text-gray-600 dark:text-gray-400 group-link" fill="none" viewBox="0 0 24 24"
                            stroke-width="1.5" stroke="currentColor" aria-hidden="true" data-slot="icon">
                            <path stroke-linecap="round" stroke-linejoin="round"
                              d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25M9 16.5v.75m3-3v3M15 12v5.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                          </svg>
                        </div>
                        <div>
                          <a href="#" class="font-semibold">
                            Reports
                            <span class="absolute inset-0"></span>
                          </a>
                          <p class="mt-1 text-gray-600 dark:text-gray-400">Edit, manage and create newly informed decisions</p>
                        </div>
                      </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 px-8 py-6">
                      <div class="flex items-center gap-x-3">
                        <h3 class="text-sm/6 font-semibold">Enterprise</h3>
                        <p class="rounded-full bg-primary-600/10 px-2.5 py-1.5 text-xs font-semibold text-primary-600">New</p>
                      </div>
                      <p class="mt-2 text-sm/6 text-gray-600 dark:text-gray-400">Empower your entire team with even more advanced tools.
                      </p>
                    </div>
                  </div>
                </div>
              </div> --}}

              @if ($shop_header_settings['page_menus'] ?? null)
                <div x-data="{ open: false }" @click.away="open = false" class="relative flex self-stretch">
                  <button type="button" @click="open = !open" :class="open ? 'active-link' : ''"
                    class="link x-focus self-stretch inline-flex items-center gap-x-1 text-sm/6 font-semibold" aria-expanded="false">
                    <span>{{ __('Pages') }}</span>
                    <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                      <path fill-rule="evenodd"
                        d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z"
                        clip-rule="evenodd" />
                    </svg>
                  </button>

                  {{-- Pages Menus --}}
                  <div x-show="open" style="display: none"
                    class="absolute top-full start-1/2 z-10 mt-1 flex w-screen max-w-min ltr:-translate-x-1/2 rtl:translate-x-1/2 px-4"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-out duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-1">
                    <div
                      class="w-48 shrink rounded-md bg-white dark:bg-gray-800 p-2 text-sm/6 font-semibold shadow-lg ring-1 ring-gray-900/5 flex flex-col gap-1">
                      @foreach ($shop_header_settings['page_menus'] as $menu)
                        <a href="{{ $menu['link'] }}"
                          class="block px-2 py-1.5 link hover:ps-4 transition-all hover:bg-gray-50 dark:hover:bg-gray-950 rounded-md">{{ $menu['label'] }}</a>
                      @endforeach
                    </div>
                  </div>
                </div>
              @endif

              @can('read-settings')
                <div x-data="{ open: false }" @click.away="open = false" class="relative flex self-stretch">
                  <button type="button" @click="open = !open" :class="open ? 'active-link' : ''"
                    class="link x-focus self-stretch inline-flex items-center gap-x-1 text-sm/6 font-semibold" aria-expanded="false">
                    <span>{{ __('Manage') }}</span>
                    <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                      <path fill-rule="evenodd"
                        d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z"
                        clip-rule="evenodd" />
                    </svg>
                  </button>

                  {{-- Admin Menus --}}
                  <div x-show="open" style="display: none"
                    class="absolute top-full start-1/2 z-10 mt-1 flex w-screen max-w-min ltr:-translate-x-1/2 rtl:translate-x-1/2 px-4"
                    x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-out duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-1">
                    <div
                      class="w-48 shrink rounded-md bg-white dark:bg-gray-800 p-2 text-sm/6 font-semibold shadow-lg ring-1 ring-gray-900/5 flex flex-col gap-1">
                      {{-- <a href="{{ route('shop.settings') }}" wire:navigate w.hover
                        class="block px-2 py-1.5 link hover:ps-4 transition-all hover:bg-gray-50 dark:hover:bg-gray-950 rounded-md">{{ __('Settings') }}</a> --}}
                      <a href="{{ route('shop.pages') }}" wire:navigate w.hover
                        class="block px-2 py-1.5 link hover:ps-4 transition-all hover:bg-gray-50 dark:hover:bg-gray-950 rounded-md">{{ __('Pages') }}</a>
                      <a href="{{ route('shop.coupons') }}" wire:navigate w.hover
                        class="block px-2 py-1.5 link hover:ps-4 transition-all hover:bg-gray-50 dark:hover:bg-gray-950 rounded-md">{{ __('Coupons') }}</a>
                      <a href="{{ route('shop.currencies') }}" wire:navigate w.hover
                        class="block px-2 py-1.5 link hover:ps-4 transition-all hover:bg-gray-50 dark:hover:bg-gray-950 rounded-md">{{ __('Currencies') }}</a>
                      <a href="{{ route('shop.shipping_methods') }}" wire:navigate w.hover
                        class="block px-2 py-1.5 link hover:ps-4 transition-all hover:bg-gray-50 dark:hover:bg-gray-950 rounded-md">{{ __('Shipping Methods') }}</a>
                      <a href="{{ route('shop.custom_code') }}" wire:navigate w.hover
                        class="block px-2 py-1.5 link hover:ps-4 transition-all hover:bg-gray-50 dark:hover:bg-gray-950 rounded-md">{{ __('Custom CSS & JS') }}</a>
                      <div class="my-1 -mx-2 border-b border-gray-200 dark:border-gray-700"></div>
                      <a href="{{ route('shop.settings') }}" wire:navigate w.hover
                        class="block px-2 py-1.5 link hover:ps-4 transition-all hover:bg-gray-50 dark:hover:bg-gray-950 rounded-md">{{ __('Shop Settings') }}</a>
                    </div>
                  </div>
                </div>
              @endcan

              <a href="{{ route('shop.products', ['filters' => ['on_promo' => true]]) }}" wire:navigate w.hover
                @class([
                    'link x-focus ms-auto inline-flex items-center gap-x-1 text-sm/6 font-semibold',
                    'active-link' =>
                        Route::is('shop.products') && request()->has('filters.on_promo'),
                ])>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
                  class="size-5">
                  <path stroke-linecap="round" stroke-linejoin="round"
                    d="m8.99 14.993 6-6m6 3.001c0 1.268-.63 2.39-1.593 3.069a3.746 3.746 0 0 1-1.043 3.296 3.745 3.745 0 0 1-3.296 1.043 3.745 3.745 0 0 1-3.068 1.593c-1.268 0-2.39-.63-3.068-1.593a3.745 3.745 0 0 1-3.296-1.043 3.746 3.746 0 0 1-1.043-3.297 3.746 3.746 0 0 1-1.593-3.068c0-1.268.63-2.39 1.593-3.068a3.746 3.746 0 0 1 1.043-3.297 3.745 3.745 0 0 1 3.296-1.042 3.745 3.745 0 0 1 3.068-1.594c1.268 0 2.39.63 3.068 1.593a3.745 3.745 0 0 1 3.296 1.043 3.746 3.746 0 0 1 1.043 3.297 3.746 3.746 0 0 1 1.593 3.068ZM9.74 9.743h.008v.007H9.74v-.007Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm4.125 4.5h.008v.008h-.008v-.008Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
                {{ __('Promotions') }}
              </a>

              <button type="button" @click="recentMenu = true"
                class="link x-focus hidden lg:inline-flex items-center gap-x-1 text-sm/6 font-semibold"
                :class="recentMenu ? 'active-link' : ''">
                {{ __('Recently Viewed') }}
                <svg class="size-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" data-slot="icon">
                  <path fill-rule="evenodd"
                    d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z"
                    clip-rule="evenodd" />
                </svg>
              </button>
            </div>
          </div>

          <div x-show="brandsMenu" style="display: none" @click.away="brandsMenu = false"
            class="absolute inset-x-0 top-0 -z-10 bg-white pt-12 shadow-xl ring-1 ring-gray-900/5"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-out duration-200"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1">
            <div class="border-t border-gray-200 dark:border-gray-800">
              <div @class([
                  'mx-auto grid max-w-7xl grid-cols-1 gap-x-8 gap-y-10 px-6 py-10 lg:px-8',
                  'md:grid-cols-4' => ($shop_header_settings['brands_article'] ?? false) == 1,
                  'md:grid-cols-3' => ($shop_header_settings['brands_article'] ?? false) == 0,
              ])>
                <div class="col-span-3 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-x-6 sm:gap-x-8">
                  @foreach ($brandsMenu->chunk(10) as $brands)
                    <div>
                      {{-- <h3 class="text-sm/6 font-medium text-gray-500">Engagement</h3> --}}
                      <div class="-my-2">
                        <div class="flow-root">
                          @foreach ($brands as $brand)
                            <a href="{{ route('shop.brand', $brand->slug) }}" wire:navigate w.hover
                              class="group flex gap-x-4 py-2 text-sm/6 font-semibold rounded-md hover:bg-gray-100 dark:hover:bg-gray-900 hover:-mx-4 hover:px-4 link">
                              <svg class="size-6 flex-none text-gray-400 link" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" aria-hidden="true" data-slot="icon">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                  d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                              </svg>
                              {{ $brand->name }}
                            </a>
                          @endforeach
                        </div>
                      </div>
                    </div>
                  @endforeach
                  {{-- <div>
                    <div class="mt-6 flow-root">
                      <div class="-my-2">
                        <a href="#" class="flex gap-x-4 py-2 text-sm/6 font-semibold">
                          <svg class="size-6 flex-none text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" aria-hidden="true" data-slot="icon">
                            <path stroke-linecap="round" stroke-linejoin="round"
                              d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                          </svg>
                          Community
                        </a>
                      </div>
                    </div>
                  </div> --}}
                </div>
                @if (($shop_header_settings['brands_article'] ?? false) == 1 && $featured)
                  <div class="grid grid-cols-1 gap-10 sm:gap-8">
                    <h3 class="sr-only">{{ __('Featured Product') }}</h3>
                    <x-shop::product :product="$featured" />
                  </div>
                @endif
              </div>
            </div>
          </div>

          {{-- Recent Products --}}
          <div x-show="recentMenu" style="display: none" @click.away="recentMenu = false"
            class="absolute inset-x-0 top-0 -z-10 bg-white pt-12 shadow-xl ring-1 ring-gray-900/5"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-out duration-200"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-1">
            <div class="border-t border-gray-200 dark:border-gray-800">
              <div class="mx-auto grid max-w-7xl grid-cols-3 gap-x-8 px-6 py-10 lg:grid-cols-4 lg:px-8">
                @forelse ($recent_views as $recent_view)
                  @if ($recent_view->product)
                    <div class="[&:last-child>div]:hidden lg:[&:last-child>div]:block">
                      <x-shop::product :product="$recent_view->product" :key="$recent_view->id . $recent_view->product->id" />
                    </div>
                  @endif
                @empty
                  {{ __('No recently viewed products') }}
                @endforelse
              </div>
            </div>
          </div>
        </div>
      </div>
    </nav>
  </div>
</header>

@pushOnce('scripts')
  <script>
    var themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
    var themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');
    console.log(themeToggleDarkIcon, themeToggleLightIcon);

    // Change the icons inside the button based on previous settings
    if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia(
        '(prefers-color-scheme: dark)').matches)) {
      themeToggleLightIcon.classList.remove('hidden');
    } else {
      themeToggleDarkIcon.classList.remove('hidden');
    }

    var themeToggleBtn = document.getElementById('theme-toggle');

    themeToggleBtn.addEventListener('click', function() {

      // toggle icons inside button
      themeToggleDarkIcon.classList.toggle('hidden');
      themeToggleLightIcon.classList.toggle('hidden');

      // if set via local storage previously
      if (localStorage.getItem('theme')) {
        if (localStorage.getItem('theme') === 'light') {
          document.documentElement.classList.add('dark');
          localStorage.setItem('theme', 'dark');
        } else {
          document.documentElement.classList.remove('dark');
          localStorage.setItem('theme', 'light');
        }

        // if NOT set via local storage previously
      } else {
        if (document.documentElement.classList.contains('dark')) {
          document.documentElement.classList.remove('dark');
          localStorage.setItem('theme', 'light');
        } else {
          document.documentElement.classList.add('dark');
          localStorage.setItem('theme', 'dark');
        }
      }

    });
  </script>
@endPushOnce
