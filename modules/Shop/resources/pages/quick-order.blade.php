<x-slot name="title">{{ __('Order') }}: {{ $product->name }}</x-slot>
<x-slot name="metaDesc">{{ __('Order') }} {{ $product->name }} — {{ config('app.name') }}</x-slot>

<div class="min-h-screen bg-slate-50">

  {{-- Back nav --}}
  <div class="bg-white border-b border-slate-200 px-4 py-3">
    <a href="/" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-emerald-600 transition-colors">
      <svg xmlns="http://www.w3.org/2000/svg" class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5"/>
      </svg>
      {{ __('Back to Products') }}
    </a>
  </div>

  <div class="mx-auto max-w-4xl px-4 sm:px-6 py-8">

    @if ($submitted)
      {{-- ══════════════════════════════════════════════════════
           SUCCESS STATE
      ══════════════════════════════════════════════════════ --}}
      <div class="text-center py-16">
        <div class="mx-auto w-20 h-20 rounded-full bg-emerald-100 flex items-center justify-center mb-6">
          <svg xmlns="http://www.w3.org/2000/svg" class="size-10 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
          </svg>
        </div>
        <h1 class="text-2xl font-extrabold text-slate-900 mb-2">{{ __('Order Placed!') }}</h1>
        <p class="text-slate-500 mb-1">{{ __('Thank you,') }} <strong>{{ $name }}</strong>.</p>
        <p class="text-slate-500 mb-6">
          {{ __('Your order reference is:') }}
          <span class="font-mono font-bold text-emerald-700 text-lg">{{ $orderRef }}</span>
        </p>
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 max-w-sm mx-auto text-left text-sm space-y-2 mb-8">
          <div class="flex justify-between">
            <span class="text-slate-500">{{ __('Product') }}</span>
            <span class="font-semibold text-slate-800">{{ $product->name }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500">{{ __('Quantity') }}</span>
            <span class="font-semibold text-slate-800">{{ $quantity }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500">{{ __('Total') }}</span>
            <span class="font-bold text-emerald-700">{{ currency_value($product->price * $quantity) }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500">{{ __('Region') }}</span>
            <span class="font-semibold text-slate-800">{{ $region }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500">{{ __('Phone') }}</span>
            <span class="font-semibold text-slate-800">{{ $phone }}</span>
          </div>
        </div>
        <p class="text-slate-400 text-sm mb-6">{{ __('We will contact you soon to confirm delivery.') }}</p>
        <a href="/" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-6 py-3 rounded-xl transition-colors">
          {{ __('Order More Products') }}
        </a>
      </div>

    @else
      {{-- ══════════════════════════════════════════════════════
           ORDER FORM
      ══════════════════════════════════════════════════════ --}}
      <div class="grid grid-cols-1 md:grid-cols-5 gap-6">

        {{-- Left: Product info --}}
        <div class="md:col-span-2">
          <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden sticky top-4">
            <div class="aspect-square bg-slate-50 overflow-hidden">
              <img
                src="{{ $product->photo ?? asset('img/products/dummy.jpg') }}"
                alt="{{ $product->name }}"
                class="w-full h-full object-cover">
            </div>
            <div class="p-5">
              @if ($product->category)
                <p class="text-xs text-slate-400 uppercase tracking-wide mb-1">{{ $product->category->name }}</p>
              @endif
              <h2 class="text-lg font-bold text-slate-900 mb-2 leading-snug">{{ $product->name }}</h2>

              @if ($product->description)
                <p class="text-sm text-slate-500 line-clamp-3 mb-3">{{ strip_tags($product->description) }}</p>
              @endif

              <div class="text-2xl font-extrabold text-emerald-700">
                {{ currency_value($product->price) }}
              </div>

              @if ($product->validPromotions->count())
                <div class="mt-2 flex flex-wrap gap-1">
                  @foreach ($product->validPromotions as $promo)
                    <span class="bg-amber-100 text-amber-700 text-xs font-bold px-2 py-0.5 rounded-full">{{ $promo->name }}</span>
                  @endforeach
                </div>
              @endif
            </div>
          </div>
        </div>

        {{-- Right: Order form --}}
        <div class="md:col-span-3">
          <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
            <h1 class="text-xl font-extrabold text-slate-900 mb-1">{{ __('Complete Your Order') }}</h1>
            <p class="text-sm text-slate-400 mb-6">{{ __('Fill in your details and we will deliver to you.') }}</p>

            @if ($errors->any())
              <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 text-sm text-red-700">
                <ul class="space-y-1">
                  @foreach ($errors->all() as $error)
                    <li>• {{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <div class="space-y-5">

              {{-- Full Name --}}
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                  {{ __('Full Name') }} <span class="text-red-500">*</span>
                </label>
                <input
                  wire:model="name"
                  type="text"
                  placeholder="{{ __('e.g. Farhan Ahmed') }}"
                  autocomplete="name"
                  class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent @error('name') border-red-400 bg-red-50 @enderror">
                @error('name')
                  <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
              </div>

              {{-- Phone --}}
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                  {{ __('Phone Number') }} <span class="text-red-500">*</span>
                </label>
                <input
                  wire:model="phone"
                  type="tel"
                  placeholder="+252 6X XXX XXXX"
                  autocomplete="tel"
                  class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent @error('phone') border-red-400 bg-red-50 @enderror">
                @error('phone')
                  <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
              </div>

              {{-- Region --}}
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                  {{ __('Region (Gobolka)') }} <span class="text-red-500">*</span>
                </label>
                <select
                  wire:model="region"
                  class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent @error('region') border-red-400 bg-red-50 @enderror">
                  @foreach (\Modules\Shop\Http\Livewire\QuickOrder::$somaliRegions as $r)
                    <option value="{{ $r }}" @selected($r === $region)>{{ $r }}</option>
                  @endforeach
                </select>
                @error('region')
                  <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
              </div>

              {{-- Quantity --}}
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">{{ __('Quantity') }}</label>
                <div class="inline-flex items-center rounded-xl border border-slate-300 overflow-hidden">
                  <button wire:click="decrement" type="button"
                    class="px-4 py-3 text-slate-600 hover:bg-slate-100 transition-colors text-lg font-bold leading-none">−</button>
                  <span class="px-5 py-3 font-bold text-slate-900 text-sm min-w-[3rem] text-center">{{ $quantity }}</span>
                  <button wire:click="increment" type="button"
                    class="px-4 py-3 text-slate-600 hover:bg-slate-100 transition-colors text-lg font-bold leading-none">+</button>
                </div>
              </div>

              {{-- Notes (optional) --}}
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                  {{ __('Delivery Notes') }} <span class="text-slate-400 font-normal">({{ __('optional') }})</span>
                </label>
                <textarea
                  wire:model="notes"
                  rows="2"
                  placeholder="{{ __('Street name, district, landmark…') }}"
                  class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent resize-none"></textarea>
              </div>

              {{-- Order summary --}}
              <div class="bg-slate-50 rounded-xl p-4 border border-slate-200">
                <div class="flex justify-between text-sm mb-1">
                  <span class="text-slate-500">{{ $product->name }} × {{ $quantity }}</span>
                  <span class="font-semibold">{{ currency_value($product->price * $quantity) }}</span>
                </div>
                <div class="border-t border-slate-200 pt-2 mt-2 flex justify-between font-bold text-base">
                  <span>{{ __('Total') }}</span>
                  <span class="text-emerald-700">{{ currency_value($product->price * $quantity) }}</span>
                </div>
              </div>

              {{-- Submit --}}
              <button
                wire:click="submit"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-60 cursor-not-allowed"
                type="button"
                class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-4 rounded-xl transition-colors text-base flex items-center justify-center gap-3">
                <span wire:loading.remove>
                  <svg xmlns="http://www.w3.org/2000/svg" class="size-5 inline -mt-0.5 me-1" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                  </svg>
                  {{ __('Place Order Now') }}
                </span>
                <span wire:loading>
                  <svg class="animate-spin size-5 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                  </svg>
                  {{ __('Placing Order…') }}
                </span>
              </button>

              <p class="text-center text-xs text-slate-400">
                {{ __('By ordering, you agree to be contacted by our team for delivery confirmation.') }}
              </p>

            </div>
          </div>
        </div>

      </div>
    @endif
  </div>
</div>
