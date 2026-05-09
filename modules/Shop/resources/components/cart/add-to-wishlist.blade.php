<button type="button" wire:click="submit" wire:loading.attr="disabled" wire:target="submit"
  class="p-2 text-prominent bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-full">
  <svg class="size-5 shrink-0 hover:stroke-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"
    data-slot="icon">
    <path stroke-linecap="round" stroke-linejoin="round"
      d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
  </svg>
  <span class="sr-only">{{ __('Add to wishlist') }}</span>
</button>
