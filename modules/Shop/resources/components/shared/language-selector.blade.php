<div x-data="{ open: false }" @click.away="open = false" class="relative z-30 inline-block text-start text-sm">

  <button type="button" @click="open = true" class="flex items-center px-2.5 py-1.5 link hover:bg-gray-100 dark:hover:bg-gray-700 rounded-md">
    @if (session('shop_language'))
      <div class="text-lg w-5"
        x-html="'{{ session('shop_language')->flag }}'.replace(/./g, char => String.fromCodePoint(char.charCodeAt(0) + 127397))"></div>
    @else
      <div class="text-lg w-5" x-html="'{{ $current->flag }}'.replace(/./g, char => String.fromCodePoint(char.charCodeAt(0) + 127397))">
      </div>
    @endif
  </button>


  <div x-show="open" style="display: none" x-transition:enter="transition ease-out duration-100"
    x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100"
    x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100"
    x-transition:leave-end="transform opacity-0 scale-95"
    class="absolute z-10 mt-2 w-40 rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black/5 focus:outline-none end-0 ltr:origin-top-right rtl:origin-top-left max-h-[calc(100vh-8rem)] overflow-y-auto"
    role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
    <div class="py-1" role="none">
      @foreach ($languages as $language)
        <a wire:click="select('{{ $language->value }}')" @click="open = false"
          class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-900" role="menuitem" tabindex="-1">
          <div class="me-2 text-lg -my-1"
            x-html="'{{ $language->flag }}'.replace(/./g, char => String.fromCodePoint(char.charCodeAt(0) + 127397))"></div>
          {{ $language->label }}
        </a>
      @endforeach
    </div>
  </div>
</div>
