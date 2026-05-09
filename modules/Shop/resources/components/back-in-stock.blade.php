<div
  x-data="{
    isOutOfStock: @js($isOutOfStock),
    variationId: null,
    subscribed: @js($subscribed),
  }"
  @variant-stock-changed.window="isOutOfStock = $event.detail.isOutOfStock; variationId = $event.detail.variationId; subscribed = false"
  @back-in-stock-subscribed.window="subscribed = true"
  x-show="isOutOfStock"
  class="mt-6 border border-gray-200 dark:border-gray-700 rounded-lg p-4"
>
  <p class="text-sm font-semibold text-prominent mb-1">{{ __('Out of Stock') }}</p>

  <div x-show="subscribed">
    <p class="text-sm text-green-600 dark:text-green-400">
      {{ __("You're on the list! We'll email you when this product is back in stock.") }}
    </p>
  </div>

  <div x-show="!subscribed">
    <p class="text-sm text-mute mb-3">{{ __('Enter your email to be notified when this product is available again.') }}</p>
    <div class="flex gap-2">
      <div class="flex-1">
        <label for="bis-email-{{ $productId }}" class="sr-only">{{ __('Email address') }}</label>
        <x-shop::jet.input
          id="bis-email-{{ $productId }}"
          type="email"
          wire:model="email"
          placeholder="{{ __('Your email address') }}"
          class="w-full"
          autocomplete="email"
        />
        <x-shop::jet.input-error for="email" class="mt-1" />
      </div>
      <button type="button" @click="$wire.subscribe(variationId)" wire:loading.attr="disabled" wire:target="subscribe"
        class="btn-primary shrink-0">
        <span wire:loading.remove wire:target="subscribe">{{ __('Notify Me') }}</span>
        <svg wire:loading wire:target="subscribe" viewBox="0 0 38 38" stroke="currentColor"
          xmlns="http://www.w3.org/2000/svg" class="size-5 stroke-current inline">
          <g fill="none" fill-rule="evenodd">
            <g transform="translate(1 1)" stroke-width="3">
              <circle stroke-opacity=".3" cx="18" cy="18" r="16" />
              <path d="M34 20c0-9.94-8.06-18-18-18">
                <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s"
                  repeatCount="indefinite" />
              </path>
            </g>
          </g>
        </svg>
      </button>
    </div>
  </div>
</div>
