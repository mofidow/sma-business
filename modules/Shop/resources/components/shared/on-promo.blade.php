@props(['item', 'align' => 'left'])

@php
  switch ($align) {
      case 'left':
          $alignmentClasses = 'origin-top-left start-0';
          break;
      case 'top':
          $alignmentClasses = 'origin-top';
          break;
      case 'none':
      case 'false':
          $alignmentClasses = '';
          break;
      case 'right':
      default:
          $alignmentClasses = 'origin-top-right end-0';
          break;
  }
@endphp
@if ($item->onPromo())
  <div x-data="{ open: false }" class="absolute h-8 w-8 z-10 top-1 start-1 inline-flex items-center">
    <div class="relative">
      <button @click="open = true" type="button" class="rounded-full text-green-600 hover:text-blue-600 h-8 w-8 focus-default p-1">
        <div x-show="!open" class="z-0 animate-ping absolute top-1 start-1 rounded-full bg-green-600 h-6 w-6"></div>
        <x-shop::elements.icon name="promo" class="z-10" />
      </button>

      <div x-show="open" @click.away="open = false" x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100"
        x-transition:leave-end="transform opacity-0 scale-95" style="display:none"
        class="absolute z-30 bg-black mt-1 px-3 py-2 text-white rounded-md {{ $alignmentClasses }}">
        @forelse ($item->validPromotions as $promotion)
          <div class="my-1 rounded-md cursor-default whitespace-nowrap relative group">
            @if ($promotion->type != 'BXGY')
              {{ __(':discount off', ['discount' => formatNumber($promotion->discount, 0) . ($promotion->discount_method == 'percentage' ? '% ' : ' ')]) }}
            @endif
            @if ($promotion->type == 'advance' && $promotion->quantity_to_buy)
              {{ __('with purchase of :quantity', ['quantity' => formatNumber($promotion->quantity_to_buy, 0)]) }}
            @endif
            @if ($promotion->type == 'BXGY')
              @if ($promotion->item_id_to_buy == $item->id)
                {{ __('Free :itemToGet with purchase of :quantity :itemToBuy', ['itemToGet' => $promotion->itemToGet?->name, 'quantity' => formatNumber($promotion->quantity_to_buy, 0), 'itemToBuy' => $promotion->itemToBuy?->name]) }}
              @endif
              @if ($promotion->item_id_to_get == $item->id)
                {{ __('Free with purchase of :quantity :itemToBuy', ['quantity' => formatNumber($promotion->quantity_to_buy, 0), 'itemToBuy' => $promotion->itemToBuy?->name]) }}
              @endif
            @endif
            @if ($promotion->type == 'SXGD' && $promotion->amount_to_spend)
              {{ __('with purchase worth :amount', ['amount' => formatNumber($promotion->amount_to_spend, 0)]) }}
            @endif
          </div>
        @empty
        @endforelse

        @forelse ($item->categories as $category)
          @forelse ($category->validPromotions as $promotion)
            <div class="my-1 rounded-md cursor-default whitespace-nowrap relative group">
              @if ($promotion->type != 'BXGY')
                {{ __(':discount off', ['discount' => formatNumber($promotion->discount, 0) . ($promotion->discount_method == 'percentage' ? '% ' : ' ')]) }}
              @endif
              @if ($promotion->type == 'advance' && $promotion->quantity_to_buy)
                {{ __('with purchase of :quantity', ['quantity' => formatNumber($promotion->quantity_to_buy, 0)]) }}
              @endif
              @if ($promotion->type == 'BXGY')
                @if ($promotion->item_id_to_buy == $item->id)
                  {{ __('Free :itemToGet with purchase of :quantity :itemToBuy', ['itemToGet' => $promotion->itemToGet?->name, 'quantity' => formatNumber($promotion->quantity_to_buy, 0), 'itemToBuy' => $promotion->itemToBuy?->name]) }}
                @endif
                @if ($promotion->item_id_to_get == $item->id)
                  {{ __('Free with purchase of :quantity :itemToBuy', ['quantity' => formatNumber($promotion->quantity_to_buy, 0), 'itemToBuy' => $promotion->itemToBuy?->name]) }}
                @endif
              @endif
              @if ($promotion->type == 'SXGD' && $promotion->amount_to_spend)
                {{ __('with purchase worth :amount', ['amount' => formatNumber($promotion->amount_to_spend, 0)]) }}
              @endif
            </div>
          @empty
          @endforelse
        @empty
        @endforelse
      </div>
    </div>
  </div>
@endif
