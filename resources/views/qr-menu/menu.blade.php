@extends('qr-menu.layout')

@section('title', ($menuSettings['name'] ?? 'Menu') . ' - ' . $table->hall->name . ' / ' . $table->name)

@section('content')
  <div x-data="qrMenu()" x-cloak
    class="mx-auto min-h-full flex max-w-5xl flex-col lg:border lg:border-gray-200 dark:lg:border-gray-700 rounded-lg">
    {{-- Header --}}
    <header
      class="sticky top-0 z-20 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 h-16 shrink-0 lg:rounded-t-lg">
      <div class="flex items-center justify-between h-16">
        <div>
          @if (($menuSettings['logo'] ?? null) && ($menuSettings['logo_dark'] ?? null))
            <img src="{{ $menuSettings['logo'] }}" alt="{{ $menuSettings['name'] }}" class="h-8 max-w-48 dark:hidden">
            <img src="{{ $menuSettings['logo_dark'] ?? $menuSettings['logo'] }}" alt="{{ $menuSettings['name'] ?? 'Menu' }}"
              class="h-8 max-w-48 hidden dark:block">
          @else
            <h1 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ $menuSettings['name'] ?? 'Menu' }}</h1>
          @endif
        </div>
        <div class="text-right text-sm text-gray-500">
          <div class="font-bold text-gray-700 dark:text-gray-200">{{ $table->hall->name }}</div>
          <div class="dark:text-gray-400">{{ $table->name }} &middot; {{ $table->seats }} {{ __('seats') }}</div>
        </div>
      </div>
    </header>

    {{-- Categories --}}
    <nav class="sticky top-16 z-10 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
      <div class="flex gap-1 overflow-x-auto overscroll-none px-4 py-2.5 scroll-pb-3">
        @foreach ($categories as $category)
          <button type="button" @click="loadCategory({{ $category->id }})"
            :class="activeCategory === {{ $category->id }} ?
                'bg-primary-600 text-white' :
                'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700'"
            class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-2 py-1.5 text-sm font-medium transition">
            @if ($category->photo)
              <img src="{{ $category->photo }}" alt="" class="h-5 w-5 rounded-full object-cover">
            @endif
            {{ $category->name }}
          </button>
          @foreach ($category->children as $child)
            @if ($child->child_products_count > 0)
              <button type="button" @click="loadCategory({{ $child->id }})"
                :class="activeCategory === {{ $child->id }} ?
                    'bg-primary-600 text-white' :
                    'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-700'"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-4 py-1.5 text-sm font-medium transition">
                {{ $child->name }}
              </button>
            @endif
          @endforeach
        @endforeach
      </div>
    </nav>

    {{-- Products --}}
    <main class="flex-1 px-4 py-4">
      {{-- Loading --}}
      <div x-show="loading" class="flex items-center justify-center py-12">
        <svg class="h-8 w-8 animate-spin text-primary-600" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
        </svg>
      </div>

      {{-- Product Grid --}}
      <div x-show="!loading" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
        <template x-for="product in products" :key="product.id">
          <div
            class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-950 transition hover:shadow-md">
            <div class="aspect-square bg-gray-100 dark:bg-gray-800">
              <img x-show="product.photo" :src="product.photo" :alt="product.name" class="h-full w-full object-cover" loading="lazy">
              <div x-show="!product.photo" class="flex h-full w-full items-center justify-center text-3xl text-gray-300 dark:text-gray-600">
                <svg class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
              </div>
            </div>
            <div class="p-3">
              <h3 class="text-sm font-medium leading-tight text-gray-900 dark:text-gray-100" x-text="product.name"></h3>
              {{-- <template x-if="product.has_variants">
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Multiple options') }}</div>
              </template> --}}
              <div class="mt-1 flex items-center justify-between">
                <span class="text-sm font-bold text-primary-600" x-text="currency + ' ' + parseFloat(product.unit_price).toFixed(2)"></span>
                <template x-if="product.has_variants">
                  <button @click="openVariantModal(product)" type="button"
                    class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-600 text-white transition hover:bg-primary-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                  </button>
                </template>
                <template x-if="!product.has_variants">
                  <template x-if="!inCart(product.id)">
                    <button @click="addToCart(product)" type="button"
                      class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-600 text-white transition hover:bg-primary-700">
                      <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                      </svg>
                    </button>
                  </template>
                </template>
                <template x-if="!product.has_variants && inCart(product.id)">
                  <div class="flex items-center gap-1">
                    <button @click="decrementCart(product.id + '-p')" type="button"
                      class="flex h-7 w-7 items-center justify-center rounded-full bg-gray-200 dark:bg-gray-800 text-gray-700 dark:text-gray-200 transition hover:bg-gray-300 dark:hover:bg-gray-700">
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                      </svg>
                    </button>
                    <span class="w-6 text-center text-sm font-bold text-gray-700 dark:text-gray-200" x-text="getCartQty(product.id)"></span>
                    <button @click="addToCart(product)" type="button"
                      class="flex h-7 w-7 items-center justify-center rounded-full bg-primary-600 text-white transition hover:bg-primary-700">
                      <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                      </svg>
                    </button>
                  </div>
                </template>
              </div>
            </div>
          </div>
        </template>
      </div>

      {{-- Empty State --}}
      <div x-show="!loading && products.length === 0" class="py-12 text-center text-gray-400">
        <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
        </svg>
        <p class="mt-2">{{ __('No products in this category') }}</p>
      </div>
    </main>

    {{-- Cart Bottom Bar --}}
    <div x-show="cart.length > 0" x-transition
      class="sticky bottom-0 z-20 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 rounded-b-lg p-4 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)]">
      <button @click="showCart = true" type="button"
        class="flex w-full items-center justify-between rounded-xl bg-primary-600 px-5 py-3.5 text-white shadow-lg transition hover:bg-primary-700">
        <div class="flex items-center gap-3">
          <div class="flex h-7 w-7 items-center justify-center rounded-full bg-white/20 text-sm font-bold" x-text="totalItems"></div>
          <span class="font-medium">{{ __('View Cart') }}</span>
        </div>
        <span class="font-bold" x-text="currency + ' ' + cartTotal.toFixed(2)"></span>
      </button>
    </div>

    {{-- Existing Order Banner --}}
    @if ($existingOrder)
      <div x-show="cart.length === 0"
        class="sticky bottom-0 z-20 border-t border-gray-200 dark:border-gray-700 bg-amber-50 dark:bg-amber-800 p-4 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)]">
        <p class="mb-2 text-center text-sm font-medium text-amber-800">
          {{ __('Adding items to order') }} <span class="font-bold">#{{ $existingOrder->number }}</span>
        </p>
        <p class="text-center text-xs text-amber-600">
          {{ __('Current items') }}: {{ $existingOrder->total_items }} &middot;
          {{ __('Total') }}: {{ get_settings('default_currency') ?? '$' }} {{ number_format($existingOrder->total, 2) }}
        </p>
      </div>
    @endif

    {{-- Variant Selection Modal --}}
    <div x-show="showVariantModal" x-transition.opacity class="fixed inset-0 z-30 bg-black/50 backdrop-blur-xs"
      @click="showVariantModal = false"></div>
    <div x-show="showVariantModal" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-full"
      x-transition:enter-end="translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0"
      x-transition:leave-end="translate-y-full"
      class="fixed inset-x-0 bottom-0 z-40 mx-auto max-h-[90vh] max-w-lg overflow-y-auto rounded-t-2xl bg-white shadow-2xl">
      <div
        class="sticky top-0 flex items-center justify-between border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 px-5 py-4">
        <h2 class="text-lg font-bold text-gray-900 dark:text-gray-200">{{ __('Select Variant') }}</h2>
        <button @click="showVariantModal = false" type="button"
          class="text-gray-400 dark:text-gray-200 hover:text-gray-600 dark:hover:text-gray-400">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div class="px-5 py-4 bg-white dark:bg-gray-800" x-show="variantProduct">
        {{-- Product Header --}}
        <div class="mb-4 flex items-center gap-3">
          <div class="h-16 w-16 shrink-0 overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800">
            <img x-show="variantProduct?.photo" :src="variantProduct?.photo" :alt="variantProduct?.name" class="h-full w-full object-cover">
            <div x-show="!variantProduct?.photo" class="flex h-full w-full items-center justify-center text-gray-300">
              <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
          </div>
          <div>
            <h3 class="font-medium text-gray-900 dark:text-gray-200" x-text="variantProduct?.name"></h3>
            <p class="text-sm text-gray-500 dark:text-gray-300" x-text="'Please select the variant to add item to order list.'"></p>
          </div>
        </div>

        {{-- Variant Dimensions --}}
        <template x-for="(dimension, di) in (variantProduct?.variants || [])" :key="di">
          <div class="mb-4">
            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-200" x-text="dimension.name"></label>
            <div class="flex flex-wrap gap-2">
              <template x-for="(option, oi) in dimension.options" :key="oi">
                <button type="button" @click="selectVariantOption(dimension.name, option)"
                  :class="selectedVariantOptions[dimension.name] === option ?
                      'border-primary-600 bg-primary-600 text-primary-100 ring-1 ring-primary-600' :
                      'border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-950 dark:text-gray-300 dark:hover:bg-gray-800'"
                  class="rounded-lg border px-4 py-2 text-sm font-medium transition" x-text="option">
                </button>
              </template>
            </div>
          </div>
        </template>

        {{-- Matched Variation Info --}}
        <div x-show="matchedVariation" class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-green-800" x-text="matchedVariationLabel"></p>
              <p class="text-xs text-green-600" x-text="'SKU: ' + (matchedVariation?.sku || '')"></p>
            </div>
            <span class="text-lg font-bold text-green-700"
              x-text="currency + ' ' + parseFloat(matchedVariation?.unit_price || 0).toFixed(2)"></span>
          </div>
        </div>

        {{-- No Match Warning --}}
        <div x-show="!matchedVariation && allDimensionsSelected" class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3">
          <p class="text-sm text-red-600">{{ __('This combination is not available.') }}</p>
        </div>

        {{-- Add Button --}}
        <button type="button" @click="addVariantToCart" :disabled="!matchedVariation"
          class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-5 py-3 font-semibold text-white shadow-lg transition hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-50">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          {{ __('Add to Cart') }}
        </button>
      </div>
    </div>

    {{-- Cart Modal --}}
    <div x-show="showCart" x-transition.opacity class="fixed inset-0 z-30 bg-black/50 backdrop-blur-xs" @click="showCart = false">
    </div>
    <div x-show="showCart" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-full"
      x-transition:enter-end="translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0"
      x-transition:leave-end="translate-y-full"
      class="fixed inset-x-0 bottom-0 z-40 mx-auto max-h-[85vh] max-w-lg overflow-y-auto rounded-t-2xl bg-white dark:bg-gray-800 shadow-2xl">
      <div
        class="sticky top-0 flex items-center justify-between border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-5 py-4">
        <h2 class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ __('Your Order') }}</h2>
        <button @click="showCart = false" type="button" class="text-gray-400 hover:text-gray-600">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <form method="POST" action="{{ route('qr-menu.store', $table->qr_token) }}" id="order-cart-form">
        @csrf
        @if ($existingOrder)
          <input type="hidden" name="order_number" value="{{ $existingOrder->number }}">
        @endif
        <div class="divide-y divide-gray-100 dark:divide-gray-700 px-5">
          <template x-for="(item, index) in cart" :key="item.cartKey">
            <div class="flex items-center gap-3 py-3">
              <input type="hidden" :name="'items[' + index + '][id]'" :value="item.id">
              <input type="hidden" :name="'items[' + index + '][name]'" :value="item.name">
              <input type="hidden" :name="'items[' + index + '][price]'" :value="item.price">
              <input type="hidden" :name="'items[' + index + '][quantity]'" :value="item.quantity">
              <template x-if="item.variation_id">
                <input type="hidden" :name="'items[' + index + '][variation_id]'" :value="item.variation_id">
              </template>
              <template x-if="item.variation_meta">
                <input type="hidden" :name="'items[' + index + '][variation_meta]'" :value="JSON.stringify(item.variation_meta)">
              </template>

              <div class="flex-1">
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100" x-text="item.name"></p>
                <template x-if="item.variation_meta">
                  <p class="text-xs text-primary-600" x-text="formatMeta(item.variation_meta)"></p>
                </template>
                <p class="text-xs text-gray-500 dark:text-gray-400"
                  x-text="currency + ' ' + unitPrice(item).toFixed(2) + ' × ' + item.quantity">
                </p>
              </div>
              <div class="flex items-center gap-2">
                <button @click="decrementCart(item.cartKey)" type="button"
                  class="flex h-7 w-7 items-center justify-center rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                  </svg>
                </button>
                <span class="w-6 text-center text-sm font-bold text-gray-700 dark:text-gray-300" x-text="item.quantity"></span>
                <button @click="incrementCart(item.cartKey)" type="button"
                  class="flex h-7 w-7 items-center justify-center rounded-full bg-primary-600 text-white hover:bg-primary-700">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                  </svg>
                </button>
              </div>
              <span class="min-w-[60px] text-right text-sm font-bold text-gray-900 dark:text-gray-100"
                x-text="currency + ' ' + (unitPrice(item) * item.quantity).toFixed(2)">
              </span>
            </div>
          </template>
        </div>

        {{-- Customer Name --}}
        <div class="border-t border-gray-200 dark:border-gray-700 px-5 pt-4">
          <label for="customer_name" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
            {{ __('Your Name') }} <span class="text-gray-400">({{ __('optional') }})</span>
          </label>
          <input id="customer_name" name="customer_name" type="text" x-model="customerName" placeholder="{{ __('Enter your name') }}"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500">
        </div>

        {{-- Notes --}}
        <div class="px-5 pt-3">
          <label for="notes" class="mb-1 block text-sm font-medium text-gray-700 dark:text-gray-200">
            {{ __('Notes') }} <span class="text-gray-400">({{ __('optional') }})</span>
          </label>
          <textarea id="notes" name="notes" rows="2" x-model="notes" placeholder="{{ __('Special requests, allergies, etc.') }}"
            class="w-full rounded-lg border border-gray-300 dark:border-gray-600 px-3 py-2 text-sm focus:border-primary-500 focus:ring-primary-500"></textarea>
        </div>

        {{-- Total & Submit --}}
        <div class="border-t border-gray-200 dark:border-gray-700 px-5 py-4 mt-3">
          <div class="mb-3 flex items-center justify-between text-gray-900 dark:text-gray-100">
            <span class="text-base font-bold">{{ __('Total') }}</span>
            <span class="text-lg font-bold" x-text="currency + ' ' + cartTotal.toFixed(2)"></span>
          </div>
          <button type="button" :disabled="submitting"
            @click="() => {
            submitting = true;
            {{-- clearCart(); --}}
            document.getElementById('order-cart-form').submit();
        }"
            class="flex w-full items-center justify-center gap-2 rounded-xl bg-primary-600 px-5 py-3.5 text-base font-semibold text-white shadow-lg transition hover:bg-primary-700 disabled:opacity-50">
            <svg x-show="submitting" class="h-5 w-5 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
              </circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
              </path>
            </svg>
            {{ $existingOrder ? __('Add Items to Order') : __('Place Order') }}
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    function qrMenu() {
      const storageKey = 'qr_cart_{{ $table->qr_token }}';

      return {
        cart: [],
        products: [],
        loading: false,
        showCart: false,
        submitting: false,
        customerName: '',
        notes: '',
        activeCategory: {{ $categories->first()?->id ?? 'null' }},
        currency: '{{ $menuSettings['currency'] ?? '$' }}',
        menuUrl: '{{ url('/menu/' . $table->qr_token) }}',
        existingOrderNumber: '{{ $existingOrder?->number ?? '' }}',

        // Variant selection state
        showVariantModal: false,
        variantProduct: null,
        selectedVariantOptions: {},
        matchedVariation: null,

        init() {
          this.loadCart();
          if (this.activeCategory) {
            this.loadCategory(this.activeCategory);
          }
          this.$watch('cart', () => this.saveCart(), {
            deep: true
          });
          this.$watch('customerName', () => this.saveCart());
          this.$watch('notes', () => this.saveCart());
        },

        saveCart() {
          try {
            localStorage.setItem(storageKey, JSON.stringify({
              cart: this.cart,
              customerName: this.customerName,
              notes: this.notes,
            }));
          } catch (e) {}
        },

        loadCart() {
          try {
            const saved = localStorage.getItem(storageKey);
            if (saved) {
              const data = JSON.parse(saved);
              this.cart = data.cart || [];
              this.customerName = data.customerName || '';
              this.notes = data.notes || '';
            }
          } catch (e) {}
        },

        clearCart() {
          this.cart = [];
          this.customerName = '';
          this.notes = '';
          localStorage.removeItem(storageKey);
        },

        unitPrice(item) {
          return item.unit_price ?? item.price;
        },

        async loadCategory(categoryId) {
          this.activeCategory = categoryId;
          this.loading = true;
          try {
            const response = await fetch(this.menuUrl + '/products/' + categoryId);
            this.products = await response.json();
          } catch (e) {
            this.products = [];
          }
          this.loading = false;
        },

        // Variant modal
        openVariantModal(product) {
          this.variantProduct = product;
          this.selectedVariantOptions = {};
          this.matchedVariation = null;
          this.showVariantModal = true;
        },

        selectVariantOption(dimensionName, option) {
          this.selectedVariantOptions[dimensionName] = option;
          this.matchedVariation = this.findMatchingVariation();
        },

        get allDimensionsSelected() {
          if (!this.variantProduct?.variants) return false;
          return this.variantProduct.variants.every(d => this.selectedVariantOptions[d.name]);
        },

        get matchedVariationLabel() {
          if (!this.matchedVariation?.meta) return '';
          return Object.values(this.matchedVariation.meta).join(' / ');
        },

        findMatchingVariation() {
          if (!this.variantProduct?.variations || !this.allDimensionsSelected) return null;
          return this.variantProduct.variations.find(v => {
            if (!v.meta) return false;
            return this.variantProduct.variants.every(d =>
              v.meta[d.name] === this.selectedVariantOptions[d.name]
            );
          }) || null;
        },

        addVariantToCart() {
          if (!this.matchedVariation || !this.variantProduct) return;

          const cartKey = this.variantProduct.id + '-v' + this.matchedVariation.id;
          const existing = this.cart.find(i => i.cartKey === cartKey);
          if (existing) {
            existing.quantity++;
          } else {
            this.cart.push({
              id: this.variantProduct.id,
              cartKey: cartKey,
              name: this.variantProduct.name,
              price: parseFloat(this.matchedVariation.price),
              quantity: 1,
              variation_id: this.matchedVariation.id,
              variation_meta: {
                ...this.matchedVariation.meta
              },
              unit_price: this.matchedVariation.unit_price,
              net_price: this.matchedVariation.net_price,
              tax_amount: this.matchedVariation.tax_amount,
            });
          }

          this.showVariantModal = false;
          this.selectedVariantOptions = {};
          this.matchedVariation = null;
          this.variantProduct = null;
        },

        addToCart(product) {
          const cartKey = product.id + '-p';
          const existing = this.cart.find(i => i.cartKey === cartKey);
          if (existing) {
            existing.quantity++;
          } else {
            this.cart.push({
              id: product.id,
              cartKey: cartKey,
              name: product.name,
              price: parseFloat(product.price),
              quantity: 1,
              variation_id: null,
              variation_meta: null,
              unit_price: product.unit_price,
              net_price: product.net_price,
              tax_amount: product.tax_amount,
            });
          }
        },

        decrementCart(cartKey) {
          const index = this.cart.findIndex(i => i.cartKey === cartKey);
          if (index > -1) {
            if (this.cart[index].quantity > 1) {
              this.cart[index].quantity--;
            } else {
              this.cart.splice(index, 1);
            }
          }
        },

        incrementCart(cartKey) {
          const item = this.cart.find(i => i.cartKey === cartKey);
          if (item) {
            item.quantity++;
          }
        },

        inCart(productId) {
          return this.cart.some(i => i.id === productId && !i.variation_id);
        },

        getCartQty(productId) {
          return this.cart.find(i => i.id === productId && !i.variation_id)?.quantity || 0;
        },

        formatMeta(meta) {
          if (!meta) return '';
          return Object.entries(meta).map(([k, v]) => k + ': ' + v).join(' · ');
        },

        get totalItems() {
          return this.cart.reduce((sum, i) => sum + i.quantity, 0);
        },

        get cartTotal() {
          return this.cart.reduce((sum, i) => sum + (this.unitPrice(i) * i.quantity), 0);
        },
      };
    }
  </script>
@endsection
