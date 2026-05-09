<div class="mt-12 md:mt-16 xl:mt-0">
  <h3 class="text-sm font-medium text-prominent">{{ __('Sign up for our newsletter') }}</h3>
  <p class="mt-3 text-sm text-mute">{{ __('The latest deals and savings, sent to your inbox weekly.') }}</p>
  <div class="flex flex-col sm:flex-row items-stretch gap-x-4 gap-y-3 xl:flex-col mt-6">
    <div class="grow max-w-sm">
      <x-shop::jet.input id="newsletter-name" wire:model="name" type="text" required placeholder="Name" class="input" />
      @error('name')
        <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span>
      @enderror
    </div>
    <div class="grow max-w-sm">
      <x-shop::jet.input id="newsletter-email" wire:model="email" type="email" required placeholder="Email address" class="input" />
      @error('email')
        <span class="text-red-600 dark:text-red-400 text-sm">{{ $message }}</span>
      @enderror
    </div>
    <div class="flex items-stretch xl:pb-3">
      <x-shop::jet.button class="justify-center" type="button" wire:click="subscribe">
        {{ __('Subscribe') }}
      </x-shop::jet.button>
    </div>
  </div>
</div>
