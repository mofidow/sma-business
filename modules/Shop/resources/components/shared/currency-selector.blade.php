<div x-data="{ open: false }" @click.away="open = false" class="relative z-30 inline-block text-start text-sm">
  <button type="button" @click="open = true"
    class="flex items-center px-2 link hover:bg-gray-100 dark:hover:bg-gray-700 -my-2.5 py-2.5 rounded-md">
    @if (session('shop_currency'))
      {{ session('shop_currency')->currency?->code ?? session('shop_currency')->code }}
    @else
      {{ default_currency()?->code ?? 'USD' }}
    @endif
  </button>


  <div x-show="open" style="display: none" x-transition:enter="transition ease-out duration-100"
    x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100"
    x-transition:leave-end="transform opacity-0 scale-95"
    class="absolute end-0 z-10 mt-4 w-24 origin-top-right rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black/5 focus:outline-none"
    role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
    <div class="py-1" role="none">
      @foreach ($currencies as $currency)
        <a wire:click="select('{{ $currency->currency?->code ?? $currency->code }}')" @click="open = false"
          class="flex items-center gap-1 px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-900 x-focus" role="menuitem" tabindex="-1">
          {{ $currency->currency?->code ?? $currency->code }}
          {{ $currency->currency?->symbol ? '(' . $currency->currency?->symbol . ') ' : '' }}
        </a>
      @endforeach
    </div>
  </div>
</div>
