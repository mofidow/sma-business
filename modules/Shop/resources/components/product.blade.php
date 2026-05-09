@props(['product'])

<div
  class="group relative z-0 flex w-full flex-col overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800">
  @if ($product->validPromotions->count())
    <div class="absolute z-10 top-0 end-0 text-xs font-semibold flex items-center gap-x-2 gap-y-1">
      @foreach ($product->validPromotions as $promotion)
        <div @class([
            'bg-primary-500 text-white px-2 pb-0.5 whitespace-nowrap',
            'rounded-b-lg' => $loop->first && !$loop->last,
            'rounded-bl-lg' => $loop->last,
        ])>
          @if ($promotion->type == 'simple')
            {{ __('{discount} off', ['discount' => format_number($promotion->discount, 0) . ($promotion->discount_method == 'percentage' ? '% ' : ' ')]) }}
          @elseif ($promotion->type == 'advance' && $promotion->quantity_to_buy)
            {{ __('Buy {quantity} to get {discount} off', ['discount' => format_number($promotion->discount, 0) . ($promotion->discount_method == 'percentage' ? '% ' : ' '), 'quantity' => format_number($promotion->quantity_to_buy, 0)]) }}
            {{-- @elseif ($promotion->type == 'SXGD' && $promotion->amount_to_spend)
            {{ __('Spend {amount} to get {discount} off', ['discount' => format_number($promotion->discount, 0) . ($promotion->discount_method == 'percentage' ? '% ' : ' '), 'amount' => format_number($promotion->amount_to_spend, 0)]) }}
          @elseif ($promotion->type == 'BXGY' && $promotion->productToGet)
            @if ($promotion->product_id_to_buy == $product->id)
              {{ __('Free :productToGet with purchase of :quantity :productToBuy', ['productToGet' => $promotion->productToGet?->name, 'quantity' => format_number($promotion->quantity_to_buy, 0), 'productToBuy' => $promotion->productToBuy?->name]) }}
            @endif
            @if ($promotion->product_id_to_get == $product->id)
              {{ __('Free with purchase of :quantity :productToBuy', ['quantity' => format_number($promotion->quantity_to_buy, 0), 'productToBuy' => $promotion->productToBuy?->name]) }}
            @endif --}}
          @else
            {{ $promotion->name }}
          @endif
        </div>
      @endforeach
    </div>
  @endif
  <img src="{{ $product->photo ?? asset('img/products/dummy.jpg') }}" alt="{{ $product->name }}"
    class="aspect-square w-full bg-gray-100 dark:bg-gray-700 object-cover group-hover:opacity-75">
  <div class="flex flex-1 flex-col space-y-1 py-3 px-4">
    <div class="flex items-start justify-between space-x-2 mb1 text-sm border-b border-gray-200 dark:border-gray-700 pb-1.5">
      <h3 class="font-bold text-prominent">
        <a href="{{ route('shop.product', $product->slug ?? 'basic-tee-8-pack') }}" wire:navigate w.hover class="x-focus">
          <span aria-hidden="true" class="absolute inset-0"></span>
          {{ $product->name ?? 'Basic Tee 8-Pack' }}
        </a>
      </h3>
      <p class="font-medium text-prominent">{{ currency_value($product->price) }}</p>
    </div>
    <div class="z-10 text-xs font-semibold truncate flex items-center space-x-1 py-0.5 overflow-x-auto">
      @if ($product->brand?->slug)
        <a href="{{ route('shop.brand', $product->brand->slug) }}" class="text-mute hover:text-prominent x-focus">
          {{ $product->brand->name }}
        </a>
        <span>&raquo;</span>
      @endif
      <a href="{{ route('shop.category', $product->category->slug) }}" class="text-mute hover:text-prominent x-focus">
        {{ $product->category->name }}
      </a>
      @if ($product->subcategory?->slug)
        <span>&raquo;</span>
        <a href="{{ route('shop.subcategory', [$product->category->slug, $product->subcategory->slug]) }}"
          class="text-mute hover:text-prominent x-focus">
          {{ $product->subcategory->name }}
        </a>
      @endif
    </div>
    <p class="text-sm mt-1 text-mute line-clamp-3">
      {{ $product->description ?? '' }}
    </p>
    {{-- <div class="flex flex-1 flex-col justify-end">
      <p class="text-sm italic text-mute">8 colors</p>
      <p class="text-base font-medium text-prominent">{{ format_currency($product->price) }}</p>
    </div> --}}
  </div>

  @if (($shop_settings ?? null) && ($shop_settings['general']['cart_button'] ?? null) == 'hover')
    <div key="add_to_cart"
      class="px-4 pb-4 xl:hidden xl:group-hover:block xl:absolute xl:inset-x-0 xl:top-1/2 xl:translate-y-1/2 animate__faster animate__animated animate__fadeIn">
      <livewire:components.cart.add :product="$product" :popup_variant="true" :key="'to_cart_' . $product->id" />
    </div>
  @else
    <div class="px-4 pb-4">
      <livewire:components.cart.add :product="$product" :popup_variant="true" :key="'add_to_cart_' . $product->id" />
    </div>
  @endif
</div>
