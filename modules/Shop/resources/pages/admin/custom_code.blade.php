<div class="mx-auto max-w-7xl sm:px-6 py-8 sm:py-16 lg:px-8">
  <x-shop::jet.form-section submit="save">
    <x-slot name="title">{{ __('Shop Custom Code') }}</x-slot>
    <x-slot name="description">{{ __('Please update custom code for shop layout as you want') }}</x-slot>

    <x-slot name="form">
      <!-- Header Custom Code -->
      <div class="col-span-full">
        <label for="shop_header_code" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
          {{ __('Header Custom Code') }}
        </label>
        <textarea class="input mt-1" rows="10" id="shop_header_code" wire:model="settings.shop_header_code"></textarea>

        @error('settings.shop_header_code')
          <span class="error">{{ $message }}</span>
        @enderror
      </div>

      <!-- Footer Custom Code -->
      <div class="col-span-full">
        <label for="shop_footer_code" class="block font-medium text-sm text-gray-700 dark:text-gray-300">
          {{ __('Footer Custom Code') }}
        </label>
        <textarea class="input mt-1" rows="10" id="shop_footer_code" wire:model="settings.shop_footer_code"></textarea>

        @error('settings.shop_footer_code')
          <span class="error">{{ $message }}</span>
        @enderror
      </div>
    </x-slot>

    <x-slot name="actions">
      <x-shop::jet.button class="ms-4 btn-md">
        {{ __('Save') }}
        </x-jet.button>
    </x-slot>
  </x-shop::jet.form-section>
</div>
