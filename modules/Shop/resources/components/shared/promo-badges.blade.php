@props(['item'])
<div class="absolute top-0 start-2 z-10 flex items-center gap-2 text-sm font-bold pe-4 h-10">
  @forelse ($item->validPromotions as $promotion)
    <span class="bg-blue-600 text-white px-2 py-1 rounded-md cursor-default whitespace-nowrap relative group">
      @if ($promotion->type != 'BXGY')
        {{ __(':discount off', ['discount' => formatNumber($promotion->discount, 0) . ($promotion->discount_method == 'percentage' ? '% ' : ' ')]) }}
      @endif
      @if ($promotion->type == 'advance' && $promotion->quantity_to_buy)
        <div class="absolute start-0 flex-col items-center hidden mt-2 group-hover:flex">
          <span
            class="relative z-10 p-2 rounded-md text-xs leading-none text-white bg-black shadow-lg text-ellipsis w-auto max-w-80 overflow-hidden">
            {{ __('with purchase of :quantity', ['quantity' => formatNumber($promotion->quantity_to_buy, 0)]) }}
          </span>
        </div>
      @endif
      @if ($promotion->type == 'BXGY')
        @if ($promotion->item_id_to_buy == $item->id)
          {{ __('BXGY') }}
          <div class="absolute start-0 flex-col items-center hidden mt-2 group-hover:flex">
            <span
              class="relative z-10 p-2 rounded-md text-xs leading-none text-white bg-black shadow-lg text-ellipsis w-auto max-w-80 overflow-hidden">
              {{ __('Free :itemToGet with purchase of :quantity :itemToBuy', ['itemToGet' => $promotion->itemToGet?->name, 'quantity' => formatNumber($promotion->quantity_to_buy, 0), 'itemToBuy' => $promotion->itemToBuy?->name]) }}
            </span>
          </div>
        @endif
        @if ($promotion->item_id_to_get == $item->id)
          {{ __('Free') }}
          <div class="absolute start-0 flex-col items-center hidden mt-2 group-hover:flex">
            <span
              class="relative z-10 p-2 rounded-md text-xs leading-none text-white bg-black shadow-lg text-ellipsis w-auto max-w-80 overflow-hidden">
              {{ __('with purchase of :quantity :itemToBuy', ['quantity' => formatNumber($promotion->quantity_to_buy, 0), 'itemToBuy' => $promotion->itemToBuy?->name]) }}
            </span>
          </div>
        @endif
      @endif
      @if ($promotion->type == 'SXGD' && $promotion->amount_to_spend)
        <div class="absolute start-0 flex-col items-center hidden mt-2 group-hover:flex">
          <span
            class="relative z-10 p-2 rounded-md text-xs leading-none text-white bg-black shadow-lg text-ellipsis w-auto max-w-80 overflow-hidden">
            {{ __('with purchase worth :amount', ['amount' => formatNumber($promotion->amount_to_spend, 0)]) }}
          </span>
        </div>
      @endif
    </span>
  @empty
  @endforelse
  @forelse ($item->categories as $category)
    @forelse ($category->validPromotions as $promotion)
      <span class="bg-blue-600 text-white px-2 py-1 rounded-md cursor-default whitespace-nowrap relative group">
        @if ($promotion->type != 'BXGY')
          {{ __(':discount off', ['discount' => formatNumber($promotion->discount, 0) . ($promotion->discount_method == 'percentage' ? '% ' : ' ')]) }}
        @endif
        @if ($promotion->type == 'advance' && $promotion->quantity_to_buy)
          <div class="absolute start-0 flex-col items-center justify-start hidden mt-2 group-hover:flex sm:w-auto">
            <span
              class="relative z-10 p-2 rounded-md text-xs leading-none text-white bg-black shadow-lg text-ellipsis w-auto max-w-80 overflow-hidden">
              {{ __('with purchase of :quantity', ['quantity' => formatNumber($promotion->quantity_to_buy, 0)]) }}
            </span>
          </div>
        @endif
        @if ($promotion->type == 'BXGY')
          @if ($promotion->item_id_to_buy == $item->id)
            {{ __('BXGY') }}
            <div class="absolute start-0 flex-col items-center hidden mt-2 group-hover:flex">
              <span
                class="relative z-10 p-2 rounded-md text-xs leading-none text-white bg-black shadow-lg text-ellipsis w-auto max-w-80 overflow-hidden">
                {{ __('Free :itemToGet with purchase of :quantity :itemToBuy', ['itemToGet' => $promotion->itemToGet?->name, 'quantity' => formatNumber($promotion->quantity_to_buy, 0), 'itemToBuy' => $promotion->itemToBuy?->name]) }}
              </span>
            </div>
          @endif
          @if ($promotion->item_id_to_get == $item->id)
            {{ __('Free') }}
            <div class="absolute start-0 flex-col items-center hidden mt-2 group-hover:flex">
              <span
                class="relative z-10 p-2 rounded-md text-xs leading-none text-white bg-black shadow-lg text-ellipsis w-auto max-w-80 overflow-hidden">
                {{ __('with purchase of :quantity :itemToBuy', ['quantity' => formatNumber($promotion->quantity_to_buy, 0), 'itemToBuy' => $promotion->itemToBuy?->name]) }}
              </span>
            </div>
          @endif
        @endif
        @if ($promotion->type == 'SXGD' && $promotion->amount_to_spend)
          <div class="absolute start-0 flex-col items-center hidden mt-2 group-hover:flex">
            <span
              class="relative z-10 p-2 rounded-md text-xs leading-none text-white bg-black shadow-lg text-ellipsis w-auto max-w-80 overflow-hidden">
              {{ __('with purchase worth :amount', ['amount' => formatNumber($promotion->amount_to_spend, 0)]) }}
            </span>
          </div>
        @endif
      </span>
    @empty
    @endforelse
  @empty
  @endforelse
</div>
